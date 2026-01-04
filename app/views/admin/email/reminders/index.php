<?php ob_start(); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-history mr-2"></i> Riwayat Reminder Email</h3>
                <a href="/admin/email/reminders/send" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Buat Reminder
                </a>
            </div>

            <div class="card-body">
                <?php if (isset($_GET['success']) && $_GET['success'] == 'sent'): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="icon fas fa-check"></i>
                        Email berhasil dikirim!
                        Terkirim:
                        <?php echo $_GET['sent'] ?? 0; ?>,
                        Gagal:
                        <?php echo $_GET['failed'] ?? 0; ?>
                    </div>
                <?php endif; ?>

                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Tanggal</th>
                            <th>Semester</th>
                            <th>Subject</th>
                            <th>Penerima</th>
                            <th>Terkirim</th>
                            <th>Gagal</th>
                            <th>Status</th>
                            <th>Dikirim Oleh</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reminders as $index => $reminder): ?>
                            <tr>
                                <td>
                                    <?php echo $index + 1; ?>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y H:i', strtotime($reminder['created_at'])); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($reminder['semester_nama']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($reminder['subject']); ?>
                                </td>
                                <td>
                                    <?php echo $reminder['recipient_count']; ?>
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <?php echo $reminder['sent_count']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-danger">
                                        <?php echo $reminder['failed_count']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($reminder['status'] == 'sent'): ?>
                                        <span class="badge badge-success">Terkirim</span>
                                    <?php elseif ($reminder['status'] == 'sending'): ?>
                                        <span class="badge badge-warning">Mengirim...</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">
                                            <?php echo ucfirst($reminder['status']); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    System
                                </td>
                                <td>
                                    <a href="/admin/email/reminders/<?php echo $reminder['id']; ?>"
                                        class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
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