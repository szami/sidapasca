# Changelog

All notable changes to the SIDA Pasca ULM project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.2.0] - 2026-01-06

### 🎓 Added - Assessment & Graduation System
- **TPA & Bidang Assessments**:
  - Implemented TPA Threshold settings (S2: 450, S3: 500).
  - Added "Keputusan Akhir" (L/TL/T) with manual override capabilities.
  - Integration of Bidang scores with Admin Prodi specific access.
  - Final decision logic based on Physical Verification, TPA Threshold, and Bidang Result.

- **Graduation Board Refinement**:
  - Deprecated legacy "Graduation Board" in favor of "Input Nilai & Keputusan".
  - Implemented bulk decision processing (Lulus/Tidak Lulus).
  - Smart logic to auto-recommend status based on system criteria.

- **Admin Menu Reorganization**:
  - Completely reorganized sidebar menu for better UX.
  - Grouped by workflow: Admisi & Peserta, Manajemen Ujian, Penilaian & Kelulusan, Master Data, System Tools.
  - Optimized role-based visibility (specifically for Admin Prodi, UPKH, and TU).
  - Renamed "Input Nilai & Keputusan" to "Proses Nilai" for brevity.

- **Project Cleanup**:
  - Removed unused files and dead code (`migrate_tpa.php`, `board.php`).
  - Streamlined controller logic.

### 🔧 Fixed
- **Graduation Quotas**: Fixed bug where quotas were not saving due to missing form ID.
- **Menu Visibility**: Fixed "Download Berkas" visibility issues for UPKH/TU.

---

## [1.1.0] - 2026-01-05

### 🎉 Added - Role-Based Access Control (RBAC)

Implemented comprehensive RBAC system with 5 distinct user roles.

**Roles Implemented:**
| Role | Access Level | Description |
|------|--------------|-------------|
| **Superadmin** | Full Access | All features, user management, settings |
| **Admin** | Standard Admin | CRUD, import/export, settings, verification |
| **UPKH** | Document Verifier | Participant data, document verification |
| **TU** | Scheduling & Reports | Exam scheduling, attendance, reports |
| **Admin Prodi** | Program-Specific | Own program data only |

**Features:**
- **RoleHelper Utility** (`app/Utils/RoleHelper.php`):
  - Role constants and detection methods
  - Permission-based access methods (canEditParticipant, canManageSchedule, etc.)
  - UI helper methods (getRoleDisplayName, getRoleBadgeClass)
  - Prodi-based filtering support for Admin Prodi role
  
- **Dynamic Sidebar Menu**:
  - Role-based menu item visibility
  - User info panel with role badge
  - Organized menu sections by role permissions
  
- **Controller Restrictions**:
  - `DocumentVerificationController`: Superadmin, Admin, UPKH only
  - `ExamSchedulerController`: Superadmin, Admin, TU only
  - `ParticipantController`: Role-based edit permissions
  - `ImportController`: Superadmin, Admin only
  - `SettingsController`: Superadmin, Admin only
  - `UserController`: Superadmin only
  
- **Dashboard Customization**:
  - Role-specific data filtering
  - Admin Prodi sees only their program data
  - Full stats for other roles

**Database Changes:**
- Added `role` column to `users` table (superadmin, admin, upkh, tu, admin_prodi)
- Added `prodi_id` column to `users` table for Admin Prodi role
- Seeded test accounts for all 5 roles

**Test Accounts:**
| Username | Password | Role |
|----------|----------|------|
| `admin` | `admin123` | superadmin |
| `operator` | `operator123` | admin |
| `upkh` | `upkh123` | upkh |
| `tu` | `tu123` | tu |
| `prodi_test` | `prodi123` | admin_prodi |

---

## [1.0.4] - 2026-01-04

### 🚀 Added - Google Apps Script Email Driver
- **Alternative Email Sending Method**:
  - Implementation of GAS (Google Apps Script) Webhook driver for sending emails.
  - Useful for environments where standard SMTP ports are blocked or restricted.
  - Supports custom "From Name" via payload parameter.
  
- **Configuration UI**:
  - Added "Driver" selection (SMTP vs GAS) in Email Configuration.
  - Conditional UI fields based on selected driver.
  - moved "From Name" and "From Email" fields to global scope (visible for both drivers).

### 🔧 Fixed & Improved
- **Local SSL Compatibility**:
  - Bypassed SSL certificate verification (`CURLOPT_SSL_VERIFYPEER = false`) for GAS requests to resolve local development environment issues (Laragon).
  
