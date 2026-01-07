<?php

namespace App\Models;

class DocumentVerification
{
    /**
     * Find verification record by ID
     */
    public static function find($id)
    {
        $db = \App\Utils\Database::connection();
        return $db->select('document_verifications')->where('id', $id)->first();
    }

    /**
     * Find verification record by participant ID
     */
    public static function findByParticipant($participantId)
    {
        $db = \App\Utils\Database::connection();
        return $db->select('document_verifications')->where('participant_id', $participantId)->first();
    }

    /**
     * Get all verifications with participant data
     */
    public static function getAllWithParticipants($semesterId = null, $statusFilter = null, $prodi = null)
    {
        $db = \App\Utils\Database::connection();

        $sql = "SELECT 
                    dv.id as verification_id,
                    dv.status_verifikasi_fisik,
                    dv.updated_at,
                    dv.bypass_verification,
                    p.id as participant_id,
                    p.nomor_peserta, 
                    p.nama_lengkap, 
                    p.email, 
                    p.nama_prodi, 
                    p.status_berkas, 
                    p.semester_id, 
                    s.nama as semester_nama
                FROM participants p
                LEFT JOIN document_verifications dv ON p.id = dv.participant_id
                LEFT JOIN semesters s ON p.semester_id = s.id
                WHERE p.status_berkas = 'lulus'";

        $params = [];

        if ($semesterId) {
            $sql .= " AND p.semester_id = ?";
            $params[] = $semesterId;
        }

        if ($prodi && $prodi !== 'all') {
            $sql .= " AND p.nama_prodi = ?";
            $params[] = $prodi;
        }

        if ($statusFilter && $statusFilter !== 'all') {
            if ($statusFilter == 'pending') {
                $sql .= " AND (dv.status_verifikasi_fisik IS NULL OR dv.status_verifikasi_fisik = 'pending')";
            } else {
                $sql .= " AND dv.status_verifikasi_fisik = ?";
                $params[] = $statusFilter;
            }
        }

        // Order by Name instead of updated_at since mostly created_at is null
        $sql .= " ORDER BY p.nama_lengkap ASC";

        return $db->query($sql)->bind(...$params)->all();
    }

    /**
     * Create new verification record
     */
    public static function create($participantId)
    {
        $db = \App\Utils\Database::connection();

        // Check if already exists
        $existing = self::findByParticipant($participantId);
        if ($existing) {
            return $existing;
        }

        $db->insert('document_verifications')->params([
            'participant_id' => $participantId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ])->execute();

        return self::findByParticipant($participantId);
    }

