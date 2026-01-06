# Database Documentation

## ER Diagram

```mermaid
erDiagram
    users {
        int id PK
        string username
        string role
        string prodi_id
    }

    semesters {
        int id PK
        string kode
        string nama
        boolean is_active
        int periode
    }

    participants {
        int id PK
        string nomor_peserta
        string no_billing
        string nama_lengkap
        string email
        string kode_prodi
        string nama_prodi
        int semester_id FK
        string photo_filename
        string ktp_filename
        string ijazah_filename
        string transkrip_filename
        string status_berkas
        boolean status_pembayaran
    }

    exam_rooms {
        int id PK
        string nama_ruang
        int kapasitas
        string ip_address
    }

    exam_sessions {
        int id PK
        int exam_room_id FK
        string nama_sesi
        date tanggal
        string waktu_mulai
        string waktu_selesai
        boolean is_active
        int semester_id FK
    }

    exam_attendances {
        int id PK
        int participant_id FK
        int semester_id FK
        boolean is_present
    }

    document_verifications {
        int id PK
        int participant_id FK
        string status_verifikasi_fisik
        int verified_by FK
        datetime verified_at
        text catatan_admin
    }

    email_configurations {
        int id PK
        string smtp_host
        string from_email
        boolean is_active
        string driver
    }

    email_templates {
        int id PK
        string name
        string subject
        text body
    }

    email_reminders {
        int id PK
        int semester_id FK
        int template_id FK
        string subject
        int sent_by FK
        string status
    }

    email_logs {
        int id PK
        int reminder_id FK
        int participant_id FK
        string email
        string status
        text error_message
    }

    assessment_components {
        int id PK
        string prodi_id
        string type
        string nama_komponen
        int bobot_persen
    }

    assessment_scores {
        int id PK
        int participant_id FK
        int component_id FK
        decimal score
    }

    prodi_configs {
        string kode_prodi PK
        string jenjang
        decimal min_tpa
        decimal min_bidang
    }

    prodi_quotas {
        int id PK
        int semester_id FK
        string kode_prodi
        int daya_tampung
    }

    participants }|--|| semesters : "belongs to"
    exam_sessions }|--|| exam_rooms : "located in"
    exam_sessions }|--|| semesters : "part of"
    exam_attendances }|--|| participants : "attends"
    exam_attendances }|--|| semesters : "in"
    document_verifications }|--|| participants : "verifies"
    document_verifications }|--|| users : "verified by"
    email_reminders }|--|| semesters : "for"
    email_reminders }|--|| email_templates : "uses"
    email_reminders }|--|| users : "sent by"
    email_logs }|--|| email_reminders : "log of"
    email_logs }|--|| participants : "sent to"
    assessment_scores }|--|| participants : "score for"
    assessment_scores }|--|| assessment_components : "component of"
    prodi_quotas }|--|| semesters : "quota for"
```

## Tables Overview

### Core Tables
- **`users`**: Administrators and operators.
- **`settings`**: Key-value store for application configuration.
- **`semesters`**: Academic periods (e.g., "20241").

### Participant Data
- **`participants`**: Main table for applicant data, including personal info, previous education, document filenames, and statuses.
- **`document_verifications`**: Physical document verification status and checklists.

### Examination (CAT)
- **`exam_rooms`**: Physical rooms/labs for exams.
- **`exam_sessions`**: Scheduled exam sessions in specific rooms.
- **`exam_attendances`**: Records participant attendance in exams.

### Assessment (Grading)
- **`assessment_components`**: Defined scoring components per program study (TPA, Interview, etc.).
- **`assessment_scores`**: Actual scores for each participant per component.
- **`prodi_configs`**: Passing grade thresholds per program study.
- **`prodi_quotas`**: Capacity (daya tampung) per program study per semester.

### Email System
- **`email_configurations`**: SMTP and driver settings.
- **`email_templates`**: HTML email templates.
- **`email_reminders`**: Bulk email campaigns/reminders.
- **`email_logs`**: Detailed delivery logs for each recipient.

### System
- **`update_logs`**: Records of system updates and migrations.
