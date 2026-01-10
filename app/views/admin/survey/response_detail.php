<?php ob_start(); ?>
<?php $title = 'Detail Respon'; ?>

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-alt mr-1"></i> Detail Jawaban Responden
                </h3>
                <div class="card-tools">
                    <a href="/admin/surveys/respondents/<?php echo $response['survey_id']; ?>" class="btn btn-tool"
                        title="Kembali ke Daftar">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Info Survei & Responden -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <strong>Judul Survei:</strong><br>
                        <?php echo htmlspecialchars($response['survey_title']); ?>
                    </div>
                    <div class="col-md-6 text-right">
                        <strong>Waktu Kirim:</strong><br>
                        <?php echo date('d F Y, H:i', strtotime($response['submitted_at'])); ?><br>
                        <strong>Tipe Responden:</strong>
                        <span
                            class="badge badge-<?php echo ($response['respondent_type'] == 'participant' ? 'primary' : 'secondary'); ?>">
                            <?php echo ucfirst($response['respondent_type']); ?>
                        </span>
                    </div>
                </div>

                <div class="callout callout-info">
                    <h5>Identitas Responden</h5>
                    <p>
                        <?php if ($response['respondent_type'] == 'participant' && !empty($response['nama_lengkap'])): ?>
                            <strong>Nama:</strong>
                            <?php echo htmlspecialchars($response['nama_lengkap']); ?><br>
                            <strong>Nomor Peserta:</strong>
                            <?php echo htmlspecialchars($response['nomor_peserta'] ?? '-'); ?>
                        <?php else: ?>
                            <strong>Identifier:</strong>
                            <?php echo htmlspecialchars($response['respondent_identifier']); ?>
                        <?php endif; ?>
                    </p>
                </div>

                <hr>

                <!-- Jawaban -->
                <h5>Jawaban Kuisioner</h5>
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th width="50">No</th>
                            <th>Pertanyaan</th>
                            <th width="150" class="text-center">Nilai (1-4)</th>
                            <th width="150" class="text-center">Kategori</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        foreach ($answers as $a):
                            // Determine label class for score
                            $scoreClass = 'secondary';
                            if ($a['score'] == 4)
                                $scoreClass = 'success';
                            elseif ($a['score'] == 3)
                                $scoreClass = 'primary';
                            elseif ($a['score'] == 2)
                                $scoreClass = 'warning';
                            elseif ($a['score'] == 1)
                                $scoreClass = 'danger';
                            ?>
                            <tr>
                                <td>
                                    <?php echo $i++; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($a['question_text']); ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-<?php echo $scoreClass; ?>"
                                        style="font-size: 1.1em; width: 30px;">
                                        <?php echo $a['score']; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($a['category']); ?>
                                    </small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($answers)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    <em>Tidak ada data jawaban (mungkin error saat penyimpanan).</em>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Saran -->
                <div class="mt-4">
                    <h5>Saran & Masukan</h5>
                    <div class="p-3 bg-light border rounded">
                        <?php if (!empty($response['suggestion'])): ?>
                            "
                            <?php echo nl2br(htmlspecialchars($response['suggestion'])); ?>"
                        <?php else: ?>
                            <em class="text-muted">- Tidak ada saran -</em>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
            <div class="card-footer">
                <button class="btn btn-default" onclick="window.print()"><i class="fas fa-print"></i> Cetak</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>