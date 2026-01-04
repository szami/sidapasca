<?php

session_start();

require __DIR__ . '/vendor/autoload.php';

// Load Env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Set Timezone to WITA (Waktu Indonesia Tengah / UTC+8)
date_default_timezone_set('Asia/Makassar');

// Config
require __DIR__ . '/config/db.php';

// Init Leaf
$app = new Leaf\App();

// Routes
$app->get('/', 'App\Controllers\HomeController@index');
$app->get('/login', 'App\Controllers\AuthController@loginView');
$app->post('/login', 'App\Controllers\AuthController@login');
$app->get('/logout', 'App\Controllers\AuthController@logout');

// Admin Routes
$app->get('/admin', 'App\Controllers\AdminController@dashboard');
$app->post('/admin/login', 'App\Controllers\AuthController@adminLogin');
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
$app->get('/admin/master/rooms', 'App\Controllers\ExamRoomController@index');
$app->get('/admin/master/rooms/create', 'App\Controllers\ExamRoomController@create');
$app->post('/admin/master/rooms/store', 'App\Controllers\ExamRoomController@store');
$app->get('/admin/master/rooms/edit/{id}', 'App\Controllers\ExamRoomController@edit');
$app->post('/admin/master/rooms/update/{id}', 'App\Controllers\ExamRoomController@update');
$app->get('/admin/master/rooms/delete/{id}', 'App\Controllers\ExamRoomController@destroy');

// Master Sesi Ujian
$app->get('/admin/master/sessions', 'App\Controllers\ExamSessionController@index');
$app->get('/admin/master/sessions/create', 'App\Controllers\ExamSessionController@create');
$app->post('/admin/master/sessions/store', 'App\Controllers\ExamSessionController@store');
$app->get('/admin/master/sessions/edit/{id}', 'App\Controllers\ExamSessionController@edit');
$app->post('/admin/master/sessions/update/{id}', 'App\Controllers\ExamSessionController@update');
$app->get('/admin/master/sessions/delete/{id}', 'App\Controllers\ExamSessionController@destroy');

// Scheduler
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
$app->get('/admin/verification/physical', 'App\Controllers\DocumentVerificationController@index');
$app->get('/admin/verification/physical/{id}', 'App\Controllers\DocumentVerificationController@verify');
$app->post('/admin/verification/physical/{id}/save', 'App\Controllers\DocumentVerificationController@save');
$app->post('/admin/verification/physical/{id}/reset', 'App\Controllers\DocumentVerificationController@reset');
$app->get('/admin/verification/physical/import/template', 'App\Controllers\DocumentVerificationController@downloadTemplate');
$app->post('/admin/verification/physical/import', 'App\Controllers\DocumentVerificationController@import');

// Email Reminder Routes
$app->get('/admin/email/config', 'App\Controllers\EmailConfigController@index');
$app->post('/admin/email/config/save', 'App\Controllers\EmailConfigController@save');
$app->post('/admin/email/config/test', 'App\Controllers\EmailConfigController@testConnection');

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
$app->get('/admin/participants', 'App\Controllers\ParticipantController@index');
$app->get('/admin/participants/view/{id}', 'App\Controllers\ParticipantController@view');
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
    echo \App\Utils\View::render('participant.dashboard', ['participant' => $participant]);
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
