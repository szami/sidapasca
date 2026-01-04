-- Email Reminder System Database Migrations

-- Table: email_configurations
CREATE TABLE IF NOT EXISTS email_configurations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    smtp_host VARCHAR(255) NOT NULL,
    smtp_port INTEGER NOT NULL DEFAULT 587,
    smtp_username VARCHAR(255) NOT NULL,
    smtp_password VARCHAR(255) NOT NULL,
    smtp_encryption VARCHAR(10) DEFAULT 'tls',
    from_email VARCHAR(255) NOT NULL,
    from_name VARCHAR(255) NOT NULL,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table: email_templates
CREATE TABLE IF NOT EXISTS email_templates (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    subject VARCHAR(500) NOT NULL,
    body TEXT NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table: email_reminders
CREATE TABLE IF NOT EXISTS email_reminders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    semester_id INTEGER NOT NULL,
    template_id INTEGER NOT NULL,
    subject VARCHAR(500) NOT NULL,
    body TEXT NOT NULL,
    recipient_count INTEGER DEFAULT 0,
    sent_count INTEGER DEFAULT 0,
    failed_count INTEGER DEFAULT 0,
    status VARCHAR(50) DEFAULT 'draft',
    sent_by INTEGER,
    sent_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (semester_id) REFERENCES semesters(id),
    FOREIGN KEY (template_id) REFERENCES email_templates(id),
    FOREIGN KEY (sent_by) REFERENCES admins(id)
);

-- Table: email_logs
CREATE TABLE IF NOT EXISTS email_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reminder_id INTEGER,
    participant_id INTEGER,
    email VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    error_message TEXT,
    sent_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reminder_id) REFERENCES email_reminders(id),
    FOREIGN KEY (participant_id) REFERENCES participants(id)
);

-- Insert default template examples
INSERT INTO email_templates (name, subject, body, description) VALUES
('Welcome Template', 'Selamat Datang di PMB PPs ULM', '<p>Yth. {nama},</p><p>Selamat! Anda telah terdaftar di Program Pascasarjana ULM dengan nomor peserta <strong>{nomor_peserta}</strong>.</p><p>Program Studi: <strong>{prodi}</strong></p><p>Semester: <strong>{semester}</strong></p><p>Silakan melengkapi berkas dan melakukan pembayaran.</p>', 'Template sambutan untuk peserta baru'),
('Payment Reminder', 'Reminder Pembayaran PMB', '<p>Yth. {nama},</p><p>Kami mengingatkan Anda untuk segera menyelesaikan pembayaran pendaftaran.</p><p>Nomor Peserta: <strong>{nomor_peserta}</strong></p><p>Prodi: <strong>{prodi}</strong></p><p>Terima kasih.</p>', 'Reminder untuk peserta yang belum bayar'),
('Document Verification', 'Reminder Verifikasi Berkas Fisik', '<p>Yth. {nama},</p><p>Silakan melakukan verifikasi berkas fisik di kampus.</p><p>Nomor Peserta: <strong>{nomor_peserta}</strong></p><p>Jadwal: Senin-Jumat, 08:00-15:00</p>', 'Reminder verifikasi fisik');
