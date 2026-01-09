<?php
ob_start();
$title = 'Edit Survei';
?>

<div class="row">
    <!-- Edit Survey Info -->
    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Informasi Survei</h3>
            </div>
            <form action="/admin/surveys/update/<?php echo $survey['id']; ?>" method="POST">
                <div class="card-body">
                    <div class="form-group">
                        <label>Judul Survei</label>
                        <input type="text" class="form-control" name="title"
                            value="<?php echo htmlspecialchars($survey['title']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea class="form-control" name="description"
                            rows="3"><?php echo htmlspecialchars($survey['description']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Target Responden</label>
                        <select class="form-control" name="target_role">
                            <option value="participant" <?php echo $survey['target_role'] == 'participant' ? 'selected' : ''; ?>>Peserta (Eksternal)</option>
                            <option value="committee_general" <?php echo $survey['target_role'] == 'committee_general' ? 'selected' : ''; ?>>Panitia Teknis</option>
                            <option value="committee_internal" <?php echo $survey['target_role'] == 'committee_internal' ? 'selected' : ''; ?>>Internal Pascasarjana</option>
                            <option value="committee_field" <?php echo $survey['target_role'] == 'committee_field' ? 'selected' : ''; ?>>Panitia Lapangan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="isActiveSwitch" name="is_active"
                                <?php echo $survey['is_active'] ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="isActiveSwitch">Aktifkan Survei</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block">Simpan Perubahan</button>
                    <a href="/admin/surveys" class="btn btn-default btn-block">Kembali</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Manage Questions -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Daftar Pertanyaan</h3>
                <button class="btn btn-sm btn-success ml-auto" data-toggle="modal" data-target="#addQuestionModal">
                    <i class="fas fa-plus"></i> Tambah Pertanyaan
                </button>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Kode</th>
                            <th>Pertanyaan</th>
                            <th>Kategori (Unsur)</th>
                            <th style="width: 100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questions as $q): ?>
                            <tr>
                                <td>
                                    <?php echo $q['order_num']; ?>
                                </td>
                                <td><span class="badge badge-light">
                                        <?php echo $q['code'] ?? '-'; ?>
                                    </span></td>
                                <td>
                                    <?php echo htmlspecialchars($q['question_text']); ?>
                                </td>
                                <td><small class="badge badge-info">
                                        <?php echo $q['category']; ?>
                                    </small></td>
                                <td>
                                    <button class="btn btn-xs btn-warning btn-edit-q" data-id="<?php echo $q['id']; ?>"
                                        data-text="<?php echo htmlspecialchars($q['question_text']); ?>"
                                        data-category="<?php echo htmlspecialchars($q['category']); ?>"
                                        data-code="<?php echo htmlspecialchars($q['code'] ?? ''); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="/admin/surveys/question/delete/<?php echo $q['id']; ?>"
                                        class="btn btn-xs btn-danger" onclick="return confirm('Hapus pertanyaan ini?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($questions)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada pertanyaan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Question Modal -->
<div class="modal fade" id="addQuestionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Pertanyaan Baru</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="/admin/surveys/question/add/<?php echo $survey['id']; ?>" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Kode (Opsional, misal U1)</label>
                        <input type="text" class="form-control" name="code" placeholder="U1">
                    </div>
                    <div class="form-group">
                        <label>Kategori / Unsur Pelayanan</label>
                        <input type="text" class="form-control" name="category"
                            placeholder="Contoh: Persyaratan, Prosedur, Waktu..." list="catList" required>
                        <datalist id="catList">
                            <option value="Persyaratan">
                            <option value="Prosedur">
                            <option value="Waktu Penyelesaian">
                            <option value="Biaya/Tarif">
                            <option value="Produk Layanan">
                            <option value="Kompetensi Pelaksana">
                            <option value="Perilaku Pelaksana">
                            <option value="Penanganan Pengaduan">
                            <option value="Sarana dan Prasarana">
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label>Isi Pertanyaan</label>
                        <textarea class="form-control" name="question_text" rows="3" required></textarea>
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

<!-- Edit Question Modal -->
<div class="modal fade" id="editQuestionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Pertanyaan</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="editQForm" action="" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Kode</label>
                        <input type="text" class="form-control" name="code" id="edit_code">
                    </div>
                    <div class="form-group">
                        <label>Kategori / Unsur</label>
                        <input type="text" class="form-control" name="category" id="edit_category" list="catList"
                            required>
                    </div>
                    <div class="form-group">
                        <label>Isi Pertanyaan</label>
                        <textarea class="form-control" name="question_text" id="edit_text" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(function () {
        $('.btn-edit-q').click(function () {
            var id = $(this).data('id');
            var text = $(this).data('text');
            var cat = $(this).data('category');
            var code = $(this).data('code');

            $('#edit_code').val(code);
            $('#edit_category').val(cat);
            $('#edit_text').val(text);

            $('#editQForm').attr('action', '/admin/surveys/question/update/' + id);
            $('#editQuestionModal').modal('show');
        });
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>