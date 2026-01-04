<?php ob_start(); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i> Detail Reminder</h3>
                <a href="/admin/email/reminders" class="btn btn-default btn-sm float-right">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Semester</th>
                                <td>
                                    <?php echo htmlspecialchars($reminder['semester_nama']); ?> (
                                    <?php echo $reminder['semester_kode']; ?>)
                                </td>
                            </tr>
                            <tr>
                                <th>Subject</th>
                                <td>
                                    <?php echo htmlspecialchars($reminder['subject']); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Kirim</th>
                                <td>
                                    <?php echo $reminder['sent_at'] ? date('d/m/Y H:i:s', strtotime($reminder['sent_at'])) : '-'; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Dikirim Oleh</th>
                                <td>
                                    System
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Total Penerima</th>
                                <td><span class="badge badge-info">
                                        <?php echo $reminder['recipient_count']; ?>
                                    </span></td>
                            </tr>
                            <tr>
                                <th>Terkirim</th>
                                <td><span class="badge badge-success">
                                        <?php echo $reminder['sent_count']; ?>
                                    </span></td>
                            </tr>
                            <tr>
                                <th>Gagal</th>
                                <td><span class="badge badge-danger">
                                        <?php echo $reminder['failed_count']; ?>
                                    </span></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <?php if ($reminder['status'] == 'sent'): ?>
                                        <span class="badge badge-success">Terkirim</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">
                                            <?php echo ucfirst($reminder['status']); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <h5>Body Email:</h5>
                <div class="border p-3 mb-4" style="background: #f9f9f9;">
                    <?php echo $reminder['body']; ?>
                </div>

                <h5>Log Pengiriman:</h5>
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Nomor Peserta</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Error Message</th>
                            <th>Waktu Kirim</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $index => $log): ?>
                            <tr>
                                <td>
                                    <?php echo $index + 1; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($log['nomor_peserta'] ?? '-'); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($log['nama_lengkap'] ?? '-'); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($log['email']); ?>
                                </td>
                                <td>
                                    <?php if ($log['status'] == 'sent'): ?>
                                        <span class="badge badge-success">Terkirim</span>
                                    <?php elseif ($log['status'] == 'failed'): ?>
                                        <span class="badge badge-danger">Gagal</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($log['error_message']): ?>
                                        <small class="text-danger">
                                            <?php echo htmlspecialchars($log['error_message']); ?>
                                        </small>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $log['sent_at'] ? date('d/m/Y H:i:s', strtotime($log['sent_at'])) : '-'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../layouts/admin.php';
?>