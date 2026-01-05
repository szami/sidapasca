<?php

session_start();

require __DIR__ . '/vendor/autoload.php';

// Load Env
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
} catch (\Throwable $e) {
    // Graceful fallback or setup mode
    // For now, let's just allow it to continue if possible, or die with a pretty message
    // But user asked for "setup otomatis" or at least not fatal error.
    // If env is bad, we might fallback to defaults or show a setup page.
    die("<h1>System Error</h1><p>Failed to load configuration file (.env). Please ensure it is valid.</p><p>Error: " . $e->getMessage() . "</p>");
}

// Set Timezone to WITA (Waktu Indonesia Tengah / UTC+8)
date_default_timezone_set('Asia/Makassar');

// Config - Try Connect DB
try {
    require __DIR__ . '/config/db.php';
} catch (\Throwable $e) {
    // If we are NOT already on the setup page, redirect/show setup link
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    // Basic check to allow /setup to work even if DB is broken
    if (strpos($uri, '/setup') === false) {
        header('Location: /setup');
        exit;
    }
}

// Global Maintenance Mode Check (Force Logout)
if (\App\Utils\Database::connection() && \App\Models\Setting::get('maintenance_mode', 'off') === 'on') {
    $isAdmin = isset($_SESSION['admin']);
    $isSuperadmin = $isAdmin && ($_SESSION['admin_role'] ?? 'admin') === 'superadmin';
    $isLoggedIn = $isAdmin || isset($_SESSION['user']);
    $uri = $_SERVER['REQUEST_URI'] ?? '/';

    // Expel non-superadmins from protected pages
    if ($isLoggedIn && !$isSuperadmin && $uri !== '/logout') {
        session_destroy();
        $target = $isAdmin ? '/admin?error=maintenance' : '/login?error=maintenance';
        header("Location: $target");
        exit;
    }
}

// Init Leaf
$app = new Leaf\App();
$app->setBasePath(dirname($_SERVER['SCRIPT_NAME']));

// Setup Routes (Available even if DB fails, assuming we handle it manually in Controller)
$app->get('/setup', 'App\Controllers\SetupController@index');
$app->post('/setup/migrate', 'App\Controllers\SetupController@migrate');

// Routes
$app->get('/', 'App\Controllers\HomeController@index');
$app->get('/login', 'App\Controllers\AuthController@loginView');
$app->post('/login', 'App\Controllers\AuthController@login');
$app->get('/logout', 'App\Controllers\AuthController@logout');

// Admin Routes
$app->get('/admin', 'App\Controllers\AdminController@dashboard');
$app->post('/admin/login', 'App\Controllers\AuthController@adminLogin');
$app->get('/admin/logout', 'App\Controllers\AuthController@adminLogout'); // Explicit Admin Logout
$app->get('/admin/import', 'App\Controllers\ImportController@index'); // --- Admin - Data Master ---
$app->post('/admin/import', 'App\Controllers\ImportController@import');

// Report Routes
$app->get('/admin/laporan', 'App\Controllers\ReportController@index');
$app->post('/admin/laporan/cetak', 'App\Controllers\ReportController@print');

// Semester Routes
$app->get('/admin/semesters', 'App\Controllers\SemesterController@index');
$app->get('/admin/semesters/create', 'App\Controllers\SemesterController@create');
$app->post('/admin/semesters/store', 'App\Controllers\SemesterController@store');
$app->get('/admin/semesters/set-active/{id}', 'App\Controllers\SemesterController@setActive');
$app->get('/admin/semesters/edit/{id}', 'App\Controllers\SemesterController@edit');
$app->post('/admin/semesters/update/{id}', 'App\Controllers\SemesterController@update');
$app->get('/admin/semesters/delete/{id}', 'App\Controllers\SemesterController@destroy');
$app->get('/admin/semesters/clean/{id}', 'App\Controllers\SemesterController@cleanParticipants');

// Master Ruang Ujian
$app->get('/api/master/rooms', 'App\Controllers\ExamRoomController@apiData');
$app->get('/admin/master/rooms', 'App\Controllers\ExamRoomController@index');
$app->get('/admin/master/rooms/create', 'App\Controllers\ExamRoomController@create');
$app->post('/admin/master/rooms/store', 'App\Controllers\ExamRoomController@store');
$app->get('/admin/master/rooms/edit/{id}', 'App\Controllers\ExamRoomController@edit');
$app->post('/admin/master/rooms/update/{id}', 'App\Controllers\ExamRoomController@update');
$app->get('/admin/master/rooms/delete/{id}', 'App\Controllers\ExamRoomController@destroy');

