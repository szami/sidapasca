<?php
use Illuminate\Support\Str;
ob_start();
?>
<?php $title = 'Laporan SKM'; ?>

<div class="row">
    <!-- Header Info -->
    <div class="col-12 mb-3">
        <h4>Laporan:
            <?php echo htmlspecialchars($survey['title']); ?>
        </h4>
        <p class="text-muted">
            <?php echo htmlspecialchars($survey['description']); ?>
        </p>
    </div>

    <!-- Stats Box -->
    <div class="col-md-4">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Nilai IKM</span>
                <span class="info-box-number" style="font-size: 2em;">
                    <?php echo $ikm; ?>
                </span>
                <div class="progress">
                    <div class="progress-bar" style="width: <?php echo $ikm; ?>%"></div>
                </div>
                <span class="progress-description">
                    Mutu: <strong>
                        <?php echo $mutu; ?>
                    </strong> (
                    <?php echo $kinerja; ?>)
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Responden</span>
                <span class="info-box-number" style="font-size: 2em;">
                    <?php echo $totalResponses; ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Link Info -->
    <div class="col-md-4">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-link"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Link Survei</span>
                <span class="info-box-number" style="font-size: 0.9em;">
                    <a href="/survey/<?php echo $survey['id']; ?>" target="_blank" class="text-white"><u>Buka Form
                            Survei</u></a>
                </span>
            </div>
        </div>
    </div>

    <!-- Detailed Table -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title">Analisis Per Unsur / Pertanyaan</h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-valign-middle">
                    <thead>
                        <tr>
                            <th>Unsur Pelayanan</th>
                            <th class="text-center">Kategori</th>
                            <th class="text-center">Nilai Rata-rata</th>
                            <th class="text-center">NRR Tartimbang</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1;
                        foreach ($stats as $s): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-light">
                                        <?php echo $s['question']['code'] ?? $i++; ?>
                                    </span>
                                    <?php echo htmlspecialchars(strlen($s['question']['question_text']) > 60 ? substr($s['question']['question_text'], 0, 60) . '...' : $s['question']['question_text']); ?>
                                </td>
                                <td class="text-center">
                                    <small class="badge badge-secondary">
                                        <?php echo $s['question']['category'] ?? '-'; ?>
                                    </small>
                                </td>
                                <td class="text-center font-weight-bold text-primary">
                                    <?php echo $s['avg_score']; ?>
                                </td>
                                <td class="text-center">
                                    <?php echo round($s['nrr'], 3); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Suggestions Panel -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title">Saran & Masukan Terbaru</h3>
            </div>
            <div class="card-body p-0">
                <div style="max-height: 400px; overflow-y: auto;">
                    <ul class="products-list product-list-in-card pl-2 pr-2">
                        <?php foreach ($suggestions as $sug): ?>
                            <li class="item">
                                <div class="product-info ml-2">
                                    <span class="product-description" style="white-space: normal;">
                                        "
                                        <?php echo htmlspecialchars($sug['suggestion']); ?>"
                                    </span>
                                    <small class="badge badge-light float-right"><i class="far fa-clock"></i>
                                        <?php echo $sug['submitted_at']; ?>
                                    </small>
                                    <span class="product-title text-muted" style="font-size: 0.8em;">
                                        by
                                        <?php echo htmlspecialchars($sug['respondent_identifier']); ?>
                                    </span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                        <?php if (empty($suggestions)): ?>
                            <li class="item text-center p-3 text-muted">Belum ada saran.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>