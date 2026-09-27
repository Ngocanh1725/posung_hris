<?php
class Workflow extends BaseModel
{
    public function getMyRequests(int $empId): array
    {
        $this->db->query("
            SELECT r.*, w.name as workflow_name, w.code as workflow_code 
            FROM requests r
            JOIN workflows w ON r.workflow_id = w.id
            WHERE r.emp_id = :e
            ORDER BY r.id DESC
            LIMIT 50
        ", ['e' => $empId]);
        return $this->db->fetchAll();
    }

    public function getPendingApprovals(): array
    {
        // For simulation, admin sees all Pending & In Progress
        $this->db->query("
            SELECT r.*, w.name as workflow_name, e.full_name as emp_name 
            FROM requests r
            JOIN workflows w ON r.workflow_id = w.id
            JOIN employees e ON r.emp_id = e.id
            WHERE r.status IN ('Pending', 'In Progress')
            ORDER BY r.id DESC
            LIMIT 100
        ");
        return $this->db->fetchAll();
    }

    public function processRequest(int $reqId, int $userId, string $action, string $password, string $comment = ''): bool
    {
        // 1. Verify E-Sign Password from Database
        $this->db->query("SELECT esign_pin FROM users WHERE id = :uid", ['uid' => $userId]);
        $user = $this->db->fetch();
        
        $validPin = ($user && !empty($user['esign_pin'])) ? $user['esign_pin'] : 'posung@123';
        
        if ($password !== $validPin) {
            return false;
        }

        // 2. Fetch Request
        $this->db->query("SELECT * FROM requests WHERE id = :id", ['id' => $reqId]);
        $req = $this->db->fetch();
        if (!$req || in_array($req['status'], ['Approved', 'Rejected'])) {
            return false;
        }

        // 3. Update Status
        $newStep = $req['current_step'];
        $newStatus = $req['status'];

        if ($action === 'Approve') {
            if ($req['current_step'] < $req['total_steps']) {
                $newStep++;
                $newStatus = 'In Progress';
            } else {
                $newStatus = 'Approved';
            }
        } else {
            $newStatus = 'Rejected';
        }

        $this->db->query("UPDATE requests SET status = :st, current_step = :stn WHERE id = :id", [
            'st' => $newStatus,
            'stn' => $newStep,
            'id' => $reqId
        ]);

        // 4. Log Action
        $this->db->query("INSERT INTO approval_logs (request_id, approver_id, step, action, comment) VALUES (:rid, :aid, :s, :act, :cmt)", [
            'rid' => $reqId,
            'aid' => $userId,
            's' => $req['current_step'],
            'act' => $action,
            'cmt' => $comment
        ]);

        return true;
    }
}