- **Reminder Filtering Logic**:
  - Improved "Lulus belum bayar" filter to automatically exclude participants who already not have `nomor_peserta`.
  - Ensures participants with exam numbers are treated as "Paid/Verified" regardless of payment status flag.

---

## [1.0.3-p2] - 2026-01-04

### 🐛 Fixed - Hosting Compatibility
- **Auto Update Safety**:
  - Handled `Call to undefined function exec()` error on shared hosting where `exec` is disabled/removed.
  - Added safety checks in `GitHelper` to verify if `exec` exists before calling.
  - Usage of `exec` is now properly namespaced as `\exec` to prevent namespace resolution errors.

## [1.0.3-p1] - 2026-01-04

### 🐛 Fixed - Linux Compatibility
- **Directory Structure**:
  - Renamed `app/utils` to `app/Utils`.
  - Renamed `app/controllers` to `app/Controllers`.
  - Renamed `app/models` to `app/Models`.
  - Fixed case-sensitivity autoloading issues on Linux servers.

## [1.0.3] - 2026-01-04

### 🔄 Improved & Refined
- **Excel Export Logic**:
  - Implemented smart column filtering for S2/S3.
  - "Education S2" and "Documents S2" columns are now hidden for S2 study programs exports.
  - Columns remain visible for S3 study programs or Superadmin (all data) exports.
  
- **Admin Sidebar UI**:
  - Cleaned up navigation menu organization.
  - Moved "Tools" section to be exclusively for Superadmin/Admin (hidden for Admin Prodi).
  - Improved menu hierarchy and readability.

---

## [1.0.2] - 2026-01-04

### 🔧 Fixed & Enhanced - PDF Generation
- **Registration Form Optimization**:
  - Implemented single-page layout for efficiency.
  - Removed unwanted borders and gray background for a cleaner "paper-like" look.
  - Fixed photo cropping issues by allowing dynamic page sizing.
  - Reduced font sizes and padding for compact presentation.
  
- **Dynamic Letterhead**:
  - Implemented `[kop_surat]` placeholder support in Exam Cards.
  - Added logic to fetch letterhead from `exam_card_letterhead` or `exam_form_letterhead` settings.
  - Fallback to default letterhead if setting is empty.
  
- **Photo Path Resolution**:
  - Fixed incorrect path depth in `ExamCardGenerator` ensuring photos display correctly.

### 📊 Enhanced - Excel Recapitulation
- **Expanded Data Points**:
  - Added comprehensive biodata columns: Place/Date of Birth, Full Address, Contact Info, Job Details.
  - Added full education history (S1 & S2): Year In/Out, University, Faculty, Program, GPA, Title.
  
- **Format Improvements**:
  - Formatted "Date of Birth" to `dd-mm-yyyy`.
  - Removed "Status Pemberkasan" column as requested.
  - Retained and styled valid documentation checklist.

### 🐛 Fixed
- **Exam Card**: Fixed `[kop_surat]` not being replaced in the template parser.
- **Registration Form**: Fixed `page` CSS causing overflow and cutoff in DOMPDF.

---

## [1.0.1] - 2026-01-04

### 🎉 Added - Email Reminder System

Implemented comprehensive email notification and reminder system for participant communication.

**Features:**
- **Email Configuration Management**: SMTP settings with connection testing
  - Host, port, username, password configuration
  - Encryption support (SSL/TLS)
  - From email and name customization
  - Test email functionality
  
- **Email Template Builder**: Dynamic template management
  - Rich text editor (Summernote) for email content
  - Subject line customization
  - Dynamic placeholders: `{nama}`, `{nomor_peserta}`, `{prodi}`, `{email}`, `{no_hp}`
  - Template preview functionality
  
- **Mass Reminder System**: Send bulk emails to participants
  - Filter by semester, program, and status
  - Preview recipient list before sending
  - Batch sending with queue management
  - Success/failure tracking per recipient
  
- **Reminder History**: Complete audit trail
  - View all sent reminders with timestamps
  - Detailed recipient logs per reminder
  - Delivery status tracking
  - Sent by admin tracking

**Database Tables Added:**
- `email_configurations` - SMTP settings storage
- `email_templates` - Reusable email templates
- `email_reminders` - Reminder batch records
- `email_logs` - Individual email delivery logs

