<?php ob_start(); ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Data Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Home</a></li>
                    <li class="breadcrumb-item active">Data Management</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php if (\App\Utils\RoleHelper::canImportExport()): ?>
            <div class="row">
                <!-- Import Data Peserta -->
                <div class="col-lg-4 col-6 mb-4">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>Import</h3>
                            <p>Import Data Peserta</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-import"></i>
                        </div>
                        <a href="/admin/import" class="small-box-footer">
                            Import Data Peserta <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Document Helper -->
                <div class="col-lg-4 col-6 mb-4">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>Doc Helper</h3>
                            <p>Download & Sinkronisasi Dokumen</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <a href="/admin/document-helper" class="small-box-footer">
                            Helper Dokumen <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Download Berkas -->
                <div class="col-lg-4 col-6 mb-4">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>Download</h3>
                            <p>Download Berkas Peserta</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-archive"></i>
                        </div>
                        <a href="/admin/documents/download" class="small-box-footer">
                            Download Berkas <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Anda tidak memiliki akses ke Data Management.
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>