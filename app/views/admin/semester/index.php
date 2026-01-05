<?php ob_start(); ?>
<div class="row">
    <div class="col-md-4">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">Tambah Semester</h3>
            </div>
            <form action="/admin/semesters/store" method="POST">
                <div class="card-body">
                    <div class="form-group">
                        <label>Kode Semester</label>
                        <input type="text" name="kode" class="form-control" placeholder="Contoh: 20251" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Semester</label>
                        <input type="text" name="nama" class="form-control"
                            placeholder="Contoh: Semester Ganjil 2025/2026" required>
                    </div>
                    <div class="form-group">
                        <label>Periode</label>
                        <select name="periode" class="form-control">
                            <option value="0">Tanpa Periode (0)</option>
                            <option value="1">Periode 1</option>
                            <option value="2">Periode 2</option>
                        </select>
                        <small class="text-muted">Ganjil: 1 atau 2. Genap: 0.</small>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Semester</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm" id="semestersTable">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Periode</th>
                            <th>Status</th>
                            <th>Aksi</th>
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

<script>
    $(document).ready(function () {
        $('#semestersTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": APP_URL + "/api/master/semesters",
            "columns": [
                {
                    "data": "kode",
                    "render": function (data) { return `<strong>${data}</strong>`; }
                },
                { "data": "nama" },
                {
                    "data": "periode",
                    "render": function (data) {
                        return data > 0 ? `<span class="badge badge-info">Periode ${data}</span>` : `<span class="text-muted">Tanpa Periode</span>`;
                    }
                },
                {
                    "data": "is_active",
                    "render": function (data) {
                        return data == 1 ? `<span class="badge badge-success">Aktif</span>` : `<span class="badge badge-secondary">Non-Aktif</span>`;
                    }
                },
                {
                    "data": "id",
                    "orderable": false,
                    "render": function (data, type, row) {
                        let btnGroup = '<div class="btn-group">';
                        if (row.is_active == 0) {
                            btnGroup += `<a href="${APP_URL}/admin/semesters/set-active/${data}" class="btn btn-default btn-xs">Set Aktif</a>`;
                        }
                        if (row.is_active == 0 && row.participants_count == 0) {
                            btnGroup += `<a href="${APP_URL}/admin/semesters/delete/${data}" class="btn btn-danger btn-xs" onclick="return confirm('Hapus semester?')">Del</a>`;
                        } else if (row.participants_count > 0) {
                            btnGroup += `<span class="badge badge-light border border-secondary text-xs ml-1" title="Ada data peserta">
                                            <i class="fas fa-database mr-1"></i>${row.participants_count}
                                         </span>`;
                        }
                        btnGroup += '</div>';
                        return btnGroup;
                    }
                }
            ],
            "order": [[0, "desc"]]
        });
    });
</script>
</div>
</div>
</div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>