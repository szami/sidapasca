<?php ob_start(); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-paper-plane mr-2"></i> Kirim Email Reminder</h3>
                <div class="card-tools">
                    <span class="badge badge-info">Semester: <?php echo $activeSemester['nama'] ?? 'Tidak Ada'; ?></span>
                </div>
            </div>
            
            <form action="/admin/email/reminders/send" method="POST">
                <div class="card-body">
                    <?php if (isset($_GET['success']) && $_GET['success'] == 'test'): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="icon fas fa-check"></i> Testing berhasil! Email terkirim ke <?php echo $_GET['count'] ?? 0; ?> alamat.
                    </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="icon fas fa-ban"></i> <?php echo urldecode($_GET['error']); ?>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Template Email *</label>
                                <select name="template_id" id="templateSelect" class="form-control" required>
                                    <option value="">-- Pilih Template --</option>
                                    <?php foreach ($templates as $template): ?>
                                    <option value="<?php echo $template['id']; ?>"><?php echo htmlspecialchars($template['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Mode Pengiriman *</label>
                                <select name="mode" id="modeSelect" class="form-control" required>
                                    <option value="actual">Actual (Kirim ke Peserta Tercentang)</option>
                                    <option value="testing">Testing (Kirim ke Email Testing)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="testEmailGroup" style="display:none;">
                        <div class="form-group">
                            <label>Email Testing (Pisahkan dengan koma)</label>
                            <input type="text" name="test_emails" class="form-control" 
                                   placeholder="email1@example.com, email2@example.com">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Subject Email *</label>
                        <input type="text" name="subject" id="emailSubject" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Body Email *</label>
                        <textarea name="body" id="emailBody" class="summernote"></textarea>
                    </div>

                    <hr>

                    <div id="participantSection">
                        <h5>Pilih Penerima</h5>
                        
                        <div class="btn-group mb-3" role="group">
                            <button type="button" class="btn btn-outline-primary preset-btn" data-preset="all">
                                <i class="fas fa-users"></i> Semua Peserta
                            </button>
                            <button type="button" class="btn btn-outline-warning preset-btn" data-preset="unpaid">
                                <i class="fas fa-money-bill-wave"></i> Lulus tapi Belum Bayar
                            </button>
                            <button type="button" class="btn btn-outline-secondary preset-btn" data-preset="custom">
                                <i class="fas fa-hand-pointer"></i> Custom
                            </button>
                        </div>

                        <div class="mb-3">
                            <button type="button" id="selectAllBtn" class="btn btn-sm btn-success">
                                <i class="fas fa-check-square"></i> Select All
                            </button>
                            <button type="button" id="deselectAllBtn" class="btn btn-sm btn-secondary">
                                <i class="fas fa-square"></i> Deselect All
                            </button>
                            <span class="ml-3"><strong>Terpilih: <span id="selectedCount">0</span></strong></span>
                        </div>

                        <table class="table table-bordered datatable" id="participantTable">
                            <thead>
                                <tr>
                                    <th width="5%">
                                        <input type="checkbox" id="checkAll">
                                    </th>
                                    <th>Nomor Peserta</th>
                                    <th>Nama</th>
                                    <th>Prodi</th>
                                    <th>Email</th>
                                    <th>Status Berkas</th>
                                    <th>Status Bayar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($participants as $p): ?>
                                <tr data-status-berkas="<?php echo $p['status_berkas']; ?>" 
                                    data-status-bayar="<?php echo $p['status_pembayaran'] ?? 'belum'; ?>"
                                    data-nomor-peserta="<?php echo $p['nomor_peserta'] ?? ''; ?>">
                                    <td>
                                        <input type="checkbox" name="participant_ids[]" 
                                               value="<?php echo $p['id']; ?>" class="participant-checkbox">
                                    </td>
                                    <td><?php echo htmlspecialchars($p['nomor_peserta'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($p['nama_lengkap'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($p['nama_prodi'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($p['email'] ?? '-'); ?></td>
                                    <td>
                                        <?php if ($p['status_berkas'] == 'lulus'): ?>
                                            <span class="badge badge-success">Lulus</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><?php echo ucfirst($p['status_berkas']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $statusBayar = $p['status_pembayaran'] ?? 'belum';
                                        if ($statusBayar == 'lunas'): ?>
                                            <span class="badge badge-success">Lunas</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Belum</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane mr-1"></i> Kirim Email
                    </button>
                    <a href="${APP_URL}/admin/email/reminders" class="btn btn-default float-right">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Summernote
    $('.summernote').summernote({
        height: 250,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link']],
            ['view', ['codeview', 'help']]
        ]
    });

    // Mode toggle
    $('#modeSelect').change(function() {
        if ($(this).val() === 'testing') {
            $('#testEmailGroup').show();
            $('#participantSection').hide();
        } else {
            $('#testEmailGroup').hide();
            $('#participantSection').show();
        }
    });

    // Template selection
    $('#templateSelect').change(function() {
        var id = $(this).val();
        if (id) {
            $.get(APP_URL + '/admin/email/templates/get/' + id, function(data) {
                $('#emailSubject').val(data.subject);
                $('#emailBody').summernote('code', data.body);
            });
        }
    });

    // Check all
    $('#checkAll').change(function() {
        $('.participant-checkbox:visible').prop('checked', this.checked);
        updateCount();
    });

    // Select/Deselect All
    $('#selectAllBtn').click(function() {
        $('.participant-checkbox:visible').prop('checked', true);
        updateCount();
    });

    $('#deselectAllBtn').click(function() {
        $('.participant-checkbox:visible').prop('checked', false);
        updateCount();
    });

    // Update count
    $('.participant-checkbox').change(updateCount);

    function updateCount() {
        var count = $('.participant-checkbox:checked').length;
        $('#selectedCount').text(count);
    }

    // Preset filters
    $('.preset-btn').click(function() {
        $('.preset-btn').removeClass('active');
        $(this).addClass('active');
        
        var preset = $(this).data('preset');
        var dt = $('#participantTable').DataTable();
        
        if (preset === 'all') {
            dt.search('').draw();
            $('.participant-checkbox:visible').prop('checked', true);
        } else if (preset === 'unpaid') {
            // Show only lulus but not lunas AND no nomor_peserta
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var row = $('#participantTable tbody tr').eq(dataIndex);
                var statusBerkas = row.data('status-berkas');
                var statusBayar = row.data('status-bayar');
                var nomorPeserta = row.data('nomor-peserta');
                
                // Exclude if already has nomor peserta (assumed paid)
                if (nomorPeserta && nomorPeserta.toString().trim() !== '') return false;
                
                return statusBerkas === 'lulus' && statusBayar !== 'lunas';
            });
            dt.draw();
            $.fn.dataTable.ext.search.pop();
            $('.participant-checkbox:visible').prop('checked', true);
        } else {
            dt.search('').draw();
            $('.participant-checkbox').prop('checked', false);
        }
        
        updateCount();
    });
});
</script>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../../../layouts/admin.php'; 
?>
