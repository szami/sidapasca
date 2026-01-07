<?php
ob_start();
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $title ?></h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="/admin/guides" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <form action="<?= isset($guide) ? '/admin/guides/update/' . $guide['id'] : '/admin/guides/store' ?>"
                method="POST">
                <div class="card-body">

                    <div class="form-group">
                        <label>Judul Panduan</label>
                        <input type="text" name="title" class="form-control" required
                            value="<?= $guide['title'] ?? '' ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Target Role</label>
                                <select name="role" class="form-control">
                                    <option value="participant" <?= (isset($guide) && $guide['role'] == 'participant') ? 'selected' : '' ?>>Participant (Peserta)</option>
                                    <option value="admin_prodi" <?= (isset($guide) && $guide['role'] == 'admin_prodi') ? 'selected' : '' ?>>Admin Prodi</option>
                                    <option value="admin" <?= (isset($guide) && $guide['role'] == 'admin') ? 'selected' : '' ?>>Admin Operator</option>
                                    <option value="superadmin" <?= (isset($guide) && $guide['role'] == 'superadmin') ? 'selected' : '' ?>>Superadmin</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Urutan Tampilan</label>
                                <input type="number" name="order_index" class="form-control"
                                    value="<?= $guide['order_index'] ?? 0 ?>">
                                <small class="text-muted">Semakin kecil angkanya, semakin di atas posisinya.</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Konten Panduan</label>
                        <textarea name="content" id="summernote"><?= $guide['content'] ?? '' ?></textarea>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                value="1" <?= (!isset($guide) || $guide['is_active']) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="is_active">Aktifkan Panduan Ini</label>
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Summernote -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.js"></script>

<script>
    $(document).ready(function () {
        // Summernote
        $('#summernote').summernote({
            height: 400,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            callbacks: {
                onImageUpload: function (files) {
                    uploadImage(files[0]);
                }
            }
        });

        function uploadImage(file) {
            var data = new FormData();
            data.append("file", file);
            $.ajax({
                data: data,
                type: "POST",
                url: "/admin/guides/upload-image", // Uses same upload handler or separate one? Handled in GuideController
                cache: false,
                contentType: false,
                processData: false,
                success: function (url) {
                    $('#summernote').summernote('insertImage', url);
                },
                error: function (data) {
                    alert("Upload Failed");
                }
            });
        }
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>