**Controllers Added:**
- `EmailConfigController.php` - SMTP configuration management
- `EmailTemplateController.php` - Template CRUD operations
- `EmailReminderController.php` - Send and track reminders

**Routes Added:**
- `/admin/email/config` - Email configuration
- `/admin/email/templates` - Template management
- `/admin/email/reminders` - Reminder history
- `/admin/email/reminders/send` - Send new reminder

---

### ✅ Added - Document Verification Module

Implemented physical document verification workflow for applicant document validation.

**Features:**
- **Physical Verification Workflow**: Track document completeness
  - Photo verification status
  - KTP verification status
  - Ijazah S1 verification status
  - Transkrip S1 verification status
  - Ijazah S2 verification status (for S3 applicants)
  - Transkrip S2 verification status (for S3 applicants)
  
- **Verification Management**: Admin interface for verification
  - List all participants with verification status
  - Individual verification detail page
  - Mark documents as verified/unverified
  - Save and reset functionality
  - Automatic progress tracking (X of 4/6 verified)
  
- **Bypass Verification**: Special cases handling
  - Allow download even without complete verification
  - Useful for urgent cases or special circumstances
  - Admin-controlled bypass flag
  
- **Excel Import**: Template-based bulk verification
  - Download Excel template with participant list
  - Import verification status from Excel
  - Batch update verification records
  
- **Integration with Exam Card**: Download control
  - Participants must complete verification to download exam card
  - Clear error messages when verification incomplete
  - Bypass option for special cases

**Database Tables Added:**
- `document_verifications` - Track verification status per participant
  - All 6 document types with boolean flags
  - `can_download_card` computed field
  - `verified_count` tracking
  - Timestamps for audit trail

**Controller Added:**
- `DocumentVerificationController.php` - Complete verification workflow

**Routes Added:**
- `/admin/verification/physical` - Verification list
- `/admin/verification/physical/{id}` - Verification detail/edit
- `/admin/verification/physical/import` - Excel import

---

### 📅 Added - CAT Schedule Feature

New CAT (Computer Assisted Test) exam schedule printing feature.

**Features:**
- Schedule generation for posting at exam room entrances
- Filter by exam session and room
- Print-ready A4 format with letterhead
- 4-column table: NO, NOMOR PESERTA, NAMA PESERTA, PROGRAM STUDI
- No signature fields (unlike attendance list)

**Use Case**: Print and post schedules outside exam rooms for participant information

**Controllers Modified:**
- `ExamCardController.php` - Added `catScheduleFilter()` and `catSchedulePrint()` methods

**Views Created:**
- `cat_schedule_filter.php` - Filter page with session/room selection
- `cat_schedule.php` - Printable schedule template

**Routes Added:**
- `/admin/cat-schedule` - Filter and generate schedule
- `/admin/cat-schedule-print` - Printable schedule output

**Menu Added:**
- "Jadwal CAT" menu item in OPERASIONAL section (before Daftar Hadir)

---

### 🔧 Fixed - Prodi Filter Showing All Semesters

Fixed bug where study program (prodi) filter dropdowns showed programs from ALL semesters instead of only the active semester.

**Issue**: 
- Prodi dropdowns in scheduler and participant list showed old/inactive programs
- Caused confusion and potential data inconsistency
- Users might select programs not available in current semester

**Solution**: Updated prodi filter queries to always use active semester

**Files Modified:**
- `ExamSchedulerController.php` (line 40-47): Added `WHERE semester_id = ?` to prodi query with active semester binding
- `ParticipantController.php` (line 79-89): Always use active semester for prodi dropdown regardless of main filter

**Impact**: 
- Cleaner, more relevant dropdown options
- Prevents selection of inactive programs
- Improves data integrity

---

### 🗂️ Changed - Project Structure Reorganization

Improved project organization by moving migration scripts to dedicated folder.

**Changes:**
- Created `database/migrations-manual/` directory
- Moved migration scripts from root to new location:
  - `migrate_bypass.php`
  - `migrate_document_verifications.php`
  - `migrate_email_reminder.php`

**Benefits:**
- Cleaner root directory
- Better organization following Laravel/PHP conventions
- Migration scripts preserved for reference
- Professional project structure

---

### 📊 Database Schema Updates

**New Tables (5 total):**
1. `email_configurations` - SMTP settings
2. `email_templates` - Email templates with placeholders
3. `email_reminders` - Reminder batch records
4. `email_logs` - Individual delivery tracking
5. `document_verifications` - Physical document verification status

**Total Tables**: 13 (was 8)