// Master Sesi Ujian
$app->get('/api/master/sessions', 'App\Controllers\ExamSessionController@apiData');
$app->get('/admin/master/sessions', 'App\Controllers\ExamSessionController@index');
$app->get('/admin/master/sessions/create', 'App\Controllers\ExamSessionController@create');
$app->post('/admin/master/sessions/store', 'App\Controllers\ExamSessionController@store');
$app->get('/admin/master/sessions/edit/{id}', 'App\Controllers\ExamSessionController@edit');
$app->post('/admin/master/sessions/update/{id}', 'App\Controllers\ExamSessionController@update');
$app->get('/admin/master/sessions/delete/{id}', 'App\Controllers\ExamSessionController@destroy');

// Scheduler
$app->get('/api/scheduler', 'App\Controllers\ExamSchedulerController@apiData');
$app->get('/admin/scheduler', 'App\Controllers\ExamSchedulerController@index');
$app->get('/admin/scheduler/rooms', 'App\Controllers\ExamSchedulerController@roomView'); // NEW
$app->post('/admin/scheduler/assign', 'App\Controllers\ExamSchedulerController@assign');
$app->post('/admin/scheduler/unassign', 'App\Controllers\ExamSchedulerController@unassign');

// Presence (NEW)
$app->get('/admin/attendance', 'App\Controllers\AttendanceController@index');
$app->get('/admin/attendance/entry', 'App\Controllers\AttendanceController@entry');
$app->post('/admin/attendance/save', 'App\Controllers\AttendanceController@save');

// Pengaturan & System
$app->get('/admin/settings', 'App\Controllers\SettingsController@index');
$app->post('/admin/settings/save', 'App\Controllers\SettingsController@save');
$app->get('/admin/settings/optimize', 'App\Controllers\SettingsController@optimizeDB');
$app->post('/admin/settings/clean-semester', 'App\Controllers\SettingsController@cleanSemester');
$app->post('/admin/settings/backup-database', 'App\Controllers\SettingsController@backupDatabase');
$app->post('/admin/settings/restore-database', 'App\Controllers\SettingsController@restoreDatabase');
// Data Import
$app->get('/admin/import', 'App\Controllers\ImportController@index');
$app->post('/admin/import', 'App\Controllers\ImportController@import');
$app->post('/admin/import/auto-download', 'App\Controllers\ImportController@autoDownload');

// Manajemen Kartu Ujian (DEDICATED)
$app->get('/admin/exam-card/design', 'App\Controllers\ExamCardSettingController@design');
$app->post('/admin/exam-card/design/save', 'App\Controllers\ExamCardSettingController@saveDesign');

// Document Import Interface
$app->get('/admin/document-import', 'App\Controllers\DocumentImportController@index');
$app->get('/admin/document-import/bulk', 'App\Controllers\DocumentImportController@bulkDownload');
$app->post('/admin/document-import/save-cookie', 'App\Controllers\DocumentImportController@saveSessionCookie');

// Physical Verification Routes
$app->get('/api/verification/physical', 'App\Controllers\DocumentVerificationController@apiData');
$app->get('/admin/verification/physical', 'App\Controllers\DocumentVerificationController@index');
$app->get('/admin/verification/physical/{id}', 'App\Controllers\DocumentVerificationController@verify');
$app->post('/admin/verification/physical/{id}/save', 'App\Controllers\DocumentVerificationController@save');
$app->post('/admin/verification/physical/{id}/reset', 'App\Controllers\DocumentVerificationController@reset');
$app->get('/admin/verification/physical/import/template', 'App\Controllers\DocumentVerificationController@downloadTemplate');
$app->post('/admin/verification/physical/import', 'App\Controllers\DocumentVerificationController@import');

// Email Reminder Routes
$app->get('/api/email/reminders/history', 'App\Controllers\EmailReminderController@apiHistory');
$app->get('/api/email/reminders/participants', 'App\Controllers\EmailReminderController@apiData');
$app->get('/admin/email/config', 'App\Controllers\EmailConfigController@index');
$app->post('/admin/email/config/save', 'App\Controllers\EmailConfigController@save');
$app->post('/admin/email/config/test', 'App\Controllers\EmailConfigController@testConnection');

