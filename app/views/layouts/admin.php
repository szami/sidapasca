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
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
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
    <!-- Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- Summernote JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.js"></script>

    <script>
        // Global App URL for JS (Handles subdirectory hosting)
        const APP_URL = "<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>";
    </script>
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
                            <?php if (\App\Utils\RoleHelper::isSuperadmin()): ?>
                                <a href="/admin/semesters" class="ml-2 text-muted" title="Pengaturan Semester">
                                    <i class="fas fa-cog fa-xs"></i>
                                </a>
                            <?php endif; ?>
                        </span>
                    <?php else: ?>
                        <a href="/admin" class="nav-link">Home</a>
                    <?php endif; ?>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/admin/logout" role="button">
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
                <!-- Sidebar user panel -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <i class="fas fa-user-circle fa-2x text-light"></i>
                    </div>
                    <div class="info">
                        <a href="#" class="d-block"><?php echo \App\Utils\RoleHelper::getUsername() ?? 'User'; ?></a>
                        <span class="badge <?php echo \App\Utils\RoleHelper::getRoleBadgeClass(); ?> text-xs">
                            <?php echo \App\Utils\RoleHelper::getRoleDisplayName(); ?>
                        </span>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <?php
                        // Get role permissions
                        $canEditParticipant = \App\Utils\RoleHelper::canEditParticipant();
                        $canValidatePhysical = \App\Utils\RoleHelper::canValidatePhysical();
                        $canManageSchedule = \App\Utils\RoleHelper::canManageSchedule();
                        $canManageUsers = \App\Utils\RoleHelper::canManageUsers();
                        $canImportExport = \App\Utils\RoleHelper::canImportExport();
                        $canManageSettings = \App\Utils\RoleHelper::canManageSettings();
                        $canManageEmail = \App\Utils\RoleHelper::canManageEmail();
                        $canPrintCards = \App\Utils\RoleHelper::canPrintCards();
                        $canPrintSchedule = \App\Utils\RoleHelper::canPrintSchedule();
                        $canManageMasterData = \App\Utils\RoleHelper::canManageMasterData();
                        $canDownloadDocuments = \App\Utils\RoleHelper::canDownloadDocuments();
                        $canViewReports = \App\Utils\RoleHelper::canViewReports();
                        $canManageAssessmentBidang = \App\Utils\RoleHelper::canManageAssessmentBidang();
                        $isSuperadmin = \App\Utils\RoleHelper::isSuperadmin();
                        $isAdminProdi = \App\Utils\RoleHelper::isAdminProdi();
                        $isUPKH = \App\Utils\RoleHelper::isUPKH();
                        $isTU = \App\Utils\RoleHelper::isTU();
                        ?>

                        <!-- DASHBOARD -->
                        <li class="nav-item">
                            <a href="/admin" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <!-- ADMISI & PESERTA -->
                        <li class="nav-header">ADMISI & PESERTA</li>
                        <?php if (!$isTU && !$isUPKH): ?>
                            <li class="nav-item">
                                <a href="/admin/participants?filter=pending" class="nav-link">
                                    <i class="nav-icon fas fa-file-signature"></i>
                                    <p>Formulir Masuk</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/admin/participants?filter=lulus" class="nav-link">
                                    <i class="nav-icon fas fa-user-check"></i>
                                    <p>Verifikasi Online</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/admin/participants?filter=gagal" class="nav-link">
                                    <i class="nav-icon fas fa-user-times"></i>
                                    <p>Berkas Tidak Valid</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if ($canValidatePhysical): ?>
                            <li class="nav-item">
                                <a href="/admin/verification/physical" class="nav-link">
                                    <i class="nav-icon fas fa-tasks"></i>
                                    <p>Verifikasi Berkas</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <li class="nav-item">
                            <a href="/admin/participants?filter=exam_ready" class="nav-link">
                                <i class="nav-icon fas fa-id-card-alt"></i>
                                <p>Data Peserta Ujian</p>
                            </a>
                        </li>

                        <?php if ($canViewReports): ?>
                            <li class="nav-item">
                                <a href="/admin/laporan" class="nav-link">
                                    <i class="nav-icon fas fa-file-alt"></i>
                                    <p>Laporan Admisi</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if ($canDownloadDocuments): ?>
                            <li class="nav-item">
                                <a href="/admin/documents/download" class="nav-link">
                                    <i class="nav-icon fas fa-file-archive"></i>
                                    <p>Download Berkas</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- PENJADWALAN & UJIAN -->
                        <?php if ($canManageSchedule || $canPrintSchedule): ?>
                            <li class="nav-header">MANAJEMEN UJIAN</li>
                            <li class="nav-item">
                                <a href="/admin/exam" class="nav-link">
                                    <i class="nav-icon fas fa-calendar-check"></i>
                                    <p>
                                        Manajemen Ujian
                                        <span class="right badge badge-info">Hub</span>
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- ASSESSMENT & KELULUSAN -->
                        <?php if ($isSuperadmin || \App\Utils\RoleHelper::isAdmin() || $canManageAssessmentBidang): ?>
                            <li class="nav-header">PENILAIAN & KELULUSAN</li>
                            <li class="nav-item">
                                <a href="/admin/assessment" class="nav-link">
                                    <i class="nav-icon fas fa-graduation-cap"></i>
                                    <p>
                                        Penilaian & Kelulusan
                                        <span class="right badge badge-info">Hub</span>
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- MASTER DATA -->
                        <?php if ($canManageMasterData): ?>
                            <li class="nav-header">MASTER DATA</li>
                            <?php if ($isSuperadmin): ?>
                                <li class="nav-item">
                                    <a href="/admin/semesters" class="nav-link">
                                        <i class="nav-icon fas fa-layer-group"></i>
                                        <p>Semester</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li class="nav-item">
                                <a href="/admin/master/rooms" class="nav-link">
                                    <i class="nav-icon fas fa-door-open"></i>
                                    <p>Ruang Ujian</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/admin/master/sessions" class="nav-link">
                                    <i class="nav-icon fas fa-clock"></i>
                                    <p>Sesi Ujian</p>
                                </a>
                            </li>
                        <?php endif; ?>


                        <!-- MANAJEMEN KONTEN -->
                        <li class="nav-header">INFORMASI & PANDUAN</li>
                        <?php if ($isSuperadmin || \App\Utils\RoleHelper::isAdmin()): ?>
                            <li class="nav-item">
                                <a href="/admin/news" class="nav-link">
                                    <i class="nav-icon fas fa-newspaper"></i>
                                    <p>Berita & Informasi</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a href="/admin/guides" class="nav-link">
                                <i class="nav-icon fas fa-book"></i>
                                <p>Panduan Sistem</p>
                            </a>
                        </li>

                        <!-- SYSTEM TOOLS -->
                        <?php if (\App\Utils\RoleHelper::canAccessToolsHub()): ?>
                            <li class="nav-item">
                                <a href="/admin/tools" class="nav-link">
                                    <i class="nav-icon fas fa-cogs"></i>
                                    <p>
                                        System Tools
                                        <span class="right badge badge-info">Hub</span>
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- AKUN -->
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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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