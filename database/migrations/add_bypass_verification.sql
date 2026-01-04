-- Add bypass_verification column to document_verifications
-- Date: 2026-01-03
-- Description: Allow admin to bypass verification for specific participants

ALTER TABLE document_verifications ADD COLUMN bypass_verification INTEGER DEFAULT 0;