$app->get('/api/email/templates', 'App\Controllers\EmailTemplateController@apiData');
$app->get('/admin/email/templates', 'App\Controllers\EmailTemplateController@index');
$app->post('/admin/email/templates/create', 'App\Controllers\EmailTemplateController@create');
$app->post('/admin/email/templates/update/{id}', 'App\Controllers\EmailTemplateController@update');
$app->post('/admin/email/templates/delete/{id}', 'App\Controllers\EmailTemplateController@delete');
$app->get('/admin/email/templates/get/{id}', 'App\Controllers\EmailTemplateController@get');

$app->get('/admin/email/reminders', 'App\Controllers\EmailReminderController@index');
$app->get('/admin/email/reminders/send', 'App\Controllers\EmailReminderController@create');
$app->post('/admin/email/reminders/send', 'App\Controllers\EmailReminderController@send');
$app->get('/admin/email/reminders/{id}', 'App\Controllers\EmailReminderController@detail');

$app->get('/admin/system/update', 'App\Controllers\SystemController@update');
$app->post('/admin/system/perform-update', 'App\Controllers\SystemController@performUpdate');
$app->get('/admin/system/check-update', 'App\Controllers\SystemController@checkUpdate');

// Assessment Module
$app->get('/admin/assessment/components', 'App\Controllers\AssessmentController@components');
$app->post('/admin/assessment/components/store', 'App\Controllers\AssessmentController@storeComponent');
$app->get('/admin/assessment/components/delete/{id}', 'App\Controllers\AssessmentController@deleteComponent');

$app->get('/api/assessment/scores', 'App\Controllers\AssessmentController@apiData');
$app->get('/admin/assessment/scores', 'App\Controllers\AssessmentController@scores');
$app->get('/admin/assessment/scores/get/{id}', 'App\Controllers\AssessmentController@getScores');
$app->post('/admin/assessment/scores/save/{id}', 'App\Controllers\AssessmentController@saveScore');
$app->get('/admin/assessment/scores/export', 'App\Controllers\AssessmentController@exportTemplate');
$app->get('/admin/assessment/scores/export-final', 'App\Controllers\AssessmentController@exportFinal');
$app->post('/admin/assessment/scores/import', 'App\Controllers\AssessmentController@importScores');
$app->post('/admin/assessment/scores/import-tpa', 'App\Controllers\AssessmentController@importTPA');
$app->post('/admin/assessment/scores/save-final', 'App\Controllers\AssessmentController@saveFinalDecision');
$app->post('/admin/assessment/scores/import-final', 'App\Controllers\AssessmentController@importFinal');

// Admin Prodi Bidang Page
$app->get('/admin/assessment/bidang', 'App\Controllers\AssessmentController@bidangScores');
$app->get('/admin/assessment/bidang/export', 'App\Controllers\AssessmentController@exportBidangReport');
$app->get('/admin/assessment/bidang/reset', 'App\Controllers\AssessmentController@resetBidangScores');
$app->post('/admin/assessment/threshold/save', 'App\Controllers\AssessmentController@saveMinimumThreshold');
$app->post('/admin/assessment/threshold/save-tpa', 'App\Controllers\AssessmentController@saveTpaThreshold');
$app->post('/admin/assessment/schedule/save', 'App\Controllers\AssessmentController@saveBidangSchedule');

// Graduation Module
$app->get('/api/master/quotas', 'App\Controllers\GraduationController@apiData');
$app->get('/admin/graduation/quotas', 'App\Controllers\GraduationController@quotas');
$app->post('/admin/graduation/quotas/save', 'App\Controllers\GraduationController@saveQuotas');

// --- Admin - User Management (Superadmin only) ---
$app->get('/admin/users', 'App\Controllers\UserController@index');
$app->get('/admin/users/create', 'App\Controllers\UserController@create');
$app->post('/admin/users/store', 'App\Controllers\UserController@store');
$app->get('/admin/users/edit/{id}', 'App\Controllers\UserController@edit');
$app->post('/admin/users/update/{id}', 'App\Controllers\UserController@update');
$app->get('/admin/users/delete/{id}', 'App\Controllers\UserController@destroy');

