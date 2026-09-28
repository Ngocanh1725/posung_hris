<?php
/** View: ai/suggest.php */
?>
<div class="ai-suggest-container">
    <!-- Header Section -->
    <div class="ai-header glass-panel">
        <div class="ai-header-content">
            <div class="ai-icon-pulse">
                <i class="fas fa-robot"></i>
            </div>
            <div class="ai-title-area">
                <h2>AI Candidate Matcher</h2>
                <p>Phân tích & Lọc Hồ sơ Tự động - YCTD: <strong><?= h($request->request_code ?? 'N/A') ?></strong> (<?= h($request->position_title ?? 'Vị trí chưa xác định') ?>)</p>
            </div>
        </div>
        <div class="ai-actions">
            <a href="<?= BASE_URL ?>/recruitment/detail/<?= $id ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <button class="btn btn-primary" onclick="reRunAnalysis()">
                <i class="fas fa-sync-alt"></i> Phân tích lại
            </button>
        </div>
    </div>

    <!-- Stats & Filters -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="ai-stat-card">
                <div class="stat-icon text-primary bg-primary-light">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?= count($candidates) ?></h3>
                    <span>Tổng số Hồ sơ</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="ai-stat-card">
                <div class="stat-icon text-success bg-success-light">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="stat-info">
                    <h3><?= count(array_filter($candidates, fn($c) => $c->ai_score >= 70)) ?></h3>
                    <span>Đạt tiêu chuẩn (>70%)</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="ai-stat-card">
                <div class="stat-icon text-warning bg-warning-light">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="stat-info">
                    <h3>Top <?= min(3, count($candidates)) ?></h3>
                    <span>Gợi ý tốt nhất</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Candidate List -->
    <div class="ai-candidate-list mt-4">
        <?php if (empty($candidates)): ?>
            <div class="empty-state glass-panel">
                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486747.png" alt="No candidates" width="120">
                <h4>Chưa có hồ sơ nào</h4>
                <p>Hệ thống AI không tìm thấy hồ sơ nào cho yêu cầu tuyển dụng này.</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($candidates as $index => $candidate): ?>
                    <div class="col-md-6 mb-4">
                        <div class="candidate-card <?= $index < 3 ? 'top-match' : '' ?>">
                            <?php if ($index < 3): ?>
                                <div class="top-badge">Top <?= $index + 1 ?></div>
                            <?php endif; ?>
                            
                            <div class="card-header-flex">
                                <div class="candidate-avatar">
                                    <?= strtoupper(mb_substr($candidate->full_name, 0, 1)) ?>
                                </div>
                                <div class="candidate-info">
                                    <h4 class="mb-1">
                                        <a href="<?= BASE_URL ?>/recruitment/candidate/<?= $candidate->id ?>" target="_blank"><?= h($candidate->full_name) ?></a>
                                    </h4>
                                    <div class="text-muted small">
                                        <i class="fas fa-envelope"></i> <?= h($candidate->email ?? 'N/A') ?> &nbsp;|&nbsp; 
                                        <i class="fas fa-phone"></i> <?= h($candidate->phone ?? 'N/A') ?>
                                    </div>
                                </div>
                                <div class="ai-score-ring border-<?= $candidate->ai_color ?>">
                                    <span class="text-<?= $candidate->ai_color ?>"><?= $candidate->ai_score ?>%</span>
                                </div>
                            </div>

                            <div class="card-body-flex mt-3">
                                <div class="row">
                                    <div class="col-6">
                                        <p class="mb-1 text-muted small">Bằng cấp</p>
                                        <p class="fw-bold"><?= h($candidate->highest_degree ?? '—') ?></p>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-1 text-muted small">Kinh nghiệm</p>
                                        <p class="fw-bold"><?= h($candidate->experience_years ?? 0) ?> năm</p>
                                    </div>
                                </div>
                                
                                <div class="ai-insights mt-3">
                                    <h6 class="text-purple"><i class="fas fa-magic"></i> AI Nhận Xét:</h6>
                                    <ul class="insight-list">
                                        <?php foreach ($candidate->ai_insights as $insight): ?>
                                            <li><i class="fas fa-check-circle text-success"></i> <?= h($insight) ?></li>
                                        <?php endforeach; ?>
                                        <?php if ($candidate->ai_score < 70): ?>
                                            <li class="text-warning"><i class="fas fa-exclamation-triangle"></i> Cần đánh giá thêm qua phỏng vấn.</li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="card-footer-flex mt-3 pt-3 border-top">
                                <button class="btn btn-sm btn-outline-primary" onclick="scheduleInterview(<?= $candidate->id ?>)">
                                    <i class="fas fa-calendar-plus"></i> Hẹn phỏng vấn
                                </button>
                                <?php if (!empty($candidate->cv_file_path)): ?>
                                    <a href="<?= BASE_URL ?>/public/<?= h($candidate->cv_file_path) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-file-pdf"></i> Xem CV
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark border">Không có CV</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .ai-suggest-container {
        padding: 0 10px;
    }
    .ai-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 24px;
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%);
        border: 1px solid rgba(139, 92, 246, 0.2);
    }
    .ai-header-content {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    .ai-icon-pulse {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.7);
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.7); }
        70% { box-shadow: 0 0 0 15px rgba(139, 92, 246, 0); }
        100% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0); }
    }
    .ai-title-area h2 {
        margin: 0 0 5px 0;
        font-weight: 700;
        background: linear-gradient(to right, #4f46e5, #9333ea);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .ai-title-area p {
        margin: 0;
        color: var(--text-muted);
    }
    
    .ai-stat-card {
        background: var(--bg-card);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
    }
    .ai-stat-card:hover {
        transform: translateY(-3px);
    }
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .bg-primary-light { background: rgba(59, 130, 246, 0.1); }
    .bg-success-light { background: rgba(16, 185, 129, 0.1); }
    .bg-warning-light { background: rgba(245, 158, 11, 0.1); }
    
    .stat-info h3 {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        color: var(--text-main);
    }
    .stat-info span {
        font-size: 13px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .candidate-card {
        background: var(--bg-card);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 16px;
        padding: 24px;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .candidate-card:hover {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
        border-color: rgba(139, 92, 246, 0.3);
    }
    .candidate-card.top-match {
        border: 1px solid rgba(139, 92, 246, 0.5);
        background: linear-gradient(180deg, rgba(139, 92, 246, 0.03) 0%, rgba(255,255,255,0) 100%), var(--bg-card);
    }
    .top-badge {
        position: absolute;
        top: 15px;
        right: -30px;
        background: linear-gradient(90deg, #f59e0b, #ef4444);
        color: white;
        padding: 5px 30px;
        font-size: 12px;
        font-weight: 700;
        transform: rotate(45deg);
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .card-header-flex {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .candidate-avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #2dd4bf);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: 700;
    }
    .candidate-info {
        flex: 1;
    }
    .candidate-info a {
        color: var(--text-main);
        text-decoration: none;
        transition: color 0.2s;
    }
    .candidate-info a:hover {
        color: #8b5cf6;
    }
    
    .ai-score-ring {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 18px;
        border: 4px solid;
        background: rgba(255,255,255,0.05);
    }
    
    .ai-insights {
        background: rgba(139, 92, 246, 0.05);
        border: 1px dashed rgba(139, 92, 246, 0.3);
        border-radius: 8px;
        padding: 12px;
        flex-grow: 1;
    }
    .text-purple { color: #8b5cf6 !important; }
    .insight-list {
        list-style: none;
        padding: 0;
        margin: 0;
        font-size: 13.5px;
    }
    .insight-list li {
        margin-bottom: 6px;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }
    .insight-list li:last-child { margin-bottom: 0; }
    
    .card-footer-flex {
        display: flex;
        gap: 10px;
        margin-top: auto;
    }
</style>

<script>
    function scheduleInterview(candidateId) {
        // Implement schedule interview logic or redirect
        window.location.href = `<?= BASE_URL ?>/recruitment/schedule?candidate_id=${candidateId}`;
    }

    function reRunAnalysis() {
        const btn = document.querySelector('.ai-actions .btn-primary');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        btn.disabled = true;
        
        setTimeout(() => {
            window.location.reload();
        }, 1500); // Fake delay for realism
    }
</script>
