<?php
/**
 * View: employee/search.php
 */
?>
<div class="panel mb-4">
    <div class="panel-header"><h3><i class="fas fa-search"></i> Tìm kiếm Nâng cao Hồ sơ Nhân sự</h3></div>
    <div class="panel-body">
        <form method="GET" action="<?= BASE_URL ?>/employee/search">
            <div class="row mb-3">
                <div class="col-md-3 form-group">
                    <label>Từ khóa (Tên, Mã, CCCD, Email, SĐT)</label>
                    <input type="text" name="search" class="form-control" value="<?= h($filters['search']) ?>" placeholder="Nhập từ khóa...">
                </div>
                <div class="col-md-3 form-group">
                    <label>Phòng ban</label>
                    <select name="department_id" class="form-control">
                        <option value="">- Tất cả -</option>
                        <?php foreach($departments as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= $filters['department_id']==$d['id']?'selected':'' ?>><?= $d['dept_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Dự án</label>
                    <select name="project_id" class="form-control">
                        <option value="">- Tất cả -</option>
                        <?php foreach($projects as $p): ?>
                            <option value="<?= $p->id ?>" <?= $filters['project_id']==$p->id?'selected':'' ?>><?= $p->project_name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Chức vụ</label>
                    <select name="position_id" class="form-control">
                        <option value="">- Tất cả -</option>
                        <?php foreach($positions as $pos): ?>
                            <option value="<?= $pos['id'] ?>" <?= $filters['position_id']==$pos['id']?'selected':'' ?>><?= $pos['pos_title'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 form-group">
                    <label>Trạng thái</label>
                    <select name="status" class="form-control">
                        <option value="">- Tất cả -</option>
                        <option value="Active" <?= $filters['status']=='Active'?'selected':'' ?>>Đang làm việc</option>
                        <option value="Probation" <?= $filters['status']=='Probation'?'selected':'' ?>>Thử việc</option>
                        <option value="Resigned" <?= $filters['status']=='Resigned'?'selected':'' ?>>Đã nghỉ việc</option>
                        <option value="Blacklisted" <?= $filters['status']=='Blacklisted'?'selected':'' ?>>Blacklist</option>
                        <option value="Retired" <?= $filters['status']=='Retired'?'selected':'' ?>>Nghỉ hưu</option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Giới tính</label>
                    <select name="gender" class="form-control">
                        <option value="">- Tất cả -</option>
                        <option value="Male" <?= $filters['gender']=='Male'?'selected':'' ?>>Nam</option>
                        <option value="Female" <?= $filters['gender']=='Female'?'selected':'' ?>>Nữ</option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Loại Nhân sự</label>
                    <select name="employee_type" class="form-control">
                        <option value="">- Tất cả -</option>
                        <option value="Office" <?= $filters['employee_type']=='Office'?'selected':'' ?>>Văn phòng</option>
                        <option value="Site_Engineer" <?= $filters['employee_type']=='Site_Engineer'?'selected':'' ?>>Kỹ sư</option>
                        <option value="Direct_Worker" <?= $filters['employee_type']=='Direct_Worker'?'selected':'' ?>>Công nhân</option>
                        <option value="Expat" <?= $filters['employee_type']=='Expat'?'selected':'' ?>>Expat</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4 form-group">
                    <label>Ngày vào làm</label>
                    <div style="display:flex; gap:10px;">
                        <input type="date" name="join_date_from" class="form-control" value="<?= h($filters['join_date_from']) ?>" title="Từ ngày">
                        <input type="date" name="join_date_to" class="form-control" value="<?= h($filters['join_date_to']) ?>" title="Đến ngày">
                    </div>
                </div>
                <div class="col-md-4 form-group">
                    <label>Ngày sinh</label>
                    <div style="display:flex; gap:10px;">
                        <input type="date" name="dob_from" class="form-control" value="<?= h($filters['dob_from']) ?>" title="Từ ngày">
                        <input type="date" name="dob_to" class="form-control" value="<?= h($filters['dob_to']) ?>" title="Đến ngày">
                    </div>
                </div>
                <div class="col-md-4 form-group">
                    <label>Mức lương cơ bản (VNĐ)</label>
                    <div style="display:flex; gap:10px;">
                        <input type="number" name="salary_from" class="form-control" value="<?= h($filters['salary_from']) ?>" placeholder="Từ">
                        <input type="number" name="salary_to" class="form-control" value="<?= h($filters['salary_to']) ?>" placeholder="Đến">
                    </div>
                </div>
            </div>

            <div style="text-align:right;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tìm kiếm Nâng cao</button>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-header"><h3><i class="fas fa-list"></i> Kết quả tìm kiếm (<?= count($employees) ?>)</h3></div>
    <div class="panel-body p-0">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Mã NV</th>
                        <th>Họ tên</th>
                        <th>Phòng ban</th>
                        <th>Chức vụ</th>
                        <th>CCCD / Email</th>
                        <th>Điện thoại</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $r): ?>
                    <tr>
                        <td><?= $r['emp_code'] ?></td>
                        <td><strong><?= $r['full_name'] ?></strong></td>
                        <td><?= $r['dept_name'] ?></td>
                        <td><?= $r['pos_title'] ?></td>
                        <td><?= $r['id_card_no'] ?><br><small><?= $r['email'] ?></small></td>
                        <td><?= $r['phone'] ?></td>
                        <td><span class="badge badge-default"><?= $r['status'] ?></span></td>
                        <td>
                            <a href="<?= BASE_URL ?>/employee/detail/<?= $r['id'] ?>" class="btn btn-sm btn-ghost" title="Xem 360"><i class="fas fa-eye"></i></a>
                            <a href="<?= BASE_URL ?>/employee/edit/<?= $r['id'] ?>" class="btn btn-sm btn-ghost" title="Sửa"><i class="fas fa-edit"></i></a>
                            <a href="<?= BASE_URL ?>/employee/printProfile/<?= $r['id'] ?>" class="btn btn-sm btn-ghost" target="_blank" title="In hồ sơ"><i class="fas fa-print"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