    /**
     * Update verification record
     */
    /**
     * Update verification record
     */
    public static function updateVerification($participantId, $data, $isS3 = false)
    {
        $db = \App\Utils\Database::connection();

        // Ensure record exists
        $verification = self::findByParticipant($participantId);
        if (!$verification) {
            self::create($participantId);
        }

        // Calculate status based on required documents
        $status = self::calculateStatus($data, $isS3);

        // Bypass overrides logic
        if (!empty($data['bypass_verification']) && $data['bypass_verification'] == 1) {
            $status = 'lengkap';
        }

        $db->update('document_verifications')
            ->params([
                'formulir_pendaftaran' => $data['formulir_pendaftaran'] ?? 0,
                'formulir_pendaftaran_jumlah' => $data['formulir_pendaftaran_jumlah'] ?? 0,
                'ijazah_s1_legalisir' => $data['ijazah_s1_legalisir'] ?? 0,
                'ijazah_s1_jumlah' => $data['ijazah_s1_jumlah'] ?? 0,
                'transkrip_s1_legalisir' => $data['transkrip_s1_legalisir'] ?? 0,
                'transkrip_s1_jumlah' => $data['transkrip_s1_jumlah'] ?? 0,
                'bukti_pembayaran' => $data['bukti_pembayaran'] ?? 0,
                'bukti_pembayaran_jumlah' => $data['bukti_pembayaran_jumlah'] ?? 0,
                'surat_rekomendasi' => $data['surat_rekomendasi'] ?? 0,
                'surat_rekomendasi_jumlah' => $data['surat_rekomendasi_jumlah'] ?? 0,
                'ijazah_s2_legalisir' => $data['ijazah_s2_legalisir'] ?? 0,
                'ijazah_s2_jumlah' => $data['ijazah_s2_jumlah'] ?? 0,
                'transkrip_s2_legalisir' => $data['transkrip_s2_legalisir'] ?? 0,
                'transkrip_s2_jumlah' => $data['transkrip_s2_jumlah'] ?? 0,
                'status_verifikasi_fisik' => $status,
                'catatan_admin' => $data['catatan_admin'] ?? '',
                'bypass_verification' => isset($data['bypass_verification']) ? ($data['bypass_verification'] ? 1 : 0) : 0,
                'verified_by' => $data['verified_by'] ?? $_SESSION['admin'] ?? null,
                'verified_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ])
            ->where('participant_id', $participantId)
            ->execute();

        // SYNC to participants table
        $db->update('participants')
            ->params(['status_verifikasi_fisik' => $status])
            ->where('id', $participantId)
            ->execute();

        return self::findByParticipant($participantId);
    }

    /**
     * Delete verification record by participant ID
     */
    public static function deleteByParticipant($participantId)
    {
        $db = \App\Utils\Database::connection();
        $db->query("DELETE FROM document_verifications WHERE participant_id = ?")->bind($participantId)->execute();

        // Also reset sync status in participants table
        return $db->update('participants')
            ->params(['status_verifikasi_fisik' => 'pending'])
            ->where('id', $participantId)
            ->execute();
    }

    /**
     * Check if participant can download card
     */
    public static function canDownloadCard($participantId)
    {
        $verification = self::findByParticipant($participantId);

        // If no verification record, assume blocked unless status_berkas is not lulus yet (logic might vary)
        if (!$verification) {
            return false;
        }

        // Allow if bypassed OR status is complete
        return !empty($verification['bypass_verification']) || $verification['status_verifikasi_fisik'] === 'lengkap';
    }

    /**
     * Calculate verification status based on document completeness
     */
    private static function calculateStatus($data, $isS3 = false)
    {
        // Required documents for all
        $required = [
            'formulir_pendaftaran',
            'ijazah_s1_legalisir',
            'transkrip_s1_legalisir',
            'bukti_pembayaran'
        ];

        if ($isS3) {
            $required[] = 'ijazah_s2_legalisir';
            $required[] = 'transkrip_s2_legalisir';
        }

        // Check if all required documents are checked
        foreach ($required as $doc) {
            if (empty($data[$doc]) || $data[$doc] == 0) {
                return 'tidak_lengkap';
            }
        }

        return 'lengkap';
    }

    /**
     * Get verification statistics
     */
    /**
     * Get verification statistics
     */
    /**
     * Get verification statistics
     */
    public static function getStatistics($semesterId = null)
    {
        $db = \App\Utils\Database::connection();

        // 0. Total Peserta (All): Lulus status berkas (usually base requirement)
        // User request: "total peserta (eligible+tidak eligible)"
        $totalAllSql = "SELECT COUNT(*) as total FROM participants WHERE status_berkas = 'lulus'";
        if ($semesterId) {
            $totalAllSql .= " AND semester_id = '$semesterId'";
        }
        $totalAllRes = $db->query($totalAllSql)->fetchAssoc();
        $totalAll = $totalAllRes['total'] ?? 0;

        // 1. Total Eligible: Participants with nomor_peserta
        $totalEligibleSql = "SELECT COUNT(*) as total FROM participants WHERE status_berkas = 'lulus' AND nomor_peserta IS NOT NULL AND nomor_peserta != ''";
        if ($semesterId) {
            $totalEligibleSql .= " AND semester_id = '$semesterId'";
        }
        $totalEligibleRes = $db->query($totalEligibleSql)->fetchAssoc();
        $totalEligible = $totalEligibleRes['total'] ?? 0;

        // 2. Lengkap: Eligible + Not Eligible users who have status 'lengkap'
        $lengkapSql = "SELECT COUNT(DISTINCT dv.participant_id) as total 
                       FROM document_verifications dv
                       JOIN participants p ON dv.participant_id = p.id
                       WHERE p.status_berkas = 'lulus' 
                       AND dv.status_verifikasi_fisik = 'lengkap'";

        if ($semesterId) {
            $lengkapSql .= " AND p.semester_id = '$semesterId'";
        }
        $lengkapRes = $db->query($lengkapSql)->fetchAssoc();
        $lengkap = $lengkapRes['total'] ?? 0;

        // 3. Tidak Lengkap: Eligible + Not Eligible users who have status 'tidak_lengkap'
        $tidakLengkapSql = "SELECT COUNT(DISTINCT dv.participant_id) as total 
                            FROM document_verifications dv
                            JOIN participants p ON dv.participant_id = p.id
                            WHERE p.status_berkas = 'lulus'
                            AND dv.status_verifikasi_fisik = 'tidak_lengkap'";

        if ($semesterId) {
            $tidakLengkapSql .= " AND p.semester_id = '$semesterId'";
        }
        $tidakLengkapRes = $db->query($tidakLengkapSql)->fetchAssoc();
        $tidakLengkap = $tidakLengkapRes['total'] ?? 0;

        // 4. Belum Verifikasi: Eligible ONLY.
        // Logic: Eligible users who have NO verification record OR have 'pending' status.
        $belumVerifikasiSql = "SELECT COUNT(DISTINCT p.id) as total
                               FROM participants p
                               LEFT JOIN document_verifications dv ON p.id = dv.participant_id
                               WHERE p.status_berkas = 'lulus' 
                               AND p.nomor_peserta IS NOT NULL AND p.nomor_peserta != ''
                               AND (dv.status_verifikasi_fisik IS NULL OR dv.status_verifikasi_fisik = 'pending')";
        if ($semesterId) {
            $belumVerifikasiSql .= " AND p.semester_id = '$semesterId'";
        }
        $belumVerifikasiRes = $db->query($belumVerifikasiSql)->fetchAssoc();
        $belumVerifikasi = $belumVerifikasiRes['total'] ?? 0;

        return [
            'total_all' => $totalAll,
            'total_eligible' => $totalEligible,
            'lengkap' => $lengkap,
            'tidak_lengkap' => $tidakLengkap,
            'belum_verifikasi' => $belumVerifikasi
        ];
    }

    /**
     * Check if participant is eligible for physical verification
     */
    public static function isEligible($participantId)
    {
        $participant = \App\Models\Participant::find($participantId);
        return $participant && $participant['status_berkas'] === 'lulus';
    }

    /**
     * Get incomplete documents list
     */
    public static function getIncompleteDocuments($participantId)
    {
        $verification = self::findByParticipant($participantId);
        if (!$verification) {
            return [];
        }

        $incomplete = [];

        $documents = [
            'formulir_pendaftaran' => 'Formulir Pendaftaran',
            'ijazah_s1_legalisir' => 'Ijazah S1 Legalisir',
            'transkrip_s1_legalisir' => 'Transkrip S1 Legalisir',
            'bukti_pembayaran' => 'Bukti Pembayaran',
            'ijazah_s2_legalisir' => 'Ijazah S2 Legalisir',
            'transkrip_s2_legalisir' => 'Transkrip S2 Legalisir',
        ];

        foreach ($documents as $field => $label) {
            if (empty($verification[$field])) {
                $incomplete[] = $label;
            }
        }

        return $incomplete;
    }
}