---

### 🔒 Security Enhancements

- Email reminder system uses PHPMailer with secure SMTP
- Document verification prevents unauthorized exam card downloads
- Proper authentication checks on all new routes
- Input sanitization on template editor

---

## [Unreleased] - 2026-01-03

### 📊 Added - Database Documentation
- Created comprehensive database ERD documentation (`database_erd.md`)
- Added visual Mermaid diagrams showing all table relationships
- Documented all 8 core tables with complete field descriptions
- Added business rules and constraints documentation
- Included database statistics and metadata

### 📝 Enhanced - Technical Documentation
- Updated `agents.md` with latest database schema (58+ participant fields)
- Added quick ERD reference section with embedded Mermaid diagram
- Enhanced database architecture section with overview and visual aids
- Added `role` field documentation to `users` table
- Documented comprehensive participant fields:
  - Extended personal data (alamat_ktp, kecamatan, kota, provinsi, kode_pos)
  - Employment information (pekerjaan, instansi_pekerjaan, alamat_pekerjaan, telpon_pekerjaan)
  - Payment tracking (transaction_id, payment_date, payment_method)
  - Exam scheduling (ruang_ujian, tanggal_ujian, waktu_ujian, sesi_ujian)
  - Enhanced academic history (S1 and S2 with fakultas, tahun_masuk, tahun_tamat, gelar)
  - Additional documents (ijazah_s2_filename, transkrip_s2_filename)

### 🔒 Enhanced - Security Documentation
- Added "Production Security" troubleshooting section
- Documented debug file removal best practices
- Added security checklist for production deployment
- Enhanced file permission guidelines
- Added storage directory security recommendations

### 📚 Enhanced - Developer Resources
- Added "Additional Resources" section to `agents.md`
- Linked comprehensive ERD documentation
- Referenced migration and seed files
- Updated developer onboarding checklist
- Added ERD review requirement for new developers

### 🗂️ Updated - Project Documentation
- Updated `README.md` with comprehensive feature overview
- Added visual badges (version, PHP, database, status)
- Enhanced technology stack section with detailed tables
- Added complete project structure documentation
- Included ERD diagram in README
- Added security features and checklist
- Enhanced troubleshooting guide
- Added development roadmap section
- Professional formatting with sections and visual hierarchy

### 🧹 Removed - Debug Files
Removed all debug and testing files from web root for production security:
- `add_payment_details.php` - Payment details testing script
- `check_all_columns.php` - Column verification utility
- `check_payment_cols.php` - Payment columns checker
- `check_payment_import.php` - Import testing script
- `check_users.php` - User table verification
- `db_update_roles.php` - Role migration script
- `fix_role_column.php` - Column fix utility
- `rollback_payment_cols.php` - Rollback script
- `get_schema.php` - Schema extraction utility

**Security Impact:** Eliminated potential information disclosure vulnerabilities

### 📋 Added - Project Management
- Created `CHANGELOG.md` for version tracking
- Added implementation plan documentation
- Created comprehensive walkthrough of all changes
- Updated task tracking with completed items

### 🔄 Changed - Metadata
- Updated last modified timestamp to "2026-01-03 18:38 WITA"
- Added database schema version tracking (v1.0 Production)
- Enhanced project status indicators

---

## [0.9.0] - 2026-01-02

### Added - Core Features
- **Admin Dashboard** with comprehensive statistics
  - Total participants by semester
  - Status breakdown (Lulus/Gagal/Pending)
  - Payment statistics
  - Program distribution charts (Chart.js)
  
- **Participant Management Module**
  - Complete CRUD operations
  - Dynamic filtering (Formulir Masuk, Lulus Berkas, Gagal Berkas, Peserta Ujian)
  - Advanced search functionality
  - Document management (upload, view, delete)
  - Photo management with preview
  - Comprehensive personal data forms
  
- **Excel Import System**
  - Bulk participant registration
  - Status update imports (Lulus/Gagal)
  - Payment data imports
  - Multi-format support (.xls, .xlsx)
  - Import modes (Full, Update Only, Insert Only)
  - Error handling and reporting
  
- **Exam Management**
  - Exam rooms master data
  - Exam sessions scheduling
  - Participant assignment to sessions
  - Attendance tracking system
  - Attendance reports generation
  
- **Exam Card Generation**
  - PDF generation using DOMPDF
  - Customizable templates
  - Dynamic data population
  - Download for qualified participants
  - Template management via settings
  