// --- Change Password (All users) ---
$app->get('/admin/change-password', 'App\Controllers\UserController@changePasswordForm');
$app->post('/admin/change-password', 'App\Controllers\UserController@changePassword');

// --- Document Download (Admin & Superadmin) ---
$app->get('/admin/documents/download', 'App\Controllers\DocumentDownloadController@index');
$app->post('/admin/documents/preview', 'App\Controllers\DocumentDownloadController@preview');
$app->post('/admin/documents/generate-zip', 'App\Controllers\DocumentDownloadController@generateZip');

// --- Admin - Participants ---
// Participant CRUD Routes
$app->get('/api/participants', 'App\Controllers\ParticipantController@apiData');
$app->get('/admin/participants', 'App\Controllers\ParticipantController@index');
$app->get('/admin/participants/view/{id}', 'App\Controllers\ParticipantController@view');
$app->get('/admin/participants/documents/{id}', 'App\Controllers\ParticipantController@documents'); // Document management
$app->get('/admin/participants/edit/{id}', 'App\Controllers\ParticipantController@edit');
$app->get('/admin/participants/export', 'App\Controllers\ParticipantController@exportExcel');
$app->post('/admin/participants/update/{id}', 'App\Controllers\ParticipantController@update');
$app->get('/admin/participants/delete/{id}', 'App\Controllers\ParticipantController@destroy');
$app->post('/admin/participants/{id}/upload-photo', 'App\Controllers\ParticipantController@uploadPhoto');
$app->delete('/admin/participants/{id}/delete-photo', 'App\Controllers\ParticipantController@deletePhoto');
$app->post('/admin/participants/{id}/auto-download-photo', 'App\Controllers\ParticipantController@autoDownloadPhoto');

// Generic document management routes (all 4 types: foto, ktp, ijazah, transkrip)
$app->post('/admin/participants/{id}/upload-doc/{type}', 'App\Controllers\ParticipantController@uploadDocument');
$app->delete('/admin/participants/{id}/delete-doc/{type}', 'App\Controllers\ParticipantController@deleteDocument');
$app->post('/admin/participants/{id}/rotate-doc/{type}', 'App\Controllers\ParticipantController@rotateDocument');
$app->post('/admin/participants/{id}/auto-download-docs', 'App\Controllers\ParticipantController@autoDownloadDocuments');

$app->get('/admin/participants/card/{id}', 'App\Controllers\ExamCardController@adminViewCard'); // NEW
$app->get('/admin/participants/form/{id}', 'App\Controllers\ExamCardController@adminViewForm'); // NEW

// Participant Area Routes
// Wait, AuthController@login was POST.
// We need a GET /dashboard for the Participant Dashboard view.
// And a GET /download-card

$app->get('/dashboard', function () {
    if (!isset($_SESSION['user'])) {
        response()->redirect('/');
        return;
    }
    // Load Participant Data
    $participant = \App\Models\Participant::find($_SESSION['user']);

    // Fetch Exam Room Info if assigned
    $examRoom = null;
    if (!empty($participant['ruang_ujian'])) {
        // Query manually since we don't have a direct relationship method yet
        $db = \App\Utils\Database::connection();
        $examRoom = $db->query("SELECT * FROM exam_rooms WHERE nama_ruang = ?")->bind($participant['ruang_ujian'])->fetchAssoc();
    }

    echo \App\Utils\View::render('participant.dashboard', [
        'participant' => $participant,
        'examRoom' => $examRoom
    ]);
});
// Exam Card
$app->get('/participant/exam-card', 'App\Controllers\ExamCardController@download');
$app->get('/participant/formulir', 'App\Controllers\ExamCardController@downloadFormulir'); // NEW
$app->get('/dummy-card', 'App\Controllers\ExamCardController@dummy');

$app->get('/download-card', 'App\Controllers\ExamCardController@download');

// Admin - Attendance List
$app->get('/admin/attendance-list', 'App\Controllers\ExamCardController@attendanceFilter');
$app->get('/admin/attendance-print', 'App\Controllers\ExamCardController@attendancePrint');

// Admin - CAT Schedule
$app->get('/admin/cat-schedule', 'App\Controllers\ExamCardController@catScheduleFilter');
$app->get('/admin/cat-schedule-print', 'App\Controllers\ExamCardController@catSchedulePrint');

$app->run();
