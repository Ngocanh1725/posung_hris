<?php require APP_ROOT . '/views/layout/header.php'; ?>

<div class="panel">
    <div class="panel-header" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h3 style="margin: 0;"><i class="fas fa-shield-alt"></i> Phân quyền: <?= h($targetUser['full_name']) ?> (<?= h($targetUser['username']) ?>)</h3>
            <span class="badge" style="margin-top: 8px; display: inline-block;">
                Vai trò: <?= h($targetUserRoleInfo['role_name'] ?? 'Không rõ') ?>
            </span>
        </div>
        <a href="<?= BASE_URL ?>/user" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại DS</a>
    </div>
    
    <form method="POST" action="<?= BASE_URL ?>
    <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">/permission/saveMatrix">
        <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">
        <input type="hidden" name="user_id" value="<?= $targetUser['id'] ?>">

        <div class="panel-body">
            <!-- Tùy chọn dành cho Super Admin -->
            <?php if ($isSuperAdmin): ?>
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); padding: 15px; border-radius: 8px; margin-bottom: 25px;">
                    <label style="display:flex; align-items:center; cursor:pointer;">
                        <input type="checkbox" name="can_delegate" value="1" 
                               <?= (!empty($targetUserRoleInfo['can_delegate'])) ? 'checked' : '' ?> 
                               style="margin-right: 12px; width: 18px; height: 18px;">
                        <strong style="color: #34d399;">Cấp cờ Ủy quyền (Cho phép tài khoản này tiếp tục phân quyền cho cấp dưới)</strong>
                    </label>
                </div>
            <?php endif; ?>

            <!-- Bảng Ma trận -->
            <?php foreach ($allPermissionsGrouped as $moduleGroup): ?>
                <div class="module-group" style="margin-bottom: 25px; background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    
                    <!-- Header Phân hệ -->
                    <div class="module-header" style="background: rgba(0, 0, 0, 0.2); padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                        <h4 style="margin: 0; font-size: 1.1rem; color: var(--primary-light);">
                            <i class="<?= h($moduleGroup['icon'] ?? 'fas fa-cube') ?>" style="width: 24px;"></i> 
                            <?= h($moduleGroup['module_name']) ?>
                        </h4>
                        <button type="button" class="btn btn-sm btn-secondary select-all-btn" data-module="<?= $moduleGroup['module_id'] ?>">
                            <i class="fas fa-check-double"></i> Chọn tất cả
                        </button>
                    </div>
                    
                    <!-- Grid Quyền hạn -->
                    <div class="module-permissions" style="padding: 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 15px;">
                        <?php foreach ($moduleGroup['permissions'] as $perm): ?>
                            <?php 
                                $isChecked = in_array($perm['id'], $targetPermIds);
                                // Vô hiệu hóa checkbox nếu là Sub Admin và không sở hữu quyền này
                                $isDisabled = (!$isSuperAdmin && !in_array($perm['id'], $currentUserPermIds));
                            ?>
                            <label class="permission-checkbox" style="display: flex; align-items: flex-start; padding: 10px; border-radius: 8px; transition: background 0.2s; cursor: <?= $isDisabled ? 'not-allowed' : 'pointer' ?>; opacity: <?= $isDisabled ? '0.4' : '1' ?>;">
                                <input type="checkbox" 
                                       name="permissions[]" 
                                       value="<?= $perm['id'] ?>" 
                                       class="perm-cb-<?= $moduleGroup['module_id'] ?>"
                                       <?= $isChecked ? 'checked' : '' ?>
                                       <?= $isDisabled ? 'disabled' : '' ?>
                                       style="margin-top: 4px; margin-right: 12px; width: 18px; height: 18px; flex-shrink: 0;">
                                <div>
                                    <strong style="display: block; font-size: 0.95rem; color: <?= $isDisabled ? 'var(--text-muted)' : 'var(--text-primary)' ?>; margin-bottom: 4px;">
                                        <?= h($perm['name']) ?>
                                    </strong>
                                    <span style="font-size: 0.8rem; color: var(--text-secondary); line-height: 1.4; display: block;">
                                        <?= h($perm['description']) ?>
                                    </span>
                                    <?php if ($isDisabled): ?>
                                        <span style="font-size: 0.75rem; color: #ef4444; margin-top: 4px; display: inline-block;">
                                            <i class="fas fa-lock"></i> Bạn không sở hữu quyền này
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Floating Save Bar -->
        <div class="floating-save-bar" style="position: sticky; bottom: 20px; background: var(--bg-card); padding: 15px 24px; border-radius: 12px; box-shadow: 0 -4px 30px rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.1); display: flex; justify-content: space-between; align-items: center; z-index: 100; margin: 30px 24px 10px;">
            <div style="color: var(--text-secondary); font-size: 0.95rem;">
                <i class="fas fa-info-circle" style="color: var(--primary-light);"></i> Các quyền bị khóa (<i class="fas fa-lock" style="font-size:10px;"></i>) là do bạn không có quyền cấp phát chức năng đó.
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 12px 32px; font-size: 1.05rem;">
                <i class="fas fa-save"></i> Lưu phân quyền
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllBtns = document.querySelectorAll('.select-all-btn');
    
    // Hover effect cho permission label
    document.querySelectorAll('.permission-checkbox:not([style*="not-allowed"])').forEach(label => {
        label.addEventListener('mouseenter', () => label.style.background = 'rgba(255,255,255,0.05)');
        label.addEventListener('mouseleave', () => label.style.background = 'transparent');
    });

    // Xử lý nút Select All
    selectAllBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const moduleId = this.getAttribute('data-module');
            const checkboxes = document.querySelectorAll('.perm-cb-' + moduleId + ':not([disabled])');
            
            if (checkboxes.length === 0) return;

            let allChecked = true;
            checkboxes.forEach(cb => {
                if (!cb.checked) allChecked = false;
            });
            
            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
            });
            
            if (allChecked) {
                this.innerHTML = '<i class="fas fa-check-double"></i> Chọn tất cả';
            } else {
                this.innerHTML = '<i class="fas fa-times"></i> Bỏ chọn';
            }
        });
    });
});
</script>

<?php require APP_ROOT . '/views/layout/footer.php'; ?>
