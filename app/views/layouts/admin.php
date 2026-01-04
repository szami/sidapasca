<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo \App\Models\Setting::get('app_name', 'SIDA Pasca ULM'); ?> | Admin</title>

    <!-- Favicon -->
    <?php $favicon = \App\Models\Setting::get('app_favicon'); ?>
    <?php if ($favicon): ?>
        <link rel="icon" href="<?php echo $favicon; ?>">
    <?php endif; ?>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <!-- Chart.js -->
    <script src="/public/js/chart.umd.min.js"></script>
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="/public/css/custom-admin.css">
    <!-- Summernote -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.css">

    <!-- Core Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <!-- Summernote JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.js"></script>
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <?php
                    $activeSem = \App\Models\Semester::getActive();
                    if ($activeSem):
                        $showPeriode = (strpos(strtolower($activeSem['nama']), 'ganjil') !== false && $activeSem['periode'] > 0);
                        ?>
                        <span class="nav-link text-bold text-primary">
                            <i class="fas fa-calendar-check mr-1"></i>
                            <?php echo $activeSem['nama']; ?>
                            <?php if ($showPeriode): ?>
                                <span class="badge badge-primary ml-1">Periode <?php echo $activeSem['periode']; ?></span>
                            <?php endif; ?>
                            <a href="/admin/semesters" class="ml-2 text-muted" title="Pengaturan Semester">
                                <i class="fas fa-cog fa-xs"></i>
                            </a>
                        </span>
                    <?php else: ?>
                        <a href="/admin" class="nav-link">Home</a>
                    <?php endif; ?>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/logout" role="button">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="/admin" class="brand-link">
                <?php $logo = \App\Models\Setting::get('app_logo'); ?>
                <?php if ($logo): ?>
                    <img src="<?php echo $logo; ?>" alt="Logo" class="brand-image img-circle elevation-3"
                        style="opacity: .8">
                <?php endif; ?>
                <span
                    class="brand-text font-weight-light px-3"><?php echo \App\Models\Setting::get('app_name', 'SIDA Pasca ULM'); ?></span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <li class="nav-item">
                            <a href="/admin" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>


                        <li class="nav-header">ADMISI PASCA</li>
                        <li class="nav-item">
                            <a href="/admin/participants?filter=pending" class="nav-link">
                                <i class="nav-icon fas fa-file-signature"></i>
                                <p>Formulir Masuk</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/admin/participants?filter=lulus" class="nav-link">
                                <i class="nav-icon fas fa-user-check"></i>
                                <p>Lulus Berkas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/admin/participants?filter=gagal" class="nav-link">
                                <i class="nav-icon fas fa-user-times"></i>
                                <p>Gagal Berkas</p>
                            </a>
                        </li>

                        <li class="nav-header">PESERTA UJIAN</li>
                        <li class="nav-item">
                            <a href="/admin/participants?filter=exam_ready" class="nav-link">
                                <i class="nav-icon fas fa-id-card-alt"></i>
                                <p>Data Peserta</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/admin/laporan" class="nav-link">
                                <i class="nav-icon fas fa-file-alt"></i>
                                <p>Laporan Admisi</p>
                            </a>
                        </li>

                        <li class="nav-header">OPERASIONAL</li>
                        <?php
                        $role = \App\Utils\RoleHelper::getRole();
                        $isSuperadmin = \App\Utils\RoleHelper::isSuperadmin();
                        $isAdmin = \App\Utils\RoleHelper::isAdmin();
                        $isAdminProdi = \App\Utils\RoleHelper::isAdminProdi();
                        ?>

                        <!-- DASHBOARD - All users -->
                        <li class="nav-item">
                            <a href="/admin" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <!-- MASTER DATA - Admin & Superadmin only -->
                        <?php if (!$isAdminProdi): ?>
                            <li class="nav-header">MASTER DATA</li>
                            <li class="nav-item">
                                <a href="/admin/semesters" class="nav-link">
                                    <i class="nav-icon fas fa-calendar-alt"></i>
                                    <p>Semester</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/admin/exam-rooms" class="nav-link">
                                    <i class="nav-icon fas fa-door-open"></i>
                                    <p>Ruang Ujian</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/admin/exam-sessions" class="nav-link">
                                    <i class="nav-icon fas fa-clock"></i>
                                    <p>Sesi Ujian</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- PARTICIPANTS - All users -->
                        <li class="nav-header">
                            <?php echo $isAdminProdi ? 'DATA PRODI' : 'PESERTA'; ?>
                        </li>
                        <li class="nav-item">
                            <a href="/admin/participants" class="nav-link">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Data Peserta</p>
                            </a>
                        </li>

                        <!-- EXAM MANAGEMENT - Admin & Superadmin only -->
                        <?php if (!$isAdminProdi): ?>
                            <li class="nav-item">
                                <a href="/admin/exam-scheduler" class="nav-link">
                                    <i class="nav-icon fas fa-user-check"></i>
                                    <p>Jadwalkan Ujian</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/admin/exam-card" class="nav-link">
                                    <i class="nav-icon fas fa-id-card"></i>
                                    <p>Kartu Ujian</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/admin/attendance" class="nav-link">
                                    <i class="nav-icon fas fa-clipboard-check"></i>
                                    <p>Kehadiran Ujian</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- REPORTS - All users -->
                        <li class="nav-header">LAPORAN</li>
                        <li class="nav-item">
                            <a href="/admin/reports" class="nav-link">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>Laporan</p>
                            </a>
                        </li>

                        <!-- EMAIL - Admin & Superadmin only -->
                        <?php if (!$isAdminProdi): ?>
                            <li class="nav-header">KOMUNIKASI</li>
                            <li class="nav-item">
                                <a href="/admin/email/config" class="nav-link">
                                    <i class="nav-icon fas fa-envelope-open-text"></i>
                                    <p>Konfigurasi Email</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/admin/email/templates" class="nav-link">
                                    <i class="nav-icon fas fa-file-alt"></i>
                                    <p>Template Email</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/admin/email/reminders" class="nav-link">
                                    <i class="nav-icon fas fa-paper-plane"></i>
                                    <p>Reminder Email</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- TOOLS - Admin & Superadmin only -->
                        <?php if (!$isAdminProdi): ?>
                            <li class="nav-header">TOOLS</li>

                            <li class="nav-item">
                                <a href="/admin/import" class="nav-link">
                                    <i class="nav-icon fas fa-file-import"></i>
                                    <p>Import Data</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/admin/document-import" class="nav-link">
                                    <i class="nav-icon fas fa-images"></i>
                                    <p>Import Berkas</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="/admin/participants/export" class="nav-link">
                                    <i class="nav-icon fas fa-file-excel"></i>
                                    <p>Export Data</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/admin/documents/download" class="nav-link">
                                    <i class="nav-icon fas fa-file-archive"></i>
                                    <p>Download Berkas</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- SYSTEM SETTINGS - Admin & Superadmin only -->
                        <?php if (!$isAdminProdi): ?>
                            <li class="nav-header">PENGATURAN SYSTEM</li>
                            <li class="nav-item">
                                <a href="/admin/exam-card/design" class="nav-link">
                                    <i class="nav-icon fas fa-paint-brush"></i>
                                    <p>Desain Kartu Ujian</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/admin/settings" class="nav-link">
                                    <i class="nav-icon fas fa-cogs"></i>
                                    <p>Pengaturan</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- User Management - Superadmin only -->
                        <?php if ($isSuperadmin): ?>
                            <li class="nav-item">
                                <a href="/admin/users" class="nav-link">
                                    <i class="nav-icon fas fa-users-cog"></i>
                                    <p>Manajemen User</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/admin/system/update" class="nav-link">
                                    <i class="nav-icon fas fa-sync"></i>
                                    <p>Update Sistem</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Change Password - All users -->
                        <li class="nav-header">AKUN</li>
                        <li class="nav-item">
                            <a href="/admin/change-password" class="nav-link">
                                <i class="nav-icon fas fa-key"></i>
                                <p>Ganti Password</p>
                            </a>
                        </li>
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <!-- Page Title Area (Can be injected) -->
                </div>
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <?php echo $content ?? ''; ?>
                </div>
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <?php
                $versionFile = __DIR__ . '/../../../version.json';
                $version = '1.0.0';
                if (file_exists($versionFile)) {
                    $versionData = json_decode(file_get_contents($versionFile), true);
                    $version = $versionData['version'] ?? '1.0.0';
                }
                ?>
                <b>Version</b> <?php echo $version; ?>
            </div>
            <strong>Copyright &copy; <?php echo date('Y'); ?> <a href="#">Pascasarjana ULM</a>.</strong> All rights
            reserved.
        </footer>
    </div>
    <!-- ./wrapper -->

    <!-- Additional JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(function () {
            // Auto-init DataTables for tables with class 'datatable'
            $(".datatable").DataTable({
                "responsive": true, "lengthChange": true, "autoWidth": false,
                "buttons": ["copy", "csv", "excel", "pdf", "print"]
            });
        });
    </script>
</body>

</html>