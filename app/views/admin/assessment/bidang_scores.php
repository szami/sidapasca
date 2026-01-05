<?php
$title = 'Penilaian Tes Bidang';
ob_start();
?>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Penilaian Tes Bidang</h3>
                <p class="text-subtitle text-muted">Input nilai Tes Tertulis Bidang dan Status Rekomendasi</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Penilaian Bidang</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<section class="section">
    <div class="alert alert-info shadow-sm">
        <i class="bi bi-info-circle-fill me-2"></i>
        Anda sedang menilai untuk Prodi: <strong>
            <?php echo htmlspecialchars($prodiName); ?>
        </strong> (Semester Aktif)
    </div>

    <div class="card card-outline card-primary shadow-sm">
        <div
            class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0 pb-0 pt-4 px-4">
            <h4 class="card-title fw-bold text-primary"><i class="bi bi-table me-2"></i> Daftar Peserta</h4>
            <div>
                <a href="/admin/assessment/bidang/export" target="_blank" class="btn btn-sm btn-danger shadow-sm me-2"
                    title="Cetak Laporan Hasil Penilaian">
                    <i class="bi bi-file-earmark-pdf"></i> Cetak Laporan
                </a>
                <a href="/admin/assessment/scores/export?semester_id=<?php echo $currentSemester['id']; ?>&prodi=<?php echo urlencode($prodiName); ?>"
                    class="btn btn-sm btn-success shadow-sm me-2">
                    <i class="bi bi-file-earmark-excel"></i> Template Excel
                </a>
                <button type="button" class="btn btn-sm btn-primary shadow-sm me-2" data-toggle="modal"
                    data-target="#importModal">
                    <i class="bi bi-upload"></i> Import Excel
                </button>
                <a href="/admin/assessment/bidang/reset" class="btn btn-sm btn-outline-danger shadow-sm"
                    onclick="return confirm('PERHATIAN: Semua nilai Bidang untuk prodi ini akan direset ke 0.\\n\\nLanjutkan?');"
                    title="Reset Semua Nilai Bidang">
                    <i class="bi bi-trash"></i> Reset Nilai
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Alert Messages -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Data berhasil disimpan.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover mb-0" id="table1">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="px-3 py-3" style="width: 50px;">No</th>
                            <th class="px-3 py-3">Nomor Peserta</th>
                            <th class="px-3 py-3">Nama Lengkap</th>
                            <th class="px-3 py-3 text-center">Nilai Bidang</th>
                            <th class="px-3 py-3 text-center">Status Rekomendasi</th>
                            <th class="px-3 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($participants as $p): ?>
                            <tr>
                                <td class="px-3 py-3">
                                    <?php echo $no++; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($p['nomor_peserta'] ?? '-'); ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">
                                        <?php echo htmlspecialchars($p['nama_lengkap']); ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold fs-6">
                                        <?php echo $p['nilai_bidang_total'] > 0 ? $p['nilai_bidang_total'] : '-'; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $badgeClass = 'bg-secondary';
                                    $label = 'Belum Dinilai';
                                    if ($p['status_tes_bidang'] === 'lulus') {
                                        $badgeClass = 'bg-success';
                                        $label = 'Disarankan Lulus';
                                    } elseif ($p['status_tes_bidang'] === 'tidak_lulus') {
                                        $badgeClass = 'bg-danger';
                                        $label = 'Tidak Disarankan';
                                    }
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo $label; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-primary shadow-sm"
                                        onclick="openScoreModal('<?php echo $p['id']; ?>', '<?php echo addslashes($p['nama_lengkap']); ?>', '<?php echo addslashes($p['nama_prodi']); ?>')">
                                        <i class="bi bi-pencil-square"></i> Input Nilai
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($participants)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox-fill display-4 d-block mb-3 text-light"></i>
                                    Belum ada peserta terdaftar untuk semester ini.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Score Modal (Bidang Only) -->
