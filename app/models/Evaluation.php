<?php
/**
 * ============================================================
 *  POSUNG HRIS – Evaluation Model (Phân hệ Đánh giá KPI & Năng lực)
 * ============================================================
 *  Quản lý Mẫu đánh giá (Templates), Tiêu chí (Criteria),
 *  Chu kỳ (Periods), Chấm điểm chi tiết (Scores) và Tổng kết (emp_evaluations)
 * ============================================================
 */

class Evaluation extends BaseModel
{
    protected string $table = 'emp_evaluations';

    // ══════════════════════════════════════════════════════════
    //  1. CHU KỲ ĐÁNH GIÁ (evaluation_periods)
    // ══════════════════════════════════════════════════════════

    /**
     * Lấy danh sách chu kỳ đánh giá kèm thống kê tiến độ
     */
    public function getPeriods(array $filters = []): array
    {
        $sql = "SELECT p.*,
                       t.name AS template_name,
                       d.dept_name,
                       COUNT(DISTINCT ee.employee_id) AS evaluated_count,
                       ROUND(AVG(ee.final_score), 1) AS avg_score
                FROM evaluation_periods p
                LEFT JOIN evaluation_templates t ON p.template_id = t.id
                LEFT JOIN departments d ON p.department_id = d.id
                LEFT JOIN emp_evaluations ee ON (p.id = ee.period_id AND ee.status IN ('Manager_Evaluated', 'Approved'))
                WHERE 1=1";

        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND p.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['year'])) {
            $sql .= " AND (YEAR(p.start_date) = :year OR YEAR(p.end_date) = :year)";
            $params['year'] = (int)$filters['year'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE :search OR t.name LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " GROUP BY p.id ORDER BY p.start_date DESC, p.id DESC";

        $this->db->query($sql, $params);
        $periods = $this->db->fetchAll();

        // Tính tổng số nhân viên áp dụng cho mỗi chu kỳ
        foreach ($periods as &$period) {
            $empSql = "SELECT COUNT(*) as cnt FROM employees WHERE status != 'Resigned'";
            $empParams = [];
            if (!empty($period['department_id'])) {
                $empSql .= " AND department_id = :dept_id";
                $empParams['dept_id'] = (int)$period['department_id'];
            }
            $this->db->query($empSql, $empParams);
            $period['total_eligible'] = (int)($this->db->fetch()['cnt'] ?? 0);
            $evaluated = (int)$period['evaluated_count'];
            $period['completion_rate'] = $period['total_eligible'] > 0 ? round(($evaluated / $period['total_eligible']) * 100) : 0;
        }

        return $periods;
    }

    /**
     * Lấy chi tiết 1 chu kỳ đánh giá
     */
    public function getPeriodById(int $id): ?object
    {
        $sql = "SELECT p.*,
                       t.name AS template_name,
                       t.description AS template_description,
                       d.dept_name,
                       d.dept_code
                FROM evaluation_periods p
                LEFT JOIN evaluation_templates t ON p.template_id = t.id
                LEFT JOIN departments d ON p.department_id = d.id
                WHERE p.id = :id
                LIMIT 1";

        $this->db->query($sql, ['id' => $id]);
        $row = $this->db->fetch();
        return $row ? (object)$row : null;
    }

    /**
     * Tạo chu kỳ đánh giá mới
     */
    public function createPeriod(array $data): int
    {
        $this->db->query(
            "INSERT INTO evaluation_periods (name, template_id, start_date, end_date, department_id, allow_self_review, allow_peer_review, peer_review_count, status, notes, created_at)
             VALUES (:name, :tid, :sdate, :edate, :dept, :self_rev, :peer_rev, :peer_cnt, :status, :notes, NOW())",
            [
                'name'     => $data['name'],
                'tid'      => (int)$data['template_id'],
                'sdate'    => $data['start_date'],
                'edate'    => $data['end_date'],
                'dept'     => !empty($data['department_id']) ? (int)$data['department_id'] : null,
                'self_rev' => isset($data['allow_self_review']) ? (int)$data['allow_self_review'] : 1,
                'peer_rev' => isset($data['allow_peer_review']) ? (int)$data['allow_peer_review'] : 1,
                'peer_cnt' => isset($data['peer_review_count']) ? (int)$data['peer_review_count'] : 2,
                'status'   => $data['status'] ?? 'Draft',
                'notes'    => $data['notes'] ?? null,
            ]
        );
        return (int)$this->db->lastInsertId();
    }

    /**
     * Cập nhật chu kỳ đánh giá
     */
    public function updatePeriod(int $id, array $data): bool
    {
        $this->db->query(
            "UPDATE evaluation_periods
             SET name = :name,
                 template_id = :tid,
                 start_date = :sdate,
                 end_date = :edate,
                 department_id = :dept,
                 allow_self_review = :self_rev,
                 allow_peer_review = :peer_rev,
                 peer_review_count = :peer_cnt,
                 status = :status,
                 notes = :notes
             WHERE id = :id",
            [
                'name'     => $data['name'],
                'tid'      => (int)$data['template_id'],
                'sdate'    => $data['start_date'],
                'edate'    => $data['end_date'],
                'dept'     => !empty($data['department_id']) ? (int)$data['department_id'] : null,
                'self_rev' => isset($data['allow_self_review']) ? (int)$data['allow_self_review'] : 1,
                'peer_rev' => isset($data['allow_peer_review']) ? (int)$data['allow_peer_review'] : 1,
                'peer_cnt' => isset($data['peer_review_count']) ? (int)$data['peer_review_count'] : 2,
                'status'   => $data['status'] ?? 'Draft',
                'notes'    => $data['notes'] ?? null,
                'id'       => $id,
            ]
        );
        return true;
    }

    /**
     * Thống kê tổng quan KPI Module
     */
    public function getPeriodStats(): array
    {
        $this->db->query("SELECT 
            COUNT(*) AS total_periods,
            SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) AS active_periods,
            SUM(CASE WHEN status = 'Closed' THEN 1 ELSE 0 END) AS closed_periods,
            SUM(CASE WHEN status = 'Draft' THEN 1 ELSE 0 END) AS draft_periods
            FROM evaluation_periods");
        $pStats = $this->db->fetch() ?: [];

        $this->db->query("SELECT 
            COUNT(*) AS total_evaluations,
            ROUND(AVG(final_score), 1) AS overall_avg_score,
            SUM(CASE WHEN overall_grade = 'A' THEN 1 ELSE 0 END) AS grade_a_count,
            SUM(CASE WHEN overall_grade = 'B' THEN 1 ELSE 0 END) AS grade_b_count,
            SUM(CASE WHEN overall_grade = 'C' THEN 1 ELSE 0 END) AS grade_c_count,
            SUM(CASE WHEN overall_grade = 'D' THEN 1 ELSE 0 END) AS grade_d_count
            FROM emp_evaluations WHERE status IN ('Manager_Evaluated', 'Approved')");
        $eStats = $this->db->fetch() ?: [];

        return array_merge($pStats, $eStats);
    }

    // ══════════════════════════════════════════════════════════
    //  2. MẪU ĐÁNH GIÁ & TIÊU CHÍ (Templates & Criteria)
    // ══════════════════════════════════════════════════════════

    /**
     * Lấy danh sách mẫu đánh giá kèm số tiêu chí và tổng trọng số
     */
    public function getTemplates(): array
    {
        $sql = "SELECT t.*,
                       COUNT(c.id) AS criteria_count,
                       COALESCE(SUM(c.weight), 0) AS total_weight
                FROM evaluation_templates t
                LEFT JOIN evaluation_criteria c ON t.id = c.template_id
                GROUP BY t.id
                ORDER BY t.id DESC";

        $this->db->query($sql);
        return $this->db->fetchAll();
    }

    /**
     * Lấy chi tiết mẫu đánh giá kèm danh sách tiêu chí
     */
    public function getTemplateById(int $id): ?object
    {
        $this->db->query("SELECT * FROM evaluation_templates WHERE id = :id LIMIT 1", ['id' => $id]);
        $template = $this->db->fetch();
        if (!$template) return null;

        $template = (object)$template;
        $template->criteria = $this->getCriteriaByTemplate($id);
        return $template;
    }

    /**
     * Lấy danh sách tiêu chí theo template_id
     */
    public function getCriteriaByTemplate(int $templateId): array
    {
        $this->db->query(
            "SELECT * FROM evaluation_criteria WHERE template_id = :tid ORDER BY sort_order ASC, id ASC",
            ['tid' => $templateId]
        );
        return $this->db->fetchAll();
    }

    /**
     * Tạo mẫu đánh giá mới
     */
    public function createTemplate(array $data): int
    {
        $this->db->query(
            "INSERT INTO evaluation_templates (name, description, applies_to, status, created_at)
             VALUES (:name, :desc, :applies, :status, NOW())",
            [
                'name'    => $data['name'],
                'desc'    => $data['description'] ?? null,
                'applies' => $data['applies_to'] ?? 'All',
                'status'  => $data['status'] ?? 'Active',
            ]
        );
        return (int)$this->db->lastInsertId();
    }

    /**
     * Cập nhật mẫu đánh giá
     */
    public function updateTemplate(int $id, array $data): bool
    {
        $this->db->query(
            "UPDATE evaluation_templates 
             SET name = :name,
                 description = :desc,
                 applies_to = :applies,
                 status = :status
             WHERE id = :id",
            [
                'name'    => $data['name'],
                'desc'    => $data['description'] ?? null,
                'applies' => $data['applies_to'] ?? 'All',
                'status'  => $data['status'] ?? 'Active',
                'id'      => $id,
            ]
        );
        return true;
    }

    /**
     * Xóa mẫu đánh giá và tiêu chí
     */
    public function deleteTemplate(int $id): bool
    {
        $this->db->query("DELETE FROM evaluation_criteria WHERE template_id = :id", ['id' => $id]);
        $this->db->query("DELETE FROM evaluation_templates WHERE id = :id", ['id' => $id]);
        return $this->db->rowCount() > 0;
    }

    /**
     * Lưu/Cập nhật một tiêu chí đánh giá
     */
    public function saveCriteria(array $data): int
    {
        if (!empty($data['id'])) {
            $this->db->query(
                "UPDATE evaluation_criteria
                 SET name = :name,
                     category = :cat,
                     weight = :weight,
                     description = :desc,
                     sort_order = :ord
                 WHERE id = :id AND template_id = :tid",
                [
                    'name'   => $data['name'],
                    'cat'    => $data['category'] ?? 'KPI',
                    'weight' => (int)($data['weight'] ?? 20),
                    'desc'   => $data['description'] ?? null,
                    'ord'    => (int)($data['sort_order'] ?? 1),
                    'id'     => (int)$data['id'],
                    'tid'    => (int)$data['template_id'],
                ]
            );
            return (int)$data['id'];
        }

        $this->db->query(
            "INSERT INTO evaluation_criteria (template_id, name, category, weight, description, sort_order, created_at)
             VALUES (:tid, :name, :cat, :weight, :desc, :ord, NOW())",
            [
                'tid'    => (int)$data['template_id'],
                'name'   => $data['name'],
                'cat'    => $data['category'] ?? 'KPI',
                'weight' => (int)($data['weight'] ?? 20),
                'desc'   => $data['description'] ?? null,
                'ord'    => (int)($data['sort_order'] ?? 1),
            ]
        );
        return (int)$this->db->lastInsertId();
    }

    /**
     * Xóa một tiêu chí
     */
    public function deleteCriteria(int $id): bool
    {
        $this->db->query("DELETE FROM evaluation_criteria WHERE id = :id", ['id' => $id]);
        return $this->db->rowCount() > 0;
    }

    // ══════════════════════════════════════════════════════════
    //  3. ĐÁNH GIÁ & CHẤM ĐIỂM (Evaluation & Scores)
    // ══════════════════════════════════════════════════════════

    /**
     * Lấy danh sách nhân viên trong phạm vi chu kỳ kèm kết quả đánh giá
     */
    public function getEmployeesForPeriod(int $periodId, array $filters = []): array
    {
        $period = $this->getPeriodById($periodId);
        if (!$period) return [];

        $sql = "SELECT e.id AS employee_id,
                       e.emp_code,
                       e.full_name,
                       e.employee_type,
                       e.avatar_path,
                       d.dept_name,
                       p.pos_title,
                       pr.project_name,
                       ee.id AS eval_id,
                       ee.final_score,
                       ee.kpi_score,
                       ee.competency_score,
                       ee.overall_grade,
                       ee.status AS eval_status,
                       ee.updated_at,
                       u.username AS evaluator_name
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN projects pr ON e.current_project_id = pr.id
                LEFT JOIN emp_evaluations ee ON (ee.employee_id = e.id AND ee.period_id = :pid)
                LEFT JOIN users u ON ee.evaluator_id = u.id
                WHERE e.status != 'Resigned'";

        $params = ['pid' => $periodId];

        if (!empty($period->department_id)) {
            $sql .= " AND e.department_id = :p_dept";
            $params['p_dept'] = (int)$period->department_id;
        }

        if (!empty($filters['dept_id'])) {
            $sql .= " AND e.department_id = :dept_id";
            $params['dept_id'] = (int)$filters['dept_id'];
        }

        if (!empty($filters['grade'])) {
            $sql .= " AND ee.overall_grade = :grade";
            $params['grade'] = $filters['grade'];
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'Not_Started') {
                $sql .= " AND ee.id IS NULL";
            } else {
                $sql .= " AND ee.status = :status";
                $params['status'] = $filters['status'];
            }
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (e.full_name LIKE :search OR e.emp_code LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY (ee.final_score IS NOT NULL) DESC, ee.final_score DESC, e.full_name ASC";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Lấy dữ liệu chi tiết cho form chấm điểm của 1 nhân viên trong chu kỳ
     */
    public function getEvaluationData(int $periodId, int $employeeId): ?array
    {
        $period = $this->getPeriodById($periodId);
        if (!$period) return null;

        // Lấy thông tin nhân viên
        $this->db->query(
            "SELECT e.*, d.dept_name, p.pos_title, pr.project_name
             FROM employees e
             LEFT JOIN departments d ON e.department_id = d.id
             LEFT JOIN positions p ON e.position_id = p.id
             LEFT JOIN projects pr ON e.current_project_id = pr.id
             WHERE e.id = :eid LIMIT 1",
            ['eid' => $employeeId]
        );
        $employee = $this->db->fetch();
        if (!$employee) return null;

        // Lấy tiêu chí kèm điểm số đã chấm (nếu có)
        $sql = "SELECT c.*,
                       s.id AS score_id,
                       s.self_score,
                       s.manager_score,
                       s.final_score,
                       s.comment
                FROM evaluation_criteria c
                LEFT JOIN evaluation_scores s ON (c.id = s.criteria_id AND s.period_id = :pid AND s.employee_id = :eid)
                WHERE c.template_id = :tid
                ORDER BY c.sort_order ASC, c.id ASC";

        $this->db->query($sql, [
            'pid' => $periodId,
            'eid' => $employeeId,
            'tid' => $period->template_id,
        ]);
        $criteria = $this->db->fetchAll();

        // Lấy thông tin tổng kết đánh giá từ emp_evaluations
        $this->db->query(
            "SELECT * FROM emp_evaluations WHERE period_id = :pid AND employee_id = :eid LIMIT 1",
            ['pid' => $periodId, 'eid' => $employeeId]
        );
        $summary = $this->db->fetch();

        return [
            'period'   => $period,
            'employee' => (object)$employee,
            'criteria' => $criteria,
            'summary'  => $summary ? (object)$summary : null,
        ];
    }

    /**
     * Lưu kết quả đánh giá (Scores + Tổng kết vào emp_evaluations)
     */
    public function saveEvaluationScores(int $periodId, int $employeeId, array $data, ?int $evaluatorId = null): bool
    {
        $period = $this->getPeriodById($periodId);
        if (!$period) return false;

        $scoresData = $data['scores'] ?? [];
        $commentsData = $data['comments'] ?? [];
        $evalRole = $data['eval_role'] ?? 'manager'; // 'self' or 'manager'

        $criteria = $this->getCriteriaByTemplate($period->template_id);
        $totalWeight = 0;
        $weightedSum = 0;
        $kpiSum = 0;
        $kpiWeight = 0;
        $compSum = 0;
        $compWeight = 0;

        foreach ($criteria as $crit) {
            $cid = (int)$crit['id'];
            $weight = (int)$crit['weight'];
            $totalWeight += $weight;

            $score = isset($scoresData[$cid]) && $scoresData[$cid] !== '' ? (float)$scoresData[$cid] : 3.0; // thang điểm 1 - 5
            $comment = $commentsData[$cid] ?? null;

            // Tính điểm phân nhóm
            $score100 = ($score / 5) * 100; // quy đổi 100%
            $weightedSum += ($score100 * ($weight / 100));

            if ($crit['category'] === 'KPI') {
                $kpiSum += ($score100 * $weight);
                $kpiWeight += $weight;
            } else {
                $compSum += ($score100 * $weight);
                $compWeight += $weight;
            }

            // Kiểm tra đã có bản ghi score chưa
            $this->db->query(
                "SELECT id FROM evaluation_scores WHERE period_id = :pid AND employee_id = :eid AND criteria_id = :cid",
                ['pid' => $periodId, 'eid' => $employeeId, 'cid' => $cid]
            );
            $existing = $this->db->fetch();

            if ($existing) {
                if ($evalRole === 'self') {
                    $this->db->query(
                        "UPDATE evaluation_scores SET self_score = :s, comment = COALESCE(:cmt, comment) WHERE id = :id",
                        ['s' => $score, 'cmt' => $comment, 'id' => $existing['id']]
                    );
                } else {
                    $this->db->query(
                        "UPDATE evaluation_scores SET manager_score = :s, final_score = :s, comment = COALESCE(:cmt, comment) WHERE id = :id",
                        ['s' => $score, 'cmt' => $comment, 'id' => $existing['id']]
                    );
                }
            } else {
                $selfScore = ($evalRole === 'self') ? $score : null;
                $mgrScore = ($evalRole === 'manager') ? $score : null;
                $finalScore = $mgrScore;

                $this->db->query(
                    "INSERT INTO evaluation_scores (period_id, employee_id, criteria_id, self_score, manager_score, final_score, comment, created_at)
                     VALUES (:pid, :eid, :cid, :self, :mgr, :final, :cmt, NOW())",
                    [
                        'pid'   => $periodId,
                        'eid'   => $employeeId,
                        'cid'   => $cid,
                        'self'  => $selfScore,
                        'mgr'   => $mgrScore,
                        'final' => $finalScore,
                        'cmt'   => $comment,
                    ]
                );
            }
        }

        // Điểm số quy đổi
        $finalScore = round($weightedSum, 1);
        $kpiScore = $kpiWeight > 0 ? round($kpiSum / $kpiWeight, 1) : $finalScore;
        $compScore = $compWeight > 0 ? round($compSum / $compWeight, 1) : $finalScore;

        // Xếp loại Grade
        if ($finalScore >= 90) {
            $grade = 'A'; // Xuất sắc
        } elseif ($finalScore >= 75) {
            $grade = 'B'; // Tốt
        } elseif ($finalScore >= 60) {
            $grade = 'C'; // Đạt
        } else {
            $grade = 'D'; // Cần cải thiện
        }

        $evalStatus = ($evalRole === 'self') ? 'Self_Evaluated' : 'Approved';
        $strengths = $data['strengths'] ?? null;
        $improvements = $data['improvements'] ?? null;
        $notes = $data['notes'] ?? null;
        $evalYear = (int)date('Y', strtotime($period->start_date));

        // Cập nhật hoặc thêm vào emp_evaluations
        $this->db->query(
            "SELECT id FROM emp_evaluations WHERE period_id = :pid AND employee_id = :eid",
            ['pid' => $periodId, 'eid' => $employeeId]
        );
        $evalRecord = $this->db->fetch();

        if ($evalRecord) {
            $this->db->query(
                "UPDATE emp_evaluations
                 SET kpi_score = :kpi,
                     competency_score = :comp,
                     final_score = :final,
                     overall_grade = :grade,
                     status = :status,
                     evaluator_id = COALESCE(:eval_id, evaluator_id),
                     strengths = COALESCE(:str, strengths),
                     improvements = COALESCE(:imp, improvements),
                     notes = COALESCE(:notes, notes)
                 WHERE id = :id",
                [
                    'kpi'     => $kpiScore,
                    'comp'    => $compScore,
                    'final'   => $finalScore,
                    'grade'   => $grade,
                    'status'  => $evalStatus,
                    'eval_id' => $evaluatorId,
                    'str'     => $strengths,
                    'imp'     => $improvements,
                    'notes'   => $notes,
                    'id'      => $evalRecord['id']
                ]
            );
        } else {
            $this->db->query(
                "INSERT INTO emp_evaluations 
                 (employee_id, eval_year, eval_period, period_id, template_id, evaluator_id, kpi_score, competency_score, final_score, overall_grade, status, strengths, improvements, notes, created_at)
                 VALUES (:eid, :year, :pname, :pid, :tid, :eval_id, :kpi, :comp, :final, :grade, :status, :str, :imp, :notes, NOW())",
                [
                    'eid'     => $employeeId,
                    'year'    => $evalYear,
                    'pname'   => $period->name,
                    'pid'     => $periodId,
                    'tid'     => $period->template_id,
                    'eval_id' => $evaluatorId,
                    'kpi'     => $kpiScore,
                    'comp'    => $compScore,
                    'final'   => $finalScore,
                    'grade'   => $grade,
                    'status'  => $evalStatus,
                    'str'     => $strengths,
                    'imp'     => $improvements,
                    'notes'   => $notes,
                ]
            );
        }

        return true;
    }

    // ══════════════════════════════════════════════════════════
    //  4. TỔNG HỢP KẾT QUẢ & RADAR ANALYTICS (Summary)
    // ══════════════════════════════════════════════════════════

    /**
     * Lấy báo cáo tổng hợp chu kỳ đánh giá (Department breakdown, Grade distribution, Radar categories)
     */
    public function getPeriodSummary(int $periodId): array
    {
        $period = $this->getPeriodById($periodId);
        if (!$period) return [];

        // 1. Thống kê xếp loại (Grade Distribution)
        $this->db->query(
            "SELECT overall_grade, COUNT(*) AS count
             FROM emp_evaluations
             WHERE period_id = :pid AND status IN ('Manager_Evaluated', 'Approved')
             GROUP BY overall_grade",
            ['pid' => $periodId]
        );
        $gradeCounts = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0];
        foreach ($this->db->fetchAll() as $r) {
            if (isset($gradeCounts[$r['overall_grade']])) {
                $gradeCounts[$r['overall_grade']] = (int)$r['count'];
            }
        }

        // 2. Thống kê theo phòng ban
        $deptSql = "SELECT d.id, d.dept_name, d.dept_code,
                           COUNT(DISTINCT e.id) AS total_employees,
                           COUNT(DISTINCT ee.employee_id) AS evaluated_count,
                           ROUND(AVG(ee.final_score), 1) AS avg_score,
                           ROUND(AVG(ee.kpi_score), 1) AS avg_kpi,
                           ROUND(AVG(ee.competency_score), 1) AS avg_comp
                    FROM departments d
                    JOIN employees e ON d.id = e.department_id AND e.status != 'Resigned'
                    LEFT JOIN emp_evaluations ee ON (e.id = ee.employee_id AND ee.period_id = :pid AND ee.status IN ('Manager_Evaluated', 'Approved'))
                    GROUP BY d.id
                    ORDER BY avg_score DESC";
        $this->db->query($deptSql, ['pid' => $periodId]);
        $deptSummary = $this->db->fetchAll();

        // 3. Dữ liệu Radar trung bình theo nhóm tiêu chí (Categories)
        $radarSql = "SELECT c.category, ROUND(AVG(s.final_score), 2) AS avg_score
                     FROM evaluation_criteria c
                     JOIN evaluation_scores s ON c.id = s.criteria_id
                     WHERE s.period_id = :pid AND s.final_score IS NOT NULL
                     GROUP BY c.category";
        $this->db->query($radarSql, ['pid' => $periodId]);
        $radarCategories = $this->db->fetchAll();

        // 4. Top 5 nhân viên xuất sắc nhất
        $topSql = "SELECT e.id, e.emp_code, e.full_name, e.avatar_path, d.dept_name, p.pos_title,
                          ee.final_score, ee.overall_grade
                   FROM emp_evaluations ee
                   JOIN employees e ON ee.employee_id = e.id
                   LEFT JOIN departments d ON e.department_id = d.id
                   LEFT JOIN positions p ON e.position_id = p.id
                   WHERE ee.period_id = :pid AND ee.status IN ('Manager_Evaluated', 'Approved')
                   ORDER BY ee.final_score DESC LIMIT 5";
        $this->db->query($topSql, ['pid' => $periodId]);
        $topPerformers = $this->db->fetchAll();

        return [
            'period'           => $period,
            'grade_counts'     => $gradeCounts,
            'dept_summary'     => $deptSummary,
            'radar_categories' => $radarCategories,
            'top_performers'   => $topPerformers,
        ];
    }

    /**
     * Dữ liệu Radar năng lực cá nhân của 1 nhân viên
     */
    public function getEmployeeRadarData(int $periodId, int $employeeId): array
    {
        $sql = "SELECT c.category, ROUND(AVG(s.final_score), 2) AS avg_score
                FROM evaluation_criteria c
                JOIN evaluation_scores s ON c.id = s.criteria_id
                WHERE s.period_id = :pid AND s.employee_id = :eid AND s.final_score IS NOT NULL
                GROUP BY c.category";

        $this->db->query($sql, ['pid' => $periodId, 'eid' => $employeeId]);
        return $this->db->fetchAll();
    }

    // ══════════════════════════════════════════════════════════
    //  5. HỒ SƠ 360 ĐỘ NHÂN VIÊN (Employee Profile 360°)
    // ══════════════════════════════════════════════════════════

    /**
     * Lấy toàn bộ lịch sử đánh giá của 1 nhân viên
     */
    public function getByEmployee(int $employeeId): array
    {
        $sql = "SELECT ee.*,
                       p.name AS period_name,
                       p.start_date,
                       p.end_date,
                       t.name AS template_name,
                       u.username AS evaluator_name
                FROM emp_evaluations ee
                LEFT JOIN evaluation_periods p ON ee.period_id = p.id
                LEFT JOIN evaluation_templates t ON ee.template_id = t.id
                LEFT JOIN users u ON ee.evaluator_id = u.id
                WHERE ee.employee_id = :eid
                ORDER BY COALESCE(p.start_date, ee.created_at) DESC, ee.id DESC";

        $this->db->query($sql, ['eid' => $employeeId]);
        $evaluations = $this->db->fetchAll();

        // Gắn dữ liệu radar cho kỳ đánh giá gần nhất
        foreach ($evaluations as &$ev) {
            if (!empty($ev['period_id'])) {
                $ev['radar'] = $this->getEmployeeRadarData((int)$ev['period_id'], $employeeId);
            }
        }

        return $evaluations;
    }

    // ══════════════════════════════════════════════════════════
    //  6. PERFORMANCE 360° & GOALS / KRA (Phân hệ nâng cấp V2)
    // ══════════════════════════════════════════════════════════

    /**
     * Lấy danh sách Goals / KRA của 1 nhân viên trong kỳ
     */
    public function getGoals(int $periodId, int $employeeId): array
    {
        $this->db->query(
            "SELECT * FROM performance_goals 
             WHERE period_id = :pid AND employee_id = :eid 
             ORDER BY id ASC",
            ['pid' => $periodId, 'eid' => $employeeId]
        );
        return $this->db->fetchAll();
    }

    /**
     * Lưu danh sách Goals / KRA (kiểm tra tổng trọng số = 100%)
     */
    public function saveGoals(int $periodId, int $employeeId, array $goalsData, string $status = 'Draft'): array
    {
        if (empty($goalsData)) {
            return ['success' => false, 'message' => 'Vui lòng thêm ít nhất một mục tiêu KRA.'];
        }

        $totalWeight = 0;
        foreach ($goalsData as $g) {
            $totalWeight += (int)($g['weightage'] ?? 0);
        }

        if ($totalWeight !== 100) {
            return [
                'success' => false, 
                'message' => "Tổng trọng số của các mục tiêu KRA phải bằng đúng 100% (Hiện tại: {$totalWeight}%)."
            ];
        }

        // Lấy danh sách ID hiện tại
        $existing = $this->getGoals($periodId, $employeeId);
        $existingIds = array_column($existing, 'id');
        $keptIds = [];

        foreach ($goalsData as $g) {
            $goalId = !empty($g['id']) ? (int)$g['id'] : 0;
            $title = trim($g['kra_title'] ?? '');
            $desc = trim($g['description'] ?? '');
            $weight = (int)($g['weightage'] ?? 0);
            $target = trim($g['target_metric'] ?? '');

            if (empty($title)) continue;

            if ($goalId > 0 && in_array($goalId, $existingIds)) {
                $this->db->query(
                    "UPDATE performance_goals 
                     SET kra_title = :title, description = :desc, weightage = :weight, 
                         target_metric = :target, status = :status, updated_at = NOW()
                     WHERE id = :id AND period_id = :pid AND employee_id = :eid",
                    [
                        'title'  => $title,
                        'desc'   => $desc,
                        'weight' => $weight,
                        'target' => $target,
                        'status' => $status,
                        'id'     => $goalId,
                        'pid'    => $periodId,
                        'eid'    => $employeeId,
                    ]
                );
                $keptIds[] = $goalId;
            } else {
                $this->db->query(
                    "INSERT INTO performance_goals 
                     (period_id, employee_id, kra_title, description, weightage, target_metric, status, created_at)
                     VALUES (:pid, :eid, :title, :desc, :weight, :target, :status, NOW())",
                    [
                        'pid'    => $periodId,
                        'eid'    => $employeeId,
                        'title'  => $title,
                        'desc'   => $desc,
                        'weight' => $weight,
                        'target' => $target,
                        'status' => $status,
                    ]
                );
                $keptIds[] = (int)$this->db->lastInsertId();
            }
        }

        // Xóa các goals đã bị gỡ bỏ
        $toDelete = array_diff($existingIds, $keptIds);
        if (!empty($toDelete)) {
            $inClause = implode(',', array_map('intval', $toDelete));
            $this->db->query("DELETE FROM performance_goals WHERE id IN ($inClause) AND period_id = :pid AND employee_id = :eid", [
                'pid' => $periodId,
                'eid' => $employeeId,
            ]);
        }

        return ['success' => true, 'message' => 'Lưu mục tiêu KRA thành công!'];
    }

    /**
     * Lấy tổng quan danh sách nhân viên trong kỳ kèm trạng thái Goals và 360 Review
     */
    public function getGoalsSummaryByPeriod(int $periodId, array $filters = []): array
    {
        $period = $this->getPeriodById($periodId);
        if (!$period) return [];

        $sql = "SELECT e.id, e.employee_code, e.full_name, e.avatar, e.department_id, e.position_id,
                       d.dept_name, d.dept_code,
                       pos.pos_title,
                       m.full_name AS manager_name,
                       COUNT(DISTINCT g.id) AS kra_count,
                       COALESCE(SUM(g.weightage), 0) AS total_weight,
                       MAX(g.status) AS kra_status,
                       ROUND(AVG(g.self_score), 1) AS avg_self_score,
                       ROUND(AVG(g.manager_score), 1) AS avg_mgr_score,
                       ROUND(AVG(g.final_score), 1) AS avg_final_score,
                       -- 360 review status
                       (SELECT COUNT(*) FROM performance_reviews_360 r WHERE r.period_id = :pid1 AND r.employee_id = e.id AND r.relationship = 'Self' AND r.status = 'Submitted') AS self_reviewed,
                       (SELECT COUNT(*) FROM performance_reviews_360 r WHERE r.period_id = :pid2 AND r.employee_id = e.id AND r.relationship = 'Manager' AND r.status = 'Submitted') AS manager_reviewed,
                       (SELECT COUNT(*) FROM performance_reviews_360 r WHERE r.period_id = :pid3 AND r.employee_id = e.id AND r.relationship = 'Peer') AS peer_assigned_count,
                       (SELECT COUNT(*) FROM performance_reviews_360 r WHERE r.period_id = :pid4 AND r.employee_id = e.id AND r.relationship = 'Peer' AND r.status = 'Submitted') AS peer_submitted_count,
                       ee.final_score AS overall_final_score,
                       ee.overall_grade,
                       ee.status AS eval_status
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions pos ON e.position_id = pos.id
                LEFT JOIN employees m ON e.direct_manager_id = m.id
                LEFT JOIN performance_goals g ON (e.id = g.employee_id AND g.period_id = :pid5)
                LEFT JOIN emp_evaluations ee ON (e.id = ee.employee_id AND ee.period_id = :pid6)
                WHERE e.status != 'Resigned'";

        $params = [
            'pid1' => $periodId,
            'pid2' => $periodId,
            'pid3' => $periodId,
            'pid4' => $periodId,
            'pid5' => $periodId,
            'pid6' => $periodId,
        ];

        if (!empty($period->department_id)) {
            $sql .= " AND e.department_id = :period_dept";
            $params['period_dept'] = (int)$period->department_id;
        }

        if (!empty($filters['dept_id'])) {
            $sql .= " AND e.department_id = :f_dept";
            $params['f_dept'] = (int)$filters['dept_id'];
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'No_Goals') {
                $sql .= " AND g.id IS NULL";
            } elseif ($filters['status'] === 'Draft') {
                $sql .= " AND g.status = 'Draft'";
            } elseif ($filters['status'] === 'Submitted') {
                $sql .= " AND g.status = 'Submitted'";
            } elseif ($filters['status'] === 'Reviewed') {
                $sql .= " AND g.status = 'Reviewed'";
            }
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (e.full_name LIKE :search OR e.employee_code LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " GROUP BY e.id ORDER BY d.dept_name ASC, e.employee_code ASC";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Nhân viên nộp kết quả tự đánh giá Goals và nhận xét 360° (Self Review)
     */
    public function submitSelfReviewGoals(int $periodId, int $employeeId, array $data): bool
    {
        $achievements = $data['achievements'] ?? [];
        $selfScores = $data['self_scores'] ?? [];

        // 1. Cập nhật actual_achievement và self_score vào performance_goals
        $goals = $this->getGoals($periodId, $employeeId);
        $totalWeight = 0;
        $weightedSelfScore = 0;

        foreach ($goals as $g) {
            $gid = (int)$g['id'];
            $achieve = $achievements[$gid] ?? $g['actual_achievement'];
            $score = isset($selfScores[$gid]) && $selfScores[$gid] !== '' ? (float)$selfScores[$gid] : 0;
            $weight = (int)$g['weightage'];

            $this->db->query(
                "UPDATE performance_goals 
                 SET actual_achievement = :achieve, self_score = :score, status = 'Submitted', updated_at = NOW()
                 WHERE id = :id AND period_id = :pid AND employee_id = :eid",
                [
                    'achieve' => $achieve,
                    'score'   => $score,
                    'id'      => $gid,
                    'pid'     => $periodId,
                    'eid'     => $employeeId,
                ]
            );

            $weightedSelfScore += ($score * ($weight / 100));
            $totalWeight += $weight;
        }

        // 2. Tính overall score tự chấm
        $overallScore = isset($data['overall_score']) && $data['overall_score'] !== '' 
            ? (float)$data['overall_score'] 
            : ($totalWeight > 0 ? round($weightedSelfScore, 2) : 0);

        // 3. Lưu vào bảng performance_reviews_360 (relationship = 'Self')
        $this->saveReview360($periodId, $employeeId, $employeeId, 'Self', [
            'overall_score' => $overallScore,
            'strengths'     => $data['strengths'] ?? null,
            'improvements'  => $data['improvements'] ?? null,
            'comments'      => $data['comments'] ?? null,
            'kra_scores'    => $selfScores,
            'status'        => 'Submitted',
        ]);

        // 4. Cập nhật hoặc tạo emp_evaluations trạng thái Self_Evaluated
        $this->db->query(
            "SELECT id FROM emp_evaluations WHERE period_id = :pid AND employee_id = :eid LIMIT 1",
            ['pid' => $periodId, 'eid' => $employeeId]
        );
        $existing = $this->db->fetch();

        if ($existing) {
            $this->db->query(
                "UPDATE emp_evaluations 
                 SET status = 'Self_Evaluated', strengths = :s, improvements = :imp 
                 WHERE id = :id",
                [
                    's'   => $data['strengths'] ?? null,
                    'imp' => $data['improvements'] ?? null,
                    'id'  => $existing['id'],
                ]
            );
        } else {
            $period = $this->getPeriodById($periodId);
            $this->db->query(
                "INSERT INTO emp_evaluations 
                 (employee_id, eval_year, eval_period, period_id, template_id, status, strengths, improvements, created_at)
                 VALUES (:eid, :yr, :pname, :pid, :tid, 'Self_Evaluated', :s, :imp, NOW())",
                [
                    'eid'   => $employeeId,
                    'yr'    => date('Y', strtotime($period->start_date ?? 'now')),
                    'pname' => $period->name ?? 'Kỳ đánh giá',
                    'pid'   => $periodId,
                    'tid'   => $period->template_id ?? null,
                    's'     => $data['strengths'] ?? null,
                    'imp'   => $data['improvements'] ?? null,
                ]
            );
        }

        return true;
    }

    /**
     * Lấy các đánh giá 360 độ của 1 nhân viên
     */
    public function getReviews360(int $periodId, int $employeeId): array
    {
        $sql = "SELECT r.*,
                       e.full_name AS reviewer_name,
                       e.employee_code AS reviewer_code,
                       d.dept_name AS reviewer_dept,
                       pos.pos_title AS reviewer_pos
                FROM performance_reviews_360 r
                LEFT JOIN employees e ON r.reviewer_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions pos ON e.position_id = pos.id
                WHERE r.period_id = :pid AND r.employee_id = :eid
                ORDER BY FIELD(r.relationship, 'Self', 'Manager', 'Peer', 'Subordinate'), r.id ASC";

        $this->db->query($sql, ['pid' => $periodId, 'eid' => $employeeId]);
        return $this->db->fetchAll();
    }

    /**
     * Lấy 1 bản ghi review 360 cụ thể
     */
    public function getReview360(int $periodId, int $employeeId, int $reviewerId): ?object
    {
        $this->db->query(
            "SELECT * FROM performance_reviews_360 
             WHERE period_id = :pid AND employee_id = :eid AND reviewer_id = :rid 
             LIMIT 1",
            ['pid' => $periodId, 'eid' => $employeeId, 'rid' => $reviewerId]
        );
        $row = $this->db->fetch();
        return $row ? (object)$row : null;
    }

    /**
     * Lưu kết quả đánh giá 360° (Manager / Peer / Subordinate / Self)
     */
    public function saveReview360(int $periodId, int $employeeId, int $reviewerId, string $relationship, array $data): bool
    {
        $overallScore = isset($data['overall_score']) && $data['overall_score'] !== '' ? (float)$data['overall_score'] : null;
        $strengths = $data['strengths'] ?? null;
        $improvements = $data['improvements'] ?? null;
        $comments = $data['comments'] ?? null;
        $kraScores = !empty($data['kra_scores']) ? json_encode($data['kra_scores']) : null;
        $status = $data['status'] ?? 'Submitted';
        $submittedAt = ($status === 'Submitted') ? date('Y-m-d H:i:s') : null;

        $existing = $this->getReview360($periodId, $employeeId, $reviewerId);

        if ($existing) {
            $this->db->query(
                "UPDATE performance_reviews_360 
                 SET relationship = :rel,
                     overall_score = :score,
                     strengths = :str,
                     improvements = :imp,
                     comments = :comm,
                     kra_scores = :kras,
                     status = :status,
                     submitted_at = COALESCE(:sub_at, submitted_at),
                     updated_at = NOW()
                 WHERE id = :id",
                [
                    'rel'    => $relationship,
                    'score'  => $overallScore,
                    'str'    => $strengths,
                    'imp'    => $improvements,
                    'comm'   => $comments,
                    'kras'   => $kraScores,
                    'status' => $status,
                    'sub_at' => $submittedAt,
                    'id'     => $existing->id,
                ]
            );
        } else {
            $this->db->query(
                "INSERT INTO performance_reviews_360 
                 (period_id, employee_id, reviewer_id, relationship, overall_score, strengths, improvements, comments, kra_scores, status, submitted_at, created_at)
                 VALUES (:pid, :eid, :rid, :rel, :score, :str, :imp, :comm, :kras, :status, :sub_at, NOW())",
                [
                    'pid'    => $periodId,
                    'eid'    => $employeeId,
                    'rid'    => $reviewerId,
                    'rel'    => $relationship,
                    'score'  => $overallScore,
                    'str'    => $strengths,
                    'imp'    => $improvements,
                    'comm'   => $comments,
                    'kras'   => $kraScores,
                    'status' => $status,
                    'sub_at' => $submittedAt,
                ]
            );
        }

        // Nếu là Manager đánh giá: Cập nhật manager_score vào từng goal trong performance_goals
        if ($relationship === 'Manager' && !empty($data['kra_scores']) && is_array($data['kra_scores'])) {
            foreach ($data['kra_scores'] as $goalId => $mScore) {
                if ($mScore !== '') {
                    $this->db->query(
                        "UPDATE performance_goals 
                         SET manager_score = :mscore, updated_at = NOW()
                         WHERE id = :id AND period_id = :pid AND employee_id = :eid",
                        [
                            'mscore' => (float)$mScore,
                            'id'     => (int)$goalId,
                            'pid'    => $periodId,
                            'eid'    => $employeeId,
                        ]
                    );
                }
            }
        }

        return true;
    }

    /**
     * HR chỉ định Peer Reviewers cho nhân viên
     */
    public function assignPeerReviewers(int $periodId, int $employeeId, array $peerIds): bool
    {
        // Lấy danh sách Peer Reviewers hiện tại của nhân viên
        $this->db->query(
            "SELECT id, reviewer_id, status FROM performance_reviews_360 
             WHERE period_id = :pid AND employee_id = :eid AND relationship = 'Peer'",
            ['pid' => $periodId, 'eid' => $employeeId]
        );
        $currentPeers = $this->db->fetchAll();
        $currentPeerIds = array_column($currentPeers, 'reviewer_id');

        // Xóa những peer chưa submit mà bị bỏ chọn
        foreach ($currentPeers as $cp) {
            if (!in_array($cp['reviewer_id'], $peerIds) && $cp['status'] === 'Pending') {
                $this->db->query("DELETE FROM performance_reviews_360 WHERE id = :id", ['id' => $cp['id']]);
            }
        }

        // Thêm những peer mới được phân công
        foreach ($peerIds as $pId) {
            $pId = (int)$pId;
            if ($pId <= 0 || $pId === $employeeId) continue;

            if (!in_array($pId, $currentPeerIds)) {
                $this->db->query(
                    "INSERT INTO performance_reviews_360 
                     (period_id, employee_id, reviewer_id, relationship, status, created_at)
                     VALUES (:pid, :eid, :rid, 'Peer', 'Pending', NOW())
                     ON DUPLICATE KEY UPDATE relationship = 'Peer'",
                    [
                        'pid' => $periodId,
                        'eid' => $employeeId,
                        'rid' => $pId,
                    ]
                );
            }
        }

        return true;
    }

    /**
     * Lấy các bài đánh giá 360 đang phân công cho 1 reviewer
     */
    public function getAssignedReviewsForEmployee(int $reviewerId, ?int $periodId = null): array
    {
        $sql = "SELECT r.*,
                       e.full_name AS target_emp_name,
                       e.employee_code AS target_emp_code,
                       d.dept_name AS target_emp_dept,
                       pos.pos_title AS target_emp_pos,
                       p.name AS period_name,
                       p.end_date AS period_end_date,
                       p.status AS period_status
                FROM performance_reviews_360 r
                JOIN employees e ON r.employee_id = e.id
                JOIN evaluation_periods p ON r.period_id = p.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions pos ON e.position_id = pos.id
                WHERE r.reviewer_id = :rid";

        $params = ['rid' => $reviewerId];
        if ($periodId) {
            $sql .= " AND r.period_id = :pid";
            $params['pid'] = $periodId;
        }

        $sql .= " ORDER BY FIELD(r.status, 'Pending', 'Submitted'), p.start_date DESC";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Lấy toàn bộ dữ liệu so sánh 360 độ để Manager / HR chốt điểm (Final Score)
     */
    public function get360ComparisonData(int $periodId, int $employeeId): ?array
    {
        $period = $this->getPeriodById($periodId);
        if (!$period) return null;

        $this->db->query(
            "SELECT e.*, d.dept_name, pos.pos_title, m.full_name AS manager_name
             FROM employees e
             LEFT JOIN departments d ON e.department_id = d.id
             LEFT JOIN positions pos ON e.position_id = pos.id
             LEFT JOIN employees m ON e.direct_manager_id = m.id
             WHERE e.id = :eid LIMIT 1",
            ['eid' => $employeeId]
        );
        $employee = $this->db->fetch();
        if (!$employee) return null;

        $goals = $this->getGoals($periodId, $employeeId);
        $reviews = $this->getReviews360($periodId, $employeeId);

        // Phân loại reviews theo relationship
        $selfReview = null;
        $managerReview = null;
        $peerReviews = [];
        $subReviews = [];

        foreach ($reviews as $rev) {
            if ($rev['relationship'] === 'Self' && $rev['status'] === 'Submitted') {
                $selfReview = (object)$rev;
            } elseif ($rev['relationship'] === 'Manager' && $rev['status'] === 'Submitted') {
                $managerReview = (object)$rev;
            } elseif ($rev['relationship'] === 'Peer') {
                $peerReviews[] = (object)$rev;
            } elseif ($rev['relationship'] === 'Subordinate') {
                $subReviews[] = (object)$rev;
            }
        }

        // Tính điểm trung bình Peer cho từng KRA và tổng thể
        $peerKraScores = [];
        $submittedPeersCount = 0;
        $peerOverallSum = 0;

        foreach ($peerReviews as $pr) {
            if ($pr->status === 'Submitted') {
                $submittedPeersCount++;
                if ($pr->overall_score !== null) {
                    $peerOverallSum += (float)$pr->overall_score;
                }
                if (!empty($pr->kra_scores)) {
                    $parsed = is_string($pr->kra_scores) ? json_decode($pr->kra_scores, true) : $pr->kra_scores;
                    if (is_array($parsed)) {
                        foreach ($parsed as $gid => $sc) {
                            $peerKraScores[$gid][] = (float)$sc;
                        }
                    }
                }
            }
        }

        $peerOverallAvg = $submittedPeersCount > 0 ? round($peerOverallSum / $submittedPeersCount, 1) : null;

        // Bổ sung peer_avg_score vào từng goal
        foreach ($goals as &$g) {
            $gid = (int)$g['id'];
            if (!empty($peerKraScores[$gid])) {
                $g['peer_score'] = round(array_sum($peerKraScores[$gid]) / count($peerKraScores[$gid]), 1);
            } else {
                $g['peer_score'] = null;
            }
        }

        // Xây dựng dữ liệu Radar Chart (KRA Labels, Self, Manager, Peer)
        $radarLabels = [];
        $radarSelf = [];
        $radarManager = [];
        $radarPeer = [];

        foreach ($goals as $g) {
            $radarLabels[] = $g['kra_title'];
            $radarSelf[] = $g['self_score'] !== null ? (float)$g['self_score'] : null;
            $radarManager[] = $g['manager_score'] !== null ? (float)$g['manager_score'] : null;
            $radarPeer[] = $g['peer_score'] !== null ? (float)$g['peer_score'] : null;
        }

        // Tính điểm khuyến nghị (Weighted average: Manager 60%, Peer 25%, Self 15% hoặc Manager 70% Self 30%)
        $mScore = $managerReview && $managerReview->overall_score !== null ? (float)$managerReview->overall_score : null;
        $sScore = $selfReview && $selfReview->overall_score !== null ? (float)$selfReview->overall_score : null;

        $recommendedScore = null;
        if ($mScore !== null && $peerOverallAvg !== null && $sScore !== null) {
            $recommendedScore = round(($mScore * 0.60) + ($peerOverallAvg * 0.25) + ($sScore * 0.15), 1);
        } elseif ($mScore !== null && $sScore !== null) {
            $recommendedScore = round(($mScore * 0.70) + ($sScore * 0.30), 1);
        } elseif ($mScore !== null) {
            $recommendedScore = $mScore;
        } elseif ($sScore !== null) {
            $recommendedScore = $sScore;
        }

        // Lấy kết quả hiện tại trong emp_evaluations nếu đã có
        $this->db->query(
            "SELECT * FROM emp_evaluations WHERE period_id = :pid AND employee_id = :eid LIMIT 1",
            ['pid' => $periodId, 'eid' => $employeeId]
        );
        $existingEval = $this->db->fetch();

        return [
            'period'            => $period,
            'employee'          => (object)$employee,
            'goals'             => $goals,
            'self_review'       => $selfReview,
            'manager_review'    => $managerReview,
            'peer_reviews'      => $peerReviews,
            'sub_reviews'       => $subReviews,
            'peer_overall_avg'  => $peerOverallAvg,
            'peer_count'        => $submittedPeersCount,
            'radar_labels'      => $radarLabels,
            'radar_self'        => $radarSelf,
            'radar_manager'     => $radarManager,
            'radar_peer'        => $radarPeer,
            'recommended_score' => $recommendedScore,
            'existing_eval'     => $existingEval ? (object)$existingEval : null,
        ];
    }

    /**
     * Chốt điểm đánh giá cuối cùng (Final Score)
     */
    public function saveFinalScore(int $periodId, int $employeeId, array $data, int $evaluatorId): bool
    {
        $finalScores = $data['goal_final_scores'] ?? [];
        $overallFinal = (float)($data['final_score'] ?? 0);
        $grade = $data['overall_grade'] ?? 'B';
        $strengths = $data['strengths'] ?? null;
        $improvements = $data['improvements'] ?? null;
        $notes = $data['notes'] ?? null;

        // 1. Cập nhật final_score vào từng goal trong performance_goals
        foreach ($finalScores as $gid => $fScore) {
            if ($fScore !== '') {
                $this->db->query(
                    "UPDATE performance_goals 
                     SET final_score = :fscore, status = 'Reviewed', updated_at = NOW()
                     WHERE id = :id AND period_id = :pid AND employee_id = :eid",
                    [
                        'fscore' => (float)$fScore,
                        'id'     => (int)$gid,
                        'pid'    => $periodId,
                        'eid'    => $employeeId,
                    ]
                );
            }
        }

        // 2. Tự động tính xếp loại nếu chưa có
        if (empty($grade)) {
            if ($overallFinal >= 90) $grade = 'A';
            elseif ($overallFinal >= 75) $grade = 'B';
            elseif ($overallFinal >= 60) $grade = 'C';
            else $grade = 'D';
        }

        // 3. Cập nhật hoặc lưu vào emp_evaluations
        $this->db->query(
            "SELECT id FROM emp_evaluations WHERE period_id = :pid AND employee_id = :eid LIMIT 1",
            ['pid' => $periodId, 'eid' => $employeeId]
        );
        $existing = $this->db->fetch();

        if ($existing) {
            $this->db->query(
                "UPDATE emp_evaluations 
                 SET final_score = :fscore,
                     overall_grade = :grade,
                     evaluator_id = :eval_id,
                     status = 'Approved',
                     strengths = :str,
                     improvements = :imp,
                     notes = :notes
                 WHERE id = :id",
                [
                    'fscore'  => $overallFinal,
                    'grade'   => $grade,
                    'eval_id' => $evaluatorId,
                    'str'     => $strengths,
                    'imp'     => $improvements,
                    'notes'   => $notes,
                    'id'      => $existing['id'],
                ]
            );
        } else {
            $period = $this->getPeriodById($periodId);
            $this->db->query(
                "INSERT INTO emp_evaluations 
                 (employee_id, eval_year, eval_period, period_id, template_id, evaluator_id, final_score, overall_grade, status, strengths, improvements, notes, created_at)
                 VALUES (:eid, :yr, :pname, :pid, :tid, :eval_id, :fscore, :grade, 'Approved', :str, :imp, :notes, NOW())",
                [
                    'eid'     => $employeeId,
                    'yr'      => date('Y', strtotime($period->start_date ?? 'now')),
                    'pname'   => $period->name ?? 'Kỳ đánh giá',
                    'pid'     => $periodId,
                    'tid'     => $period->template_id ?? null,
                    'eval_id' => $evaluatorId,
                    'fscore'  => $overallFinal,
                    'grade'   => $grade,
                    'str'     => $strengths,
                    'imp'     => $improvements,
                    'notes'   => $notes,
                ]
            );
        }

        return true;
    }

    /**
     * Dashboard phân tích 360° (Bell Curve, Top/Bottom Performers, Radar Benchmark, Department stats)
     */
    public function get360Analytics(int $periodId): array
    {
        $period = $this->getPeriodById($periodId);
        if (!$period) return [];

        // 1. Thống kê tiến độ theo quy trình 4 bước: Self → Peer → Manager → Final
        $empSql = "SELECT COUNT(*) as cnt FROM employees WHERE status != 'Resigned'";
        $empParams = [];
        if (!empty($period->department_id)) {
            $empSql .= " AND department_id = :dept_id";
            $empParams['dept_id'] = (int)$period->department_id;
        }
        $this->db->query($empSql, $empParams);
        $totalEligible = (int)($this->db->fetch()['cnt'] ?? 0);

        // Số đã tạo Goals
        $this->db->query(
            "SELECT COUNT(DISTINCT employee_id) as cnt FROM performance_goals WHERE period_id = :pid",
            ['pid' => $periodId]
        );
        $goalsCount = (int)($this->db->fetch()['cnt'] ?? 0);

        // Số đã hoàn thành Self Review
        $this->db->query(
            "SELECT COUNT(DISTINCT employee_id) as cnt FROM performance_reviews_360 
             WHERE period_id = :pid AND relationship = 'Self' AND status = 'Submitted'",
            ['pid' => $periodId]
        );
        $selfCount = (int)($this->db->fetch()['cnt'] ?? 0);

        // Số đã có Peer Review
        $this->db->query(
            "SELECT COUNT(DISTINCT employee_id) as cnt FROM performance_reviews_360 
             WHERE period_id = :pid AND relationship = 'Peer' AND status = 'Submitted'",
            ['pid' => $periodId]
        );
        $peerCount = (int)($this->db->fetch()['cnt'] ?? 0);

        // Số đã có Manager Review
        $this->db->query(
            "SELECT COUNT(DISTINCT employee_id) as cnt FROM performance_reviews_360 
             WHERE period_id = :pid AND relationship = 'Manager' AND status = 'Submitted'",
            ['pid' => $periodId]
        );
        $managerCount = (int)($this->db->fetch()['cnt'] ?? 0);

        // Số đã Final chốt điểm
        $this->db->query(
            "SELECT COUNT(*) as cnt FROM emp_evaluations 
             WHERE period_id = :pid AND status = 'Approved' AND final_score IS NOT NULL",
            ['pid' => $periodId]
        );
        $finalCount = (int)($this->db->fetch()['cnt'] ?? 0);

        // 2. Phân bố điểm số (Bell Curve Bins: <50, 50-64, 65-74, 75-84, 85-94, 95-100)
        $this->db->query(
            "SELECT ee.final_score, ee.overall_grade, e.id AS employee_id, e.full_name, e.employee_code, d.dept_name, pos.pos_title
             FROM emp_evaluations ee
             JOIN employees e ON ee.employee_id = e.id
             LEFT JOIN departments d ON e.department_id = d.id
             LEFT JOIN positions pos ON e.position_id = pos.id
             WHERE ee.period_id = :pid AND ee.final_score IS NOT NULL
             ORDER BY ee.final_score DESC",
            ['pid' => $periodId]
        );
        $allEvaluated = $this->db->fetchAll();

        $bellBins = [
            '< 50'   => ['count' => 0, 'label' => 'Yếu (<50)', 'color' => '#ef4444'],
            '50-64'  => ['count' => 0, 'label' => 'Trung bình (50-64)', 'color' => '#f97316'],
            '65-74'  => ['count' => 0, 'label' => 'Khá (65-74)', 'color' => '#f59e0b'],
            '75-84'  => ['count' => 0, 'label' => 'Tốt (75-84)', 'color' => '#3b82f6'],
            '85-94'  => ['count' => 0, 'label' => 'Xuất sắc (85-94)', 'color' => '#10b981'],
            '95-100' => ['count' => 0, 'label' => 'Đặc biệt (95-100)', 'color' => '#8b5cf6'],
        ];

        $scoresList = [];
        foreach ($allEvaluated as $row) {
            $sc = (float)$row['final_score'];
            $scoresList[] = $sc;
            if ($sc < 50) $bellBins['< 50']['count']++;
            elseif ($sc < 65) $bellBins['50-64']['count']++;
            elseif ($sc < 75) $bellBins['65-74']['count']++;
            elseif ($sc < 85) $bellBins['75-84']['count']++;
            elseif ($sc < 95) $bellBins['85-94']['count']++;
            else $bellBins['95-100']['count']++;
        }

        $avgFinalScore = !empty($scoresList) ? round(array_sum($scoresList) / count($scoresList), 1) : 0;

        // 3. Top Performers (Top 5 cao nhất)
        $topPerformers = array_slice($allEvaluated, 0, 5);

        // Bottom Performers (Những nhân sự dưới 75 điểm cần phát triển đào tạo)
        $bottomPerformers = array_filter($allEvaluated, function($row) {
            return (float)$row['final_score'] < 75;
        });
        usort($bottomPerformers, function($a, $b) {
            return (float)$a['final_score'] <=> (float)$b['final_score'];
        });
        $bottomPerformers = array_slice($bottomPerformers, 0, 5);

        // 4. Thống kê theo phòng ban
        $this->db->query(
            "SELECT d.dept_name, d.dept_code,
                    COUNT(ee.id) AS evaluated_count,
                    ROUND(AVG(ee.final_score), 1) AS avg_score,
                    MIN(ee.final_score) AS min_score,
                    MAX(ee.final_score) AS max_score
             FROM emp_evaluations ee
             JOIN employees e ON ee.employee_id = e.id
             JOIN departments d ON e.department_id = d.id
             WHERE ee.period_id = :pid AND ee.final_score IS NOT NULL
             GROUP BY d.id
             ORDER BY avg_score DESC",
            ['pid' => $periodId]
        );
        $deptStats = $this->db->fetchAll();

        // 5. Radar Chart phân tích các nhóm KRA / Năng lực công ty
        $this->db->query(
            "SELECT g.kra_title,
                    ROUND(AVG(g.self_score), 1) AS avg_self,
                    ROUND(AVG(g.manager_score), 1) AS avg_manager,
                    ROUND(AVG(g.final_score), 1) AS avg_final
             FROM performance_goals g
             WHERE g.period_id = :pid
             GROUP BY g.kra_title
             HAVING COUNT(*) >= 1
             ORDER BY COUNT(*) DESC
             LIMIT 8",
            ['pid' => $periodId]
        );
        $kraBenchmarks = $this->db->fetchAll();

        return [
            'period'           => $period,
            'total_eligible'   => $totalEligible,
            'goals_count'      => $goalsCount,
            'self_count'       => $selfCount,
            'peer_count'       => $peerCount,
            'manager_count'    => $managerCount,
            'final_count'      => $finalCount,
            'avg_final_score'  => $avgFinalScore,
            'bell_bins'        => $bellBins,
            'top_performers'   => $topPerformers,
            'bottom_performers'=> $bottomPerformers,
            'dept_stats'       => $deptStats,
            'kra_benchmarks'   => $kraBenchmarks,
        ];
    }
}
