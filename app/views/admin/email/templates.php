<?php ob_start(); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-invoice mr-2"></i> Template Email</h3>
                <button type="button" class="btn btn-primary float-right" data-toggle="modal"
                    data-target="#templateModal">
                    <i class="fas fa-plus mr-1"></i> Tambah Template
                </button>
            </div>

            <div class="card-body">
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="icon fas fa-check"></i> Operasi berhasil!
                    </div>
                <?php endif; ?>

                <table class="table table-bordered" id="templateTable">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th>Nama Template</th>
                            <th>Subject</th>
                            <th>Deskripsi</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- AJAX Populated -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Template Modal -->
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Template Email</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="templateForm" method="POST">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Placeholder:</strong> {nama}, {nomor_peserta}, {prodi}, {semester}, {email},
                        {no_billing}
                    </div>

                    <input type="hidden" id="templateId" name="id">

                    <div class="form-group">
                        <label>Nama Template *</label>
                        <input type="text" name="name" id="templateName" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Subject *</label>
                        <input type="text" name="subject" id="templateSubject" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Body *</label>
                        <textarea name="body" id="templateBody" class="summernote"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="description" id="templateDescription" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>

<script>
    $(document).ready(function () {
        // Initialize DataTable
        const table = $('#templateTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": APP_URL + "/api/email/templates",
            "order": [[0, "desc"]],
            "columns": [
                { "data": "id" },
                { "data": "name" },
                { "data": "subject" },
                { "data": "description" },
                {
                    "data": "id",
                    "orderable": false,
                    "render": function (data) {
                        return `
                            <button class="btn btn-sm btn-info edit-btn" data-id="${data}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form action="${APP_URL}/admin/email/templates/delete/${data}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus template ini?')">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        `;
                    }
                }
            ]
        });

        // Initialize Summernote
        $('.summernote').summernote({
            height: 200,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link']],
                ['view', ['codeview']]
            ]
        });

        // Reset modal on close
        $('#templateModal').on('hidden.bs.modal', function () {
            $('#templateForm')[0].reset();
            $('#templateId').val('');
            $('#templateBody').summernote('code', '');
            $('#templateForm').attr('action', APP_URL + '/admin/email/templates/create');
        });

        // Edit button (Event Delegation)
        $('#templateTable').on('click', '.edit-btn', function () {
            var id = $(this).data('id');

            $.get(APP_URL + '/admin/email/templates/get/' + id, function (data) {
                $('#templateId').val(data.id);
                $('#templateName').val(data.name);
                $('#templateSubject').val(data.subject);
                $('#templateBody').summernote('code', data.body);
                $('#templateDescription').val(data.description);
                $('#templateForm').attr('action', APP_URL + '/admin/email/templates/update/' + id);
                $('#templateModal').modal('show');
            });
        });
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>