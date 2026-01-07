-- Migration: Add berkas fisik and hasil seleksi fields to participants table
-- Date: 2026-01-07

ALTER TABLE participants ADD COLUMN berkas_fisik_status TEXT DEFAULT 'belum_lengkap';
ALTER TABLE participants ADD COLUMN berkas_fisik_note TEXT;
ALTER TABLE participants ADD COLUMN hasil_seleksi TEXT DEFAULT 'belum_ada';
ALTER TABLE participants ADD COLUMN hasil_seleksi_note TEXT;
ALTER TABLE participants ADD COLUMN hasil_seleksi_date DATETIME;
