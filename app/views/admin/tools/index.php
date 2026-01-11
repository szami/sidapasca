<?php ob_start(); ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">System Tools</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Home</a></li>
                    <li class="breadcrumb-item active">System Tools</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php if (\App\Utils\RoleHelper::canImportExport()): ?>
            <h5 class="mt-4 mb-2"><i class="fas fa-database mr-1"></i> Database Tools</h5>
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>Migration</h3>
                            <p>Database Schema Sync</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <a href="/admin/tools/migration" class="small-box-footer">Check & Sync <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>Sync Guide</h3>
                            <p>Sinkronisasi Eksternal</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-sync"></i>
                        </div>
                        <a href="/admin/sync-guide" class="small-box-footer">View Guide <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (\App\Utils\RoleHelper::canManageEmail()): ?>
            <h5 class="mt-4 mb-2"><i class="fas fa-envelope mr-1"></i> Email System</h5>
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-purple">
                        <div class="inner">
                            <h3>Templates</h3>
                            <p>Kelola Template Email</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <a href="/admin/email/templates" class="small-box-footer">Manage Templates <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-purple">
                        <div class="inner">
                            <h3>Reminders</h3>
                            <p>Kirim & Log Reminder</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <a href="/admin/email/reminders" class="small-box-footer">Go to Reminders <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3>Config</h3>
                            <p>Konfigurasi Email/SMTP</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <a href="/admin/email/config" class="small-box-footer">Configure <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (\App\Utils\RoleHelper::canManageSettings()): ?>
            <h5 class="mt-4 mb-2"><i class="fas fa-cogs mr-1"></i> System Configuration</h5>
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>Card Design</h3>
                            <p>Desain Kartu Ujian</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <a href="/admin/exam-card/design" class="small-box-footer">Edit Design <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-dark">
                        <div class="inner">
                            <h3>Settings</h3>
                            <p>Pengaturan Sistem Utama</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <a href="/admin/settings" class="small-box-footer">Manage Settings <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <?php if (\App\Utils\RoleHelper::isSuperadmin()): ?>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-secondary">
                            <div class="inner">
                                <h3>Users</h3>
                                <p>Manajemen User Admin</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users-cog"></i>
                            </div>
                            <a href="/admin/users" class="small-box-footer">Manage Users <i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (\App\Utils\RoleHelper::isSuperadmin()): ?>
            <h5 class="mt-4 mb-2"><i class="fas fa-tools mr-1"></i> User & Maintenance</h5>
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-purple">
                        <div class="inner">
                            <h3>Deploy</h3>
                            <p>Deploy from Dev Folder</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <a href="/admin/system/deploy-from-dev" class="small-box-footer">Deploy Now <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>Update</h3>
                            <p>System Update (Git)</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <a href="/admin/system/update" class="small-box-footer">Check Updates <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
$content = ob_get_clean();
$title = 'System Tools';
include __DIR__ . '/../../layouts/admin.php';
?>