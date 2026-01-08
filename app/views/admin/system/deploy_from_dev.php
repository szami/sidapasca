<?php ob_start(); ?>
<style>
    .deploy-card {
        border-radius: 1rem;
        overflow: hidden;
    }

    .deploy-header {
        background: linear-gradient(135deg, #6610f2 0%, #9f7aea 100%);
        color: white;
        padding: 2rem;
    }

    .version-box {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 0.75rem;
        padding: 1.5rem;
        text-align: center;
    }

    .version-number {
        font-size: 2rem;
        font-weight: 700;
    }

    .sync-arrow {
        font-size: 2rem;
        color: rgba(255, 255, 255, 0.7);
    }

    .status-card {
        border-radius: 0.75rem;
        border: none;
    }

    .history-item {
        border-left: 3px solid #6610f2;
        padding-left: 1rem;
        margin-bottom: 1rem;
    }

    .btn-deploy {
        background: linear-gradient(135deg, #6610f2 0%, #9f7aea 100%);
        border: none;
        padding: 1rem 2rem;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 0.75rem;
        transition: all 0.3s;
    }

    .btn-deploy:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 16, 242, 0.3);
    }

    .btn-deploy:disabled {
        opacity: 0.6;
        transform: none;
    }
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/tools">System Tools</a></li>
                    <li class="breadcrumb-item active">Deploy from Dev</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Main Deploy Card -->
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card deploy-card shadow-lg border-0">
                <div class="deploy-header">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="version-box">
                                <div class="text-uppercase small opacity-75 mb-2">Development</div>
                                <div class="version-number">
                                    <?= htmlspecialchars($devVersion['version'] ?? 'N/A') ?>
                                </div>
                                <div class="small opacity-75">
                                    <?php if ($devFolderExists): ?>
                                        <i class="fas fa-check-circle text-success"></i> devsida/
                                    <?php else: ?>
                                        <i class="fas fa-times-circle text-danger"></i> Not Found
                                    <?php endif; ?>
                                </div>
                                <div class="mt-2">
                                    <a href="<?= $devUrl ?>" target="_blank" class="badge badge-light text-dark font-weight-normal">
                                        <i class="fas fa-external-link-alt mr-1"></i> opens site
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center py-3">
                            <div class="sync-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                            <div class="small text-white-50 mt-2">Sync Files</div>
                        </div>
                        <div class="col-md-4">
                            <div class="version-box">
                                <div class="text-uppercase small opacity-75 mb-2">Production</div>
                                <div class="version-number">
                                    <?= htmlspecialchars($currentVersion['version'] ?? '1.0.0') ?>
                                </div>
                                <div class="small opacity-75">
                                    <i class="fas fa-server"></i> sidapasca-ulm/
                                </div>
                                <div class="mt-2">
                                    <a href="<?= $prodUrl ?>" target="_blank" class="badge badge-light text-dark font-weight-normal">
                                        <i class="fas fa-external-link-alt mr-1"></i> opens site
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <?php if (!$devFolderExists): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Dev Folder Not Found!</strong><br>
                            Pastikan folder <code>devsida</code> ada di level yang sama dengan folder production.
                            <pre class="mt-2 mb-0 p-2 bg-dark text-white rounded small">
    /home/username/
    ├── devsida/          ← GitHub deploy target
    └── sidapasca-ulm/    ← Production (this folder)
                                </pre>
                        </div>
                    <?php else: ?>
                        <!-- Deployment Action -->
                        <div class="text-center py-4">
                            <button type="button" class="btn btn-deploy btn-lg text-white" id="btnDeploy">
                                <i class="fas fa-rocket mr-2"></i> Deploy from Dev
                            </button>
                            <p class="text-muted mt-3 mb-0">
                                <i class="fas fa-info-circle mr-1"></i>
                                Proses ini akan menyalin file dari <code>devsida</code> ke folder production
                            </p>
                        </div>

                        <!-- Progress -->
                        <div id="deployProgress" class="d-none">
                            <hr>
                            <div class="text-center py-3">
                                <div class="spinner-border text-primary mb-3" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <h5>Deploying...</h5>
                                <p class="text-muted">Menyalin file dan menjalankan migration</p>
                            </div>
                        </div>

                        <!-- Result -->
                        <div id="deployResult" class="d-none">
                            <hr>
                            <div id="resultContent"></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Protected Files Info -->
            <div class="card mt-4 shadow-sm border-0">
                <div class="card-header bg-light">
                    <i class="fas fa-shield-alt mr-2 text-primary"></i>
                    File yang Dilindungi (Tidak Akan Di-overwrite)
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <ul class="list-unstyled mb-0">
                                <li><code>.env</code> - Environment config</li>
                                <li><code>.git/</code> - Git repository</li>
                                <li><code>storage/database.sqlite</code></li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <ul class="list-unstyled mb-0">
                                <li><code>storage/photos/</code></li>
                                <li><code>storage/documents/</code></li>
                                <li><code>storage/backups/</code></li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <ul class="list-unstyled mb-0">
                                <li><code>sync.php</code></li>
                                <li><code>deploy.php</code></li>
                                <li><code>config/db.php</code></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deployment History -->
            <?php if (!empty($deployHistory)): ?>
                <div class="card mt-4 shadow-sm border-0">
                    <div class="card-header bg-light">
                        <i class="fas fa-history mr-2 text-primary"></i>
                        Riwayat Deployment (10 Terakhir)
                    </div>
                    <div class="card-body">
                        <?php foreach ($deployHistory as $deploy): ?>
                            <div class="history-item">
                                <div class="d-flex justify-content-between">
                                    <strong>
                                        <i class="fas fa-user-circle mr-1"></i>
                                        <?= htmlspecialchars($deploy['user']) ?>
                                    </strong>
                                    <span class="text-muted small">
                                        <?= htmlspecialchars($deploy['timestamp']) ?>
                                    </span>
                                </div>
                                <div class="small text-muted">
                                    Files:
                                    <?= $deploy['stats']['files'] ?? 0 ?> |
                                    Dirs:
                                    <?= $deploy['stats']['dirs'] ?? 0 ?> |
                                    Skipped:
                                    <?= $deploy['stats']['skipped'] ?? 0 ?> |
                                    Duration:
                                    <?= $deploy['duration'] ?? '-' ?>s |
                                    Migration:
                                    <?= htmlspecialchars($deploy['migration'] ?? '-') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnDeploy = document.getElementById('btnDeploy');
        const deployProgress = document.getElementById('deployProgress');
        const deployResult = document.getElementById('deployResult');
        const resultContent = document.getElementById('resultContent');

        if (btnDeploy) {
            btnDeploy.addEventListener('click', function () {
                if (!confirm('Apakah Anda yakin ingin deploy dari folder devsida?\n\nProses ini akan menimpa file yang ada di production.')) {
                    return;
                }

                btnDeploy.disabled = true;
                btnDeploy.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
                deployProgress.classList.remove('d-none');
                deployResult.classList.add('d-none');

                fetch('/admin/system/deploy-from-dev/execute', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        deployProgress.classList.add('d-none');
                        deployResult.classList.remove('d-none');

                        if (data.success) {
                            resultContent.innerHTML = `
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle mr-2"></i> Deployment Berhasil!</h5>
                            <p class="mb-0">${data.message}</p>
                        </div>
                        <div class="row text-center">
                            <div class="col-3">
                                <div class="h4 text-primary">${data.stats.files}</div>
                                <div class="small text-muted">Files</div>
                            </div>
                            <div class="col-3">
                                <div class="h4 text-info">${data.stats.dirs}</div>
                                <div class="small text-muted">Dirs</div>
                            </div>
                            <div class="col-3">
                                <div class="h4 text-secondary">${data.stats.skipped}</div>
                                <div class="small text-muted">Skipped</div>
                            </div>
                            <div class="col-3">
                                <div class="h4 ${data.stats.errors > 0 ? 'text-danger' : 'text-success'}">${data.stats.errors}</div>
                                <div class="small text-muted">Errors</div>
                            </div>
                        </div>
                        <hr>
                        <p><strong>Migration:</strong> ${data.migration}</p>
                        ${data.errors && data.errors.length > 0 ? '<div class="alert alert-warning"><strong>Errors:</strong><br>' + data.errors.join('<br>') + '</div>' : ''}
                        <div class="text-center mt-3">
                            <button class="btn btn-primary" onclick="location.reload()">
                                <i class="fas fa-sync-alt mr-1"></i> Refresh
                            </button>
                        </div>
                    `;
                        } else {
                            resultContent.innerHTML = `
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-times-circle mr-2"></i> Deployment Gagal</h5>
                            <p class="mb-0">${data.message}</p>
                        </div>
                    `;
                        }

                        btnDeploy.disabled = false;
                        btnDeploy.innerHTML = '<i class="fas fa-rocket mr-2"></i> Deploy from Dev';
                    })
                    .catch(error => {
                        deployProgress.classList.add('d-none');
                        deployResult.classList.remove('d-none');
                        resultContent.innerHTML = `
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-times-circle mr-2"></i> Error</h5>
                        <p class="mb-0">${error.message}</p>
                    </div>
                `;
                        btnDeploy.disabled = false;
                        btnDeploy.innerHTML = '<i class="fas fa-rocket mr-2"></i> Deploy from Dev';
                    });
            });
        }
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>