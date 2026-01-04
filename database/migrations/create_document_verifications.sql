-- Migration: Create document_verifications table
-- Date: 2026-01-03
-- Description: Physical document verification checklist for participants

CREATE TABLE IF NOT EXISTS document_verifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    participant_id INTEGER NOT NULL,
    
    -- Basic Documents (All applicants)
    formulir_pendaftaran INTEGER DEFAULT 0,
    formulir_pendaftaran_jumlah INTEGER DEFAULT 0,
    
    ijazah_s1_legalisir INTEGER DEFAULT 0,
    ijazah_s1_jumlah INTEGER DEFAULT 0,
    
    transkrip_s1_legalisir INTEGER DEFAULT 0,
    transkrip_s1_jumlah INTEGER DEFAULT 0,
    
    bukti_pembayaran INTEGER DEFAULT 0,
    bukti_pembayaran_jumlah INTEGER DEFAULT 0,
    
    surat_rekomendasi INTEGER DEFAULT 0,
    surat_rekomendasi_jumlah INTEGER DEFAULT 0,
    
    -- Additional for S3
    ijazah_s2_legalisir INTEGER DEFAULT 0,
    ijazah_s2_jumlah INTEGER DEFAULT 0,
    
    transkrip_s2_legalisir INTEGER DEFAULT 0,
    transkrip_s2_jumlah INTEGER DEFAULT 0,
    
    -- Verification Info
    status_verifikasi_fisik VARCHAR(20) DEFAULT 'pending', -- pending, lengkap, tidak_lengkap
    catatan_admin TEXT,
    verified_by INTEGER, -- admin user id
    verified_at DATETIME,
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE
);

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_doc_verif_participant ON document_verifications(participant_id);
CREATE INDEX IF NOT EXISTS idx_doc_verif_status ON document_verifications(status_verifikasi_fisik);
CREATE INDEX IF NOT EXISTS idx_doc_verif_verified_at ON document_verifications(verified_at);
