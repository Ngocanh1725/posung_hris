<?php /** View: reports/recruitment.php – BC Tuyển dụng */ ?>
<div class="panel mb-4">
    <div class="panel-header"><h3><i class="fas fa-filter"></i> Lọc Báo cáo</h3></div>
    <div class="panel-body">
        <form method="GET">
            <div style="display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
                <div class="form-group"><label class="form-label-sm">Năm</label>
                    <select name="year" class="form-control"><?php for($y=date('Y');$y>=2020;$y--): ?><option value="<?=$y?>" <?=$filters['year']==$y?'selected':''?>><?=$y?></option><?php endfor; ?></select></div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-header"><h3><i class="fas fa-user-plus"></i> Báo cáo Tình hình Tuyển dụng (<?= count($data) ?> YCTD)</h3></div>
    <div class="panel-body p-0">
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Mã YCTD</th><th>Vị trí</th><th>Phòng ban</th><th style="text-align:center;">SL Cần</th><th style="text-align:center;">SL Ứng viên</th><th style="text-align:center;">Đạt PV</th><th style="text-align:center;">Đã tuyển</th><th style="text-align:center;">Tỷ lệ lấp đầy</th><th>Trạng thái</th><th>Hạn tuyển</th></tr></thead>
                <tbody>
                    <?php 
                    $totReq=0; $totCand=0; $totPass=0; $totHire=0;
                    foreach($data as $r): 
                        $totReq += $r['quantity']; $totCand += $r['total_candidates']; $totPass += $r['passed_interview']; $totHire += $r['hired_count'];
                        $fillRate = $r['quantity'] > 0 ? round(($r['hired_count'] / $r['quantity']) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td><strong><?= $r['request_code'] ?></strong></td>
                        <td><?= $r['pos_title'] ?? '-' ?></td>
                        <td><small><?= $r['dept_name'] ?? '-' ?></small></td>
                        <td style="text-align:center; font-weight:bold;"><?= $r['quantity'] ?></td>
                        <td style="text-align:center; color:var(--primary);"><?= $r['total_candidates'] ?></td>
                        <td style="text-align:center; color:var(--warning);"><?= $r['passed_interview'] ?></td>
                        <td style="text-align:center; color:var(--success); font-weight:bold;"><?= $r['hired_count'] ?></td>
                        <td style="text-align:center;">
                            <div style="background:#e2e8f0; height:8px; border-radius:4px; overflow:hidden; width:100%; margin-bottom:4px;">
                                <div style="background:<?= $fillRate>=100?'var(--success)':'var(--primary)' ?>; height:100%; width:<?= min(100, $fillRate) ?>%;"></div>
                            </div>
                            <small><?= $fillRate ?>%</small>
                        </td>
                        <td>
                            <?php if(in_array($r['status'],['Closed','Cancelled'])): ?>
                                <span class="badge badge-resigned"><?= $r['status'] ?></span>
                            <?php else: ?>
                                <span class="badge badge-active"><?= $r['status'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= $r['deadline'] ? date('d/m/Y', strtotime($r['deadline'])) : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php if(!empty($data)): ?>
                <tfoot>
                    <tr style="background:#f1f5f9; font-weight:bold;">
                        <td colspan="3" style="text-align:right;">TỔNG CỘNG:</td>
                        <td style="text-align:center;"><?= $totReq ?></td>
                        <td style="text-align:center; color:var(--primary);"><?= $totCand ?></td>
                        <td style="text-align:center; color:var(--warning);"><?= $totPass ?></td>
                        <td style="text-align:center; color:var(--success);"><?= $totHire ?></td>
                        <td style="text-align:center;"><?= $totReq>0 ? round(($totHire/$totReq)*100,1) : 0 ?>%</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>
<style>.form-label-sm{font-size:0.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;}</style>