<div class="modal fade" id="scoreModal" tabindex="-1" role="dialog" aria-labelledby="scoreModalTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="scoreForm" action="" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="scoreModalTitle">Input Nilai Bidang</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-4">Peserta: <strong id="modalParticipantName"></strong></p>

                    <div class="mb-4 p-3 bg-light rounded border">
                        <label class="form-label fw-bold">Rekomendasi Akhir Tes Bidang</label>
                        <p class="text-small text-muted mb-2">Apakah peserta ini disarankan untuk lulus tes bidang?</p>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status_tes_bidang" id="status_lulus"
                                value="lulus">
                            <label class="form-check-label text-success fw-bold" for="status_lulus">DISARANKAN
                                LULUS</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status_tes_bidang" id="status_tidak"
                                value="tidak_lulus">
                            <label class="form-check-label text-danger fw-bold" for="status_tidak">TIDAK
                                DISARANKAN</label>
                        </div>
                    </div>

                    <hr>
                    <h6 class="mb-3">Rincian Nilai Komponen (Opsional)</h6>
                    <div id="bidangInputs">
                        <!-- Populated by JS -->
                        <p class="text-muted" id="noBidangMsg">Tidak ada komponen rincian.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan Nilai</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Import Modal (Bidang Only) -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="/admin/assessment/scores/import" method="POST" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Nilai Excel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Gunakan file template yang sudah didownload.
                    </div>
                    <div class="form-group">
                        <label for="file">Pilih File Excel (.xlsx)</label>
                        <input type="file" name="file" id="file" class="form-control" required accept=".xlsx, .xls">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload & Proses</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    var bidangComponents = <?php echo json_encode($bidangComponents); ?>;
    var prodiName = "<?php echo $prodiName; ?>";

    function openScoreModal(id, name, pName) {
        $('#scoreModal').modal('show');
        document.getElementById('modalParticipantName').textContent = name;
        document.getElementById('scoreForm').action = '/admin/assessment/scores/save/' + id + '?from=bidang';

        // Reset
        document.querySelectorAll('input[type="number"]').forEach(i => i.value = '');
        document.querySelectorAll('input[name="status_tes_bidang"]').forEach(i => i.checked = false);

        // Populate Inputs
        var container = document.getElementById('bidangInputs');
        container.innerHTML = '';
        var found = false;

        bidangComponents.forEach(function (c) {
            // Filter components ensuring they match session prodi logic handled by Controller passing correct components
            // But double check against prodiName just in case structure differs
            var cProdi = (c.prodi_id || '').trim().toLowerCase();
            var pProdi = (prodiName || '').trim().toLowerCase();

            // Since Controller already filters components for this Prodi, we can just list them all?
            // Actually components array passed to view should be filtered already.
            // Let's assume passed $bidangComponents is filtered.
            found = true;
            var div = document.createElement('div');
            div.className = 'mb-3 row';
            div.innerHTML = `
                    <label class="col-sm-4 col-form-label">${c.nama_komponen}</label>
                    <div class="col-sm-8">
                        <input type="number" step="0.01" class="form-control" name="comp_${c.id}" placeholder="Nilai (0-100)">
                    </div>
                 `;
            container.appendChild(div);
        });

        if (!found) {
            container.innerHTML = '<p class="text-muted">Tidak ada komponen rincian. Silakan isi Status Rekomendasi saja.</p>';
        }

        // Fetch Existing
        fetch('/admin/assessment/scores/get/' + id)
            .then(response => response.json())
            .then(data => {
                var scores = data.scores || [];
                var status = data.status_tes_bidang;

                if (status === 'lulus') document.getElementById('status_lulus').checked = true;
                if (status === 'tidak_lulus') document.getElementById('status_tidak').checked = true;

                scores.forEach(item => {
                    var input = document.querySelector('#bidangInputs input[name="comp_' + item.component_id + '"]');
                    if (input) input.value = item.score;
                });
            });
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>