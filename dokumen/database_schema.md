# Database Schema (Updated)

> **Terakhir Diperbarui:** 6 Januari 2026  
> **Format**: SQLite  
> **File**: `storage/database.sqlite`

---

## 1. Core Tables

### `semesters`
Stores admissions periods.
- `id` (INTEGER, PK)
- `nama` (VARCHAR) e.g. "Ganjil 2025/2026"
- `kode` (VARCHAR) e.g. "20251"
- `periode` (INTEGER) e.g. 1, 2
- `is_active` (BOOLEAN)
- `tanggal_mulai`, `tanggal_selesai` (DATE)

### `users`
Admin and staff users.
- `id` (INTEGER, PK)
- `username` (VARCHAR)
- `password` (VARCHAR) Hash
- `role` (VARCHAR) Enum: 'superadmin', 'admin', 'upkh', 'tu', 'admin_prodi'
- `prodi_id` (VARCHAR, Nullable) For admin_prodi
- `nama_lengkap` (VARCHAR)

### `settings`
Application configuration.
- `id` (INTEGER, PK)
- `key` (VARCHAR)
- `value` (TEXT)

---

## 2. Participant Tables

### `participants`
Main applicant data.
- `id` (INTEGER, PK)
- `nomor_peserta` (VARCHAR)
- `no_billing` (VARCHAR)
- `nama_lengkap` (VARCHAR)
- `email` (VARCHAR)
- `no_hp` (VARCHAR)
- `semester_id` (INT) FK
- `kode_prodi` (VARCHAR)
- `nama_prodi` (VARCHAR)
- `jalur_masuk` (VARCHAR)
- **Status Fields**:
  - `status_berkas` ('pending', 'lulus', 'gagal')
  - `status_verifikasi_fisik` ('pending', 'lengkap', 'tidak_lengkap')
  - `status_pembayaran` (BOOL)
  - `status_tes_bidang` ('lulus', 'tidak_lulus')
  - `status_kelulusan_akhir` ('lulus', 'tidak_lulus')
- **Scores**:
  - `nilai_tpa_total` (DECIMAL)
  - `nilai_bidang_total` (DECIMAL)
- **Scheduling**:
  - `ruang_ujian` (VARCHAR)
  - `sesi_ujian` (VARCHAR)
  - `tanggal_ujian` (DATE)
  - `waktu_ujian` (TEXT)
- **Documents**: `photo_filename`, `ktp_filename`, `ijazah_filename`, `transkrip_filename`, `ijazah_s2_filename`, `transkrip_s2_filename`
- **Output**:
  - `sk_kelulusan` (VARCHAR)

### `document_verifications`
Physical document verification records.
- `id` (INTEGER, PK)
- `participant_id` (INT) FK
- `status_verifikasi_fisik` (VARCHAR)
- `catatan_admin` (TEXT)
- `verified_by` (INT) FK User
- `verified_at` (DATETIME)
- Document checklist columns (INTEGER): `foto`, `ktp`, `ijazah_s1_*`, `transkrip_s1_*`, `bukti_pembayaran*`, etc.

---

## 3. Exam & Scheduling

### `exam_rooms`
- `id` (INTEGER, PK)
- `nama_ruang` (VARCHAR)
- `fakultas` (VARCHAR)
- `kapasitas` (INT)
- `semester_id` (INT) FK

### `exam_sessions`
- `id` (INTEGER, PK)
- `nama_sesi` (VARCHAR)
- `tanggal` (DATE)
- `waktu_mulai`, `waktu_selesai` (TIME)
- `exam_room_id` (INT) FK
- `semester_id` (INT) FK
- `is_active` (BOOLEAN)

### `exam_attendances`
- `id` (INTEGER, PK)
- `participant_id` (INT) FK
- `semester_id` (INT) FK
- `is_present` (BOOLEAN)
- `check_in_time` (DATETIME)
- `notes` (TEXT)

---

## 4. Assessment Module

### `assessment_components`
- `id` (INTEGER, PK)
- `prodi_id` (VARCHAR, Nullable) If null -> Global
- `type` ('TPA', 'BIDANG')
- `nama_komponen` (VARCHAR)
- `bobot_persen` (FLOAT)

### `assessment_scores`
- `id` (INTEGER, PK)
- `participant_id` (INT) FK
- `component_id` (INT) FK
- `score` (FLOAT)
- `created_by` (INT) FK User

---

## 5. Graduation Module

### `prodi_quotas`
- `id` (INTEGER, PK)
- `semester_id` (INT) FK
- `kode_prodi` (VARCHAR)
- `daya_tampung` (INT)

### `prodi_configs`
- `id` (INTEGER, PK)
- `kode_prodi` (VARCHAR)
- `jenjang` (VARCHAR)
- `min_tpa` (FLOAT)
- `min_bidang` (FLOAT)

---

## 6. Email Module

### `email_configurations`
- `id` (INTEGER, PK)
- `driver` (TEXT) 'phpmailer' or 'gas'
- `smtp_host`, `smtp_port`, `smtp_username`, `smtp_password`, `smtp_encryption`
- `api_url` (TEXT) For GAS driver
- `from_email`, `from_name` (VARCHAR)
- `is_active` (BOOLEAN)

### `email_templates`
- `id` (INTEGER, PK)
- `name` (VARCHAR)
- `subject` (VARCHAR)
- `body` (TEXT)
- `description` (TEXT)

### `email_reminders`
- `id` (INTEGER, PK)
- `semester_id` (INT) FK
- `template_id` (INT) FK
- `subject`, `body` (TEXT)
- `recipient_count`, `sent_count`, `failed_count` (INT)
- `status` (VARCHAR)
- `sent_by` (INT) FK User
- `sent_at` (DATETIME)

### `email_logs`
- `id` (INTEGER, PK)
- `reminder_id` (INT) FK
- `participant_id` (INT) FK
- `email` (VARCHAR)
- `status` (VARCHAR)
- `error_message` (TEXT)
- `sent_at` (DATETIME)

---

## 7. System

### `update_logs`
- `id` (INTEGER, PK)
- `version_from`, `version_to` (VARCHAR)
- `status` (VARCHAR)
- `message` (TEXT)
- `performed_by` (INT) FK User
- `backup_file` (VARCHAR)
