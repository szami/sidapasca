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
                <a href="/admin/news" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <form action="<?= isset($news) ? '/admin/news/update/' . $news['id'] : '/admin/news/store' ?>" method="POST"
                enctype="multipart/form-data">
                <div class="card-body">

                    <div class="form-group">
                        <label>Judul Berita</label>
                        <input type="text" name="title" class="form-control" required
                            value="<?= $news['title'] ?? '' ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kategori</label>
                                <select name="category" class="form-control">
                                    <option value="umum" <?= (isset($news) && $news['category'] == 'umum') ? 'selected' : '' ?>>Umum</option>
                                    <option value="pengumuman" <?= (isset($news) && $news['category'] == 'pengumuman') ? 'selected' : '' ?>>Pengumuman</option>
                                    <option value="informasi" <?= (isset($news) && $news['category'] == 'informasi') ? 'selected' : '' ?>>Informasi</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipe Konten</label>
                                <div class="mt-2">
                                    <div class="icheck-primary d-inline mr-3">
                                        <input type="radio" id="type1" name="content_type" value="text_image"
                                            <?= (!isset($news) || $news['content_type'] == 'text_image') ? 'checked' : '' ?>>
                                        <label for="type1">Artikel (Teks + Gambar)</label>
                                    </div>
                                    <div class="icheck-primary d-inline">
                                        <input type="radio" id="type2" name="content_type" value="image_only"
                                            <?= (isset($news) && $news['content_type'] == 'image_only') ? 'checked' : '' ?>>
                                        <label for="type2">Poster / Gambar Saja</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label id="image-label">Gambar Utama</label>
                        <?php if (isset($news) && $news['image_url']): ?>
                            <div class="mb-2">
                                <img src="<?= $news['image_url'] ?>" class="img-thumbnail" style="max-height: 200px">
                            </div>
                        <?php endif; ?>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="image" id="image-upload"
                                    accept="image/*">
                                <label class="custom-file-label">Pilih file</label>
                            </div>
                        </div>
                        <small class="text-muted">Format: JPG/PNG/JPEG. Max 2MB.</small>
                    </div>

                    <div class="form-group" id="content-group">
                        <label>Konten Berita</label>
                        <textarea name="content" id="summernote"><?= $news['content'] ?? '' ?></textarea>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <?php if (isset($news) && $news['is_published']): ?>
                                <input type="hidden" name="was_published" value="1">
                            <?php endif; ?>
                            <input type="checkbox" class="custom-control-input" id="is_published" name="is_published"
                                value="1" <?= (isset($news) && $news['is_published']) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="is_published">Publish Berita Ini</label>
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
<script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>

<script>
    $(document).ready(function () {
        bsCustomFileInput.init();

        // Summernote
        $('#summernote').summernote({
            height: 300,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
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
                url: "/admin/news/upload-image",
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

        // Handle Content Type Toggle
        function toggleContentType() {
            if ($('#type2').is(':checked')) {
                // Image Only
                $('#content-group').hide();
                $('#image-upload').attr('required', true); // Require image for poster mode
                $('#image-label').text('Upload Poster / Gambar *');
            } else {
                // Text + Image
                $('#content-group').show();
                $('#image-upload').attr('required', false); // Optional for article
                $('#image-label').text('Gambar Sampul (Opsional)');
            }
        }

        $('input[name="content_type"]').change(toggleContentType);
        toggleContentType(); // Init
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>