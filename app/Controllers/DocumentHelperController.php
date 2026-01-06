<?php

namespace App\Controllers;

use App\Models\Participant;
use Leaf\Http\Request;

class DocumentHelperController
{
    public function index()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin');
            exit;
        }

        $db = \App\Utils\Database::connection();

        // Get Semesters for filter
        $semesters = $db->query("SELECT * FROM semesters ORDER BY created_at DESC")->fetchAll(\PDO::FETCH_ASSOC);

        // Get Active Semester
        $activeSemester = \App\Models\Semester::getActive();

        // Initial Prodis (for Active Semester or All)
        $initialProdis = [];
        if ($activeSemester) {
            $prodisRaw = $db->query("SELECT DISTINCT nama_prodi FROM participants WHERE semester_id = ? AND nama_prodi IS NOT NULL AND nama_prodi != '' ORDER BY nama_prodi")->bind($activeSemester['id'])->fetchAll(\PDO::FETCH_ASSOC);
            $initialProdis = array_column($prodisRaw, 'nama_prodi');
        } else {
            $prodisRaw = $db->query("SELECT DISTINCT nama_prodi FROM participants WHERE nama_prodi IS NOT NULL AND nama_prodi != '' ORDER BY nama_prodi")->fetchAll(\PDO::FETCH_ASSOC);
            $initialProdis = array_column($prodisRaw, 'nama_prodi');
        }

        echo \App\Utils\View::render('admin.document_helper.index', [
            'semesters' => $semesters,
            'activeSemester' => $activeSemester,
            'prodis' => $initialProdis
        ]);
    }

    public function apiData()
    {
        if (!isset($_SESSION['admin'])) {
            response()->json(['data' => []], 401);
            return;
        }

        $db = \App\Utils\Database::connection();

        // DataTables Parameters
        $draw = (int) Request::get('draw');
        $start = (int) Request::get('start');
        $length = (int) Request::get('length');
        $search = Request::get('search')['value'] ?? '';

        // Filters
        $semesterId = Request::get('semester_id');
        $prodi = Request::get('prodi');

        // Base Where
        $where = ["1=1"];

        // --- 1. Custom Filters ---
        if (!empty($semesterId) && $semesterId !== 'all') {
            $semIdInt = (int) $semesterId;
            $where[] = "p.semester_id = $semIdInt";
        }

        if (!empty($prodi) && $prodi !== 'all') {
            $prodiSafe = str_replace("'", "''", $prodi);
            $where[] = "p.nama_prodi = '$prodiSafe'";
        }

        // --- 2. Global Search ---
        if (!empty($search)) {
            $searchSafe = str_replace("'", "''", $search);
            $searchClause = "(p.nama_lengkap LIKE '%$searchSafe%' OR p.email LIKE '%$searchSafe%' OR p.nomor_peserta LIKE '%$searchSafe%')";
            $where[] = $searchClause;
        }

        $whereSql = implode(' AND ', $where);

        try {
            // --- 3. Counts ---
            // Total records (ignoring filters for standard SSP, but usually okay to match filter context if persistent)
            $totalStmt = $db->query("SELECT COUNT(*) as cx FROM participants")->fetchAll(\PDO::FETCH_ASSOC);
            $recordsTotal = $totalStmt[0]['cx'] ?? 0;

            // Filtered count
            $filteredStmt = $db->query("SELECT COUNT(*) as cx 
                                        FROM participants p 
                                        LEFT JOIN semesters s ON p.semester_id = s.id 
                                        WHERE $whereSql")->fetchAll(\PDO::FETCH_ASSOC);
            $recordsFiltered = $filteredStmt[0]['cx'] ?? 0;

            // --- 4. Data Query ---
            // Default Limit if not set or invalid (length=-1 means ALL)
            $noLimit = ($length == -1);
            if ($length < 1 && !$noLimit)
                $length = 10;

            $sql = "SELECT p.*, s.nama as nama_semester 
                    FROM participants p 
                    LEFT JOIN semesters s ON p.semester_id = s.id 
                    WHERE $whereSql
                    ORDER BY p.created_at DESC";

            // Only add LIMIT if not requesting all
            if (!$noLimit) {
                $sql .= " LIMIT $length OFFSET $start";
            }

            $participants = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

            // --- 5. Transform ---
            $data = array_map(function ($p) {
                $isS3 = (stripos($p['nama_prodi'] ?? '', 'S3') !== false || stripos($p['nama_prodi'] ?? '', 'DOKTOR') !== false);

                // Photo Logic
                $photoUrl = !empty($p['photo_filename']) ? '/storage/photos/' . $p['photo_filename'] : '/public/img/default-profile.png';
                if (empty($p['photo_filename'])) {
                    $photoUrl = 'https://ui-avatars.com/api/?name=' . urlencode($p['nama_lengkap']) . '&background=random&size=100';
                }

                return [
                    'id' => $p['id'],
                    'email' => $p['email'],
                    'nama_lengkap' => $p['nama_lengkap'],
                    'nomor_peserta' => $p['nomor_peserta'],
                    'nama_prodi' => $p['nama_prodi'],
                    'nama_semester' => $p['nama_semester'],
                    'photo_url' => $photoUrl,
                    'docs' => [
                        'photo' => !empty($p['photo_filename']),
                        'ktp' => !empty($p['ktp_filename']),
                        'ijazah' => !empty($p['ijazah_filename']),
                        'transkrip' => !empty($p['transkrip_filename']),
                        'ijazah_s2' => !empty($p['ijazah_s2_filename']),
                        'transkrip_s2' => !empty($p['transkrip_s2_filename']),
                        'is_s3' => $isS3
                    ]
                ];
            }, $participants);

            response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data
            ]);

        } catch (\Throwable $e) {
            response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function apiProdis()
    {
        if (!isset($_SESSION['admin'])) {
            response()->json([], 401);
            return;
        }

        $semesterId = Request::get('semester_id');
        $db = \App\Utils\Database::connection();

        $sql = "SELECT DISTINCT nama_prodi FROM participants WHERE nama_prodi IS NOT NULL AND nama_prodi != ''";
        $params = [];

        if (!empty($semesterId) && $semesterId !== 'all') {
            $sql .= " AND semester_id = ?";
            $params[] = $semesterId;
        }

        $sql .= " ORDER BY nama_prodi";

        try {
            if (!empty($params)) {
                $prodisRaw = $db->query($sql)->bind(...$params)->fetchAll(\PDO::FETCH_ASSOC);
            } else {
                $prodisRaw = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
            }
            $prodis = array_column($prodisRaw, 'nama_prodi');
            response()->json($prodis);
        } catch (\Exception $e) {
            response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // JSON API to get docs for preview modal
    public function getDocs($id)
    {
        if (!isset($_SESSION['admin'])) {
            response()->json(['success' => false], 401);
            return;
        }

        $p = \App\Models\Participant::find($id);
        if (!$p) {
            response()->json(['success' => false, 'message' => 'Not Found'], 404);
            return;
        }

        // Determine if S3
        $isS3 = (stripos($p['nama_prodi'] ?? '', 'S3') !== false || stripos($p['nama_prodi'] ?? '', 'DOKTOR') !== false);

        // Map documents - files are stored at /storage/documents/{subfolder}/filename
        // The filename in DB already contains the subfolder prefix (e.g., "2025-1/ktp_12345.jpg")
        $docs = [
            'photo' => $p['photo_filename'] ? '/storage/photos/' . $p['photo_filename'] : null,
            'ktp' => $p['ktp_filename'] ? '/storage/documents/' . $p['ktp_filename'] : null,
            'ijazah' => $p['ijazah_filename'] ? '/storage/documents/' . $p['ijazah_filename'] : null,
            'transkrip' => $p['transkrip_filename'] ? '/storage/documents/' . $p['transkrip_filename'] : null,
        ];

        if ($isS3) {
            $docs['ijazah_s2'] = $p['ijazah_s2_filename'] ? '/storage/documents/' . $p['ijazah_s2_filename'] : null;
            $docs['transkrip_s2'] = $p['transkrip_s2_filename'] ? '/storage/documents/' . $p['transkrip_s2_filename'] : null;
        }

        response()->json([
            'success' => true,
            'participant' => [
                'id' => $p['id'],
                'nama' => $p['nama_lengkap'],
                'email' => $p['email'],
                'is_s3' => $isS3
            ],
            'docs' => $docs
        ]);
    }

    // Specific Import for a participant (By ID)
    // Ignores filename convention "berkas_{email}" because we know the target ID.
    public function importZip($id)
    {
        try {
            if (!isset($_SESSION['admin'])) {
                response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
                return;
            }

            $participant = \App\Models\Participant::find($id);
            if (!$participant) {
                response()->json(['success' => false, 'message' => 'Peserta tidak ditemukan'], 404);
                return;
            }

            $file = $_FILES['file'] ?? null;
            if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
                response()->json(['success' => false, 'message' => 'Upload error'], 400);
                return;
            }

            // Move to Temp
            $tempDir = dirname(__DIR__, 2) . '/storage/temp';
            if (!is_dir($tempDir))
                mkdir($tempDir, 0755, true);
            $tempZipPath = $tempDir . '/' . uniqid('import_adhoc_') . '.zip';

            if (!move_uploaded_file($file['tmp_name'], $tempZipPath)) {
                if (!copy($file['tmp_name'], $tempZipPath)) {
                    response()->json(['success' => false, 'message' => 'Gagal simpan temp file'], 500);
                    return;
                }
            }

            // Calls shared logic
            $result = $this->processZipFile($tempZipPath, $participant);

            // Allow processZipFile to handle cleanup or do it here?
            // Shared logic does NOT unlink because sometimes we want to keep it?
            // Actually, best to cleanup HERE for uploaded file.
            if (file_exists($tempZipPath))
                unlink($tempZipPath);

            response()->json($result);

        } catch (\Throwable $e) {
            response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function sync($id)
    {
        if (!isset($_SESSION['admin'])) {
            response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $participant = \App\Models\Participant::find($id);
            if (!$participant || empty($participant['email'])) {
                response()->json(['success' => false, 'message' => 'Peserta/Email tidak ditemukan'], 404);
                return;
            }

            // 1. Get Cookie
            $sessionCookie = \App\Models\Setting::get('admisipasca_session_cookie', '');
            if (empty($sessionCookie)) {
                response()->json(['success' => false, 'message' => 'Konfigurasi Cookie belum diset di menu Import'], 400);
                return;
            }

            // 2. Download
            $email = $participant['email'];
            $targetUrl = "https://admisipasca.ulm.ac.id/administrator/formulir/download_zip/{$email}/1";

            $ch = curl_init($targetUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_COOKIE, $sessionCookie);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120); // Longer timeout for big files

            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            if ($httpCode !== 200 || empty($content)) {
                throw new \Exception("Gagal download dari server lama. HTTP Code: $httpCode");
            }

            // Verify content type is ZIP
            if (stripos($contentType, 'zip') === false && substr($content, 0, 2) !== 'PK') {
                // Sometimes login page is returned
                throw new \Exception("Gagal autektikasi. Cek kembali cookie.");
            }

            // 3. Update Status to 'Lulus Berkas' (As per user request "status automatically")
            // Actually user just said "Auto Import", but usually implies verifying presence.
            // Using existing logic which just saves files.

            // 4. Save Temp
            $tempDir = dirname(__DIR__, 2) . '/storage/temp';
            if (!is_dir($tempDir))
                mkdir($tempDir, 0755, true);
            $tempZipPath = $tempDir . '/' . uniqid('sync_') . '.zip';
            file_put_contents($tempZipPath, $content);

            // 5. Process
            $result = $this->processZipFile($tempZipPath, $participant);

            // Cleanup
            if (file_exists($tempZipPath))
                unlink($tempZipPath);

            response()->json($result);

        } catch (\Throwable $e) {
            response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Shared logic to extract and save files from a ZIP for a participant
     */
    private function processZipFile($zipPath, $participant)
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \Exception('Gagal membuka file ZIP');
        }

        $baseStorage = dirname(__DIR__, 2) . '/storage';
        $semester = \App\Models\Semester::find($participant['semester_id']);
        $subfolder = $semester ? $semester['kode'] : 'legacy';

        $paths = [
            'photo' => $baseStorage . '/photos/' . $subfolder,
            'doc' => $baseStorage . '/documents/' . $subfolder
        ];
        foreach ($paths as $pPath) {
            if (!is_dir($pPath))
                mkdir($pPath, 0755, true);
        }

        $db = \App\Utils\Database::connection();
        $log = [];
        $processed = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            if (substr($entryName, -1) === '/' || strpos($entryName, '__MACOSX') === 0 || strpos($entryName, '.DS_Store') !== false)
                continue;

            $lowerName = strtolower($entryName);
            $fileContent = $zip->getFromIndex($i);

            // --- IDENTIFICATION LOGIC ---
            $type = null;
            $ext = pathinfo($lowerName, PATHINFO_EXTENSION);

            // Rules:
            // Allow both Image (JPG/PNG) and PDF for all main documents to be safe.
            // Identify based on filename keywords first.

            $isImage = in_array($ext, ['jpg', 'jpeg', 'png']);
            $isPdf = ($ext === 'pdf');

            if (!$isImage && !$isPdf) {
                continue; // Skip unknown extensions
            }

            // Keyword Matching
            if (strpos($lowerName, 'foto') !== false || strpos($lowerName, 'pas_foto') !== false || strpos($lowerName, 'pasfoto') !== false) {
                $type = 'photo';
            } elseif (strpos($lowerName, 'ktp') !== false || strpos($lowerName, 'identitas') !== false || strpos($lowerName, 'nik') !== false) {
                $type = 'ktp';
            } elseif (strpos($lowerName, 'ijazah') !== false || strpos($lowerName, 'ijasah') !== false) {
                $type = 'ijazah';
                if (strpos($lowerName, 's2') !== false)
                    $type = 'ijazah_s2';
            } elseif (strpos($lowerName, 'transkrip') !== false || strpos($lowerName, 'nilai') !== false) {
                $type = 'transkrip';
                if (strpos($lowerName, 's2') !== false)
                    $type = 'transkrip_s2';
            }

            if (!$type) {
                // Log skipped for debugging
                $log[] = "Skipped: $lowerName (No Keyword Match)";
                continue;
            }

            // --- SAVING ---
            $newFilename = '';
            $dbColumn = '';
            $destPath = '';

            if ($type === 'photo') {
                $newFilename = ($participant['nomor_peserta'] ?? 'temp_' . $participant['id']) . '.' . $ext;
                $destPath = $paths['photo'] . '/' . $newFilename;
                $dbPath = $subfolder . '/' . $newFilename;
                $dbColumn = 'photo_filename';
            } else {
                $prefix = $type . '_';
                $newFilename = $prefix . ($participant['nomor_peserta'] ?? $participant['id']) . '.' . $ext;
                $destPath = $paths['doc'] . '/' . $newFilename;
                $dbPath = $subfolder . '/' . $newFilename;

                if ($type === 'ijazah_s2')
                    $dbColumn = 'ijazah_s2_filename';
                elseif ($type === 'transkrip_s2')
                    $dbColumn = 'transkrip_s2_filename';
                elseif ($type === 'ktp')
                    $dbColumn = 'ktp_filename';
                elseif ($type === 'ijazah')
                    $dbColumn = 'ijazah_filename';
                elseif ($type === 'transkrip')
                    $dbColumn = 'transkrip_filename';
                else
                    $dbColumn = $type . '_filename';
            }

            file_put_contents($destPath, $fileContent);

            $db->update('participants')
                ->params([$dbColumn => $dbPath])
                ->where('id', $participant['id'])
                ->execute();

            $processed++;
            $log[] = "Updated $type";
        }

        $zip->close();

        return ['success' => true, 'processed' => $processed, 'log' => $log];
    }

}