- **Settings & Configuration**
  - Application branding (name, logo, favicon)
  - Database backup and restore
  - Semester management
  - Active semester selection
  - Database cleanup utilities
  
- **Participant Portal**
  - Secure login (email + date of birth)
  - Status checking
  - Exam card download
  - Math-based captcha protection

### Added - Database Schema
- Created `users` table for admin authentication
- Created `semesters` table for academic period management
- Created `participants` table with comprehensive fields:
  - Personal information
  - Contact details
  - Educational background (S1 and S2)
  - Document tracking
  - Status management
- Created `exam_rooms` table for exam venue management
- Created `exam_sessions` table for scheduling
- Created `exam_attendances` table for tracking
- Created `settings` table for system configuration

### Added - Security Features
- bcrypt password hashing for admin accounts
- Session-based authentication
- SQL injection protection (prepared statements)
- Input sanitization for Excel imports
- Captcha protection for participant login
- File upload validation and sanitization

### Added - Technical Infrastructure
- Leaf PHP v3 framework integration
- Blade templating engine
- Tailwind CSS v3 via CDN
- AdminLTE 3 theme integration
- Chart.js for data visualization
- PhpSpreadsheet for Excel processing
- DOMPDF for PDF generation
- SQLite database with MySQL fallback support

### Added - Development Tools
- Migration scripts (`app/database/migrations/migrate.php`)
- Seed scripts (`app/database/migrations/seed.php`)
- Database utility helpers
- View rendering helpers
- SimpleCaptcha utility

---

## Version History Summary

| Version | Date | Type | Description |
|---------|------|------|-------------|
| **1.0.0** | 2026-01-03 | Release | First production release |
| **Unreleased** | 2026-01-03 | Update | Documentation & security enhancements |
| **0.9.0** | 2026-01-02 | Beta | Initial feature implementation |

---

## Categories

### 🎉 Added
New features and functionality

### 🔄 Changed
Changes to existing functionality

### 🗑️ Deprecated
Features that will be removed in future versions

### 🧹 Removed
Features that have been removed

### 🔧 Fixed
Bug fixes

### 🔒 Security
Security improvements and vulnerability fixes

### 📝 Documentation
Documentation changes and improvements

### 📊 Database
Database schema changes

---

## Upgrade Notes

### From 0.9.0 to 1.0.0
- No database migration required (schema unchanged)
- Remove debug files from root directory (done automatically)
- Review updated documentation in `agents.md`
- Check new ERD documentation for database understanding

---

## Future Roadmap

### v1.1.0 (Planned)
- [ ] Role-Based Access Control (RBAC)
- [ ] Activity Audit Logs
- [ ] Enhanced user management
- [ ] Operator role with limited permissions

### v1.2.0 (Planned)
- [ ] Email notification system
- [ ] Automated status notifications
- [ ] Payment reminders
- [ ] Exam card delivery via email

### v1.3.0 (Planned)
- [ ] Advanced reporting module
- [ ] Export to multiple formats (PDF, Excel, CSV)
- [ ] Custom report builder
- [ ] Statistical analysis tools

### v2.0.0 (Future)
- [ ] RESTful API implementation
- [ ] Mobile application support
- [ ] Multi-language support (ID/EN)
- [ ] Advanced analytics dashboard
- [ ] QR Code integration for exam cards

---

## Migration Guide

### Database Migrations
All database changes are managed through migration scripts in `app/database/migrations/`.

**Running Migrations:**
```bash
php app/database/migrations/migrate.php
```

**Running Seeds:**
```bash
php app/database/migrations/seed.php
```

### Backup Before Upgrading
Always backup your database before running migrations:
```bash
# Via Admin Panel: Settings → Database Tools → Backup
# Or manually copy the file:
cp storage/database.sqlite storage/database.backup.sqlite
```

---

## Breaking Changes

### v1.0.0
No breaking changes from v0.9.0

---

## Contributors

### Development Team
- **Program Pascasarjana ULM** - Core development and maintenance
- **AI Assistant** - Documentation and code optimization

---

## Support

For questions, issues, or feature requests:
- Email: shabirin.mukhlish@ulm.ac.id
- Database: See ERD documentation for schema details

---

**Changelog Maintained By:** Program Pascasarjana ULM Development Team  
**Last Updated:** 04 Januari 2026 00:06 WITA  
**Project:** SIDA Pasca ULM - Sistem Informasi & Data Admisi Pascasarjana ULM
