<?php

namespace App\Controllers;

use App\Models\Participant;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Leaf\Http\Request;

class ImportController
{
    public function index()
    {
        if (!isset($_SESSION['admin'])) {
            response()->redirect('/admin');
            return;
        }
        $semesters = \App\Models\Semester::all();

        // Check cookie status for auto-download
        $sessionCookie = \App\Models\Setting::get('admisipasca_session_cookie', '');
        $cookieStatus = !empty($sessionCookie) ? 'configured' : 'not_configured';

        echo \App\Utils\View::render('admin.import', [
            'semesters' => $semesters,
            'cookieStatus' => $cookieStatus
        ]);
    }

    public function import()
    {
        // Enable realtime output
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', 1);
        }
        @ini_set('output_buffering', 0);
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);
        for ($i = 0; $i < ob_get_level(); $i++) {
            ob_end_flush();
        }
        ob_implicit_flush(1);

        // Start Log View
        echo '<!DOCTYPE html><html><head>';
        echo '<script src="https://cdn.tailwindcss.com"></script>';
        echo '</head><body class="bg-gray-100 p-8">';
        echo '<div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6">';
        echo '<h2 class="text-2xl font-bold mb-4">Proses Import Data...</h2>';
        echo '<div id="log-container" class="h-96 overflow-y-auto bg-gray-900 text-green-400 p-4 font-mono text-sm rounded mb-4">';

        try {
            $semesterId = request()->get('semester_id');
            $file = request()->files('file');
            $importBehavior = request()->get('import_behavior') ?? 'insert_new_only';

            if (!$file || !$semesterId) {
                throw new \Exception("File atau Semester tidak dipilih.");
            }

            echo "<div>[INFO] Membaca file: " . $file['name'] . "...</div>";
            $this->flush_msg();

            $importType = request()->get('import_type') ?? 'formulir_masuk';
            $isLegacy = request()->get('is_legacy') == 1;

            // Map Type to Mode + Status
            $importMode = 'full';
            $defaultStatus = 'pending';

            switch ($importType) {
                case 'lulus_berkas':
                    $importMode = 'full';
                    $defaultStatus = 'lulus';
                    break;
                case 'gagal_berkas':
                    $importMode = 'full'; // Gagal usually doesn't pay, but 'full' safely handles empty payment cols
                    $defaultStatus = 'gagal';
                    break;
                case 'nomor_peserta':
                    $importMode = 'update_no_peserta';
                    break;
                case 'formulir_masuk':
                default:
                    $importMode = 'full';
                    $defaultStatus = 'pending';
                    break;
            }

            echo "<div>[INFO] Mode Import: $importType</div>";
            echo "<div>[INFO] Logic Mode: $importMode | Target Status: $defaultStatus</div>";
            echo "<div>[INFO] Perilaku Import: $importBehavior</div>";
            echo "<div>[INFO] Struktur Lama: " . ($isLegacy ? 'YA' : 'TIDAK') . "</div>";
            $this->flush_msg();

            // Suppress DOM warnings for HTML-format XLS files
            libxml_use_internal_errors(true);
            try {
                $spreadsheet = IOFactory::load($file['tmp_name']);
            } catch (\Exception $e) {
                // Should fallback or retry?
                throw new \Exception("Gagal membaca file Excel/HTML: " . $e->getMessage());
            } finally {
                libxml_clear_errors();
            }

            $finalData = []; // Store summary

            foreach ($spreadsheet->getAllSheets() as $sheetIndex => $sheet) {
                echo "<div>[INFO] Check Sheet " . ($sheetIndex + 1) . "...</div>";
                $this->flush_msg();

                $data = $sheet->toArray();
                if (count($data) < 2)
                    continue;

                $headers = $data[0] ?? [];
                // Normalize Headers
                $headers = array_map(function ($h) {
                    return strtolower(trim($h ?? ''));
                }, $headers);

                $successCount = 0;
                $errorCount = 0;
                $skippedCount = 0;
                $updatedCount = 0;

                try {
                    // Pass semester ID to processors
                    // PRIORITY CHECK: 'nomor peserta' indicates Ujian File (which also contains billing/mother name)
                    // If Legacy, we might not have standard headers yet, but usually 'tempat, tanggal lahir' is the key
                    $hasNomorPeserta = in_array('nomor peserta', $headers) || in_array('status kelulusan', $headers);
                    $hasBilling = (in_array('no. billing', $headers) || in_array('billing', $headers)) && in_array('nama ibu', $headers);
                    $hasLegacyHeader = $isLegacy && in_array('tempat, tanggal lahir', $headers);

                    if ($hasNomorPeserta) {
                        $this->processLoop($data, $headers, $semesterId, $successCount, $errorCount, $skippedCount, $updatedCount, 'ujian', $importMode, $defaultStatus, $isLegacy, $importBehavior);
                    } elseif ($hasBilling || $hasLegacyHeader) {
                        $this->processLoop($data, $headers, $semesterId, $successCount, $errorCount, $skippedCount, $updatedCount, 'formulir', $importMode, $defaultStatus, $isLegacy, $importBehavior);
                    } else {
                        throw new \Exception("Format file tidak dikenali. Pastikan header sesuai.");
                    }
                } catch (\Exception $e) {
                    echo "<div class='text-red-500'>[ERROR] Gagal memproses sheet: " . $e->getMessage() . "</div>";
                }
            }

            echo "<div class='mt-4 text-white'>[DONE] Import Selesai.</div>";

        } catch (\Exception $e) {
            echo "<div class='text-red-500'>[FATAL] " . $e->getMessage() . "</div>";
        }

        echo '</div>'; // End log container
        echo '<a href="/admin/import" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Kembali ke Menu Import</a>';
        echo '</div></body></html>';
    }

    private function flush_msg()
    {
        echo str_repeat(' ', 1024 * 64);
        flush();
        if (ob_get_level() > 0)
            ob_flush();
    }

    private function processLoop($data, $headers, $semesterId, &$success, &$error, &$skipped, &$updated, $type, $importMode = 'full', $defaultStatus = 'pending', $isLegacy = false, $importBehavior = 'insert_new_only')
    {
        $map = array_flip($headers);
        $total = count($data) - 1;

        echo "<div class='text-yellow-400'>[INFO] Memproses $total baris data...</div>";
        $this->flush_msg();

        for ($i = 1; $i <= $total; $i++) {
            $row = $data[$i];
            try {
                $result = null;
                if ($type === 'formulir') {
                    // Formulir usually implies full import of biodata
                    $result = $this->processRowFormulir($row, $map, $semesterId, $i, $importMode, $defaultStatus, $isLegacy, $importBehavior);
                } else {
                    $result = $this->processRowUjian($row, $map, $semesterId, $i, $importMode, $defaultStatus, $isLegacy, $importBehavior);
                }

                // Track based on result status
                if ($result['status'] === 'new') {
                    $success++;
                    echo "<div class='text-green-400'>[NEW ✓] Baris $i: {$result['name']} ({$result['email']}) - Data baru diimport</div>";
                } elseif ($result['status'] === 'skipped') {
                    $skipped++;
                    // Silent skip - tidak tampilkan di log untuk menjaga kebersihan
                } elseif ($result['status'] === 'updated') {
                    $updated++;
                    echo "<div class='text-blue-400'>[UPDATE ↻] Baris $i: {$result['name']} ({$result['email']}) - Data diupdate</div>";
                }
            } catch (\Exception $e) {
                $error++;
                echo "<div class='text-red-400'>[FAIL ✗] Baris $i: " . $e->getMessage() . "</div>";
            }

            // Auto scroll script
            echo '<script>var elem = document.getElementById("log-container"); elem.scrollTop = elem.scrollHeight;</script>';
            $this->flush_msg();
        }

        // Display summary statistics
        echo "<div class='mt-6 p-4 bg-gray-800 rounded-lg border border-gray-700'>";
        echo "<div class='text-xl font-bold text-white mb-3'>📊 Ringkasan Import</div>";
        echo "<div class='grid grid-cols-2 gap-3 text-sm'>";
        echo "<div class='bg-green-900 p-3 rounded'><span class='font-bold text-green-400'>✅ Data Baru:</span> <span class='text-white text-lg'>$success</span> record</div>";
        echo "<div class='bg-yellow-900 p-3 rounded'><span class='font-bold text-yellow-400'>⏭️ Data Dilewati:</span> <span class='text-white text-lg'>$skipped</span> record</div>";
        if ($updated > 0) {
            echo "<div class='bg-blue-900 p-3 rounded'><span class='font-bold text-blue-400'>🔄 Data Diupdate:</span> <span class='text-white text-lg'>$updated</span> record</div>";
        }
        if ($error > 0) {
            echo "<div class='bg-red-900 p-3 rounded'><span class='font-bold text-red-400'>❌ Data Gagal:</span> <span class='text-white text-lg'>$error</span> record</div>";
        }
        echo "<div class='bg-gray-700 p-3 rounded col-span-2'><span class='font-bold text-gray-300'>📋 Total Diproses:</span> <span class='text-white text-lg'>$total</span> baris</div>";
        echo "</div></div>";
        $this->flush_msg();
    }

    private function processRowFormulir($row, $map, $semesterId, $rowIdx, $importMode, $defaultStatus, $isLegacy = false, $importBehavior = 'insert_new_only')
    {
        $emailRaw = isset($map['email']) ? ($row[$map['email']] ?? null) : null;
        if (!$emailRaw)
            throw new \Exception("Email kosong.");

        $email = strtolower(trim($emailRaw));

        $no_billing = (isset($map['no. billing']) ? ($row[$map['no. billing']] ?? null) : null) ?? (isset($map['billing']) ? ($row[$map['billing']] ?? null) : null);
        $nama = isset($map['nama']) ? ($row[$map['nama']] ?? null) : null;

        $tempat_lahir = null;
        $tgl_lahir = null;

        if ($isLegacy && (isset($map['tempat, tanggal lahir']) || isset($map['tempat, tgl lahir']))) {
            $combined = $row[$map['tempat, tanggal lahir']] ?? $row[$map['tempat, tgl lahir']] ?? '';
            // Split by last comma
            $lastComma = strrpos($combined, ',');
            if ($lastComma !== false) {
                $tempat_lahir = trim(substr($combined, 0, $lastComma));
                $tgl_lahir_raw = trim(substr($combined, $lastComma + 1));
                $tgl_lahir = $this->parseDateID($tgl_lahir_raw);
            } else {
                // Fallback attempt
                $tempat_lahir = $combined;
            }
        } else {
            $tgl_lahir_raw = $row[$map['tanggal lahir']] ?? $row[$map['tgl lahir']] ?? null;
            $tgl_lahir = $this->parseDateID($tgl_lahir_raw);
            // Correct header from file: 'Tempat'
            $tempat_lahir = $row[$map['tempat']] ?? $row[$map['tempat lahir']] ?? null;
        }

        $kode_prodi = isset($map['kode prodi']) ? ($row[$map['kode prodi']] ?? null) : null;
        $nama_prodi = isset($map['nama prodi']) ? ($row[$map['nama prodi']] ?? null) : null;

        // Biodata Lengkap
        // Correct header: 'Alamat'
        $alamat_ktp = (isset($map['alamat']) ? ($row[$map['alamat']] ?? null) : null) ?? (isset($map['alamat ktp']) ? ($row[$map['alamat ktp']] ?? null) : null);
        $kecamatan = isset($map['kecamatan']) ? ($row[$map['kecamatan']] ?? null) : null;
        // Correct header: 'Kabupaten/Kota' or 'Kota'
        $kota = (isset($map['kabupaten/kota']) ? ($row[$map['kabupaten/kota']] ?? null) : null) ?? (isset($map['kota']) ? ($row[$map['kota']] ?? null) : null);
        $provinsi = isset($map['provinsi']) ? ($row[$map['provinsi']] ?? null) : null;
        $kode_pos = isset($map['kode pos']) ? ($row[$map['kode pos']] ?? null) : null;
        $no_hp = isset($map['telpon/hp']) ? ($row[$map['telpon/hp']] ?? null) : null;
        $agama = isset($map['agama']) ? ($row[$map['agama']] ?? null) : null;

        $jenis_kelamin = isset($map['jenis kelamin']) ? ($row[$map['jenis kelamin']] ?? null) : null;
        if (!$jenis_kelamin)
            $jenis_kelamin = isset($map['gender']) ? ($row[$map['gender']] ?? null) : null;

        // Correct header: 'Status Kawin'
        $status_pernikahan = (isset($map['status kawin']) ? ($row[$map['status kawin']] ?? null) : null) ?? (isset($map['status pernikahan']) ? ($row[$map['status pernikahan']] ?? null) : null);
        $pekerjaan = isset($map['pekerjaan']) ? ($row[$map['pekerjaan']] ?? null) : null;
        $instansi_pekerjaan = isset($map['instansi pekerjaan']) ? ($row[$map['instansi pekerjaan']] ?? null) : null;
        $alamat_pekerjaan = isset($map['alamat pekerjaan']) ? ($row[$map['alamat pekerjaan']] ?? null) : null;
        // Correct header: 'Telepon Pekerjaan'
        $telpon_pekerjaan = isset($map['telepon pekerjaan']) ? ($row[$map['telepon pekerjaan']] ?? null) : null;

        // Pendidikan S1 (Safe Check)
        $s1_tahun_masuk = isset($map['tahun masuk s1']) ? ($row[$map['tahun masuk s1']] ?? null) : null;
        $s1_tahun_tamat = isset($map['tahun lulus s1']) ? ($row[$map['tahun lulus s1']] ?? null) : (isset($map['tahun tamat s1']) ? ($row[$map['tahun tamat s1']] ?? null) : null);
        $s1_perguruan_tinggi = isset($map['nama pt s1']) ? ($row[$map['nama pt s1']] ?? null) : (isset($map['perguruan tinggi s1']) ? ($row[$map['perguruan tinggi s1']] ?? null) : null);
        $s1_fakultas = isset($map['fakultas s1']) ? ($row[$map['fakultas s1']] ?? null) : null;
        $s1_prodi = isset($map['prodi s1']) ? ($row[$map['prodi s1']] ?? null) : (isset($map['program studi s1']) ? ($row[$map['program studi s1']] ?? null) : null);
        $s1_ipk = isset($map['ipk s1']) ? ($row[$map['ipk s1']] ?? null) : null;
        $s1_gelar = isset($map['gelar s1']) ? ($row[$map['gelar s1']] ?? null) : null;

        // Pendidikan S2 (Safe Check)
        $s2_tahun_masuk = isset($map['tahun masuk s2']) ? ($row[$map['tahun masuk s2']] ?? null) : null;
        $s2_tahun_tamat = isset($map['tahun lulus s2']) ? ($row[$map['tahun lulus s2']] ?? null) : (isset($map['tahun tamat s2']) ? ($row[$map['tahun tamat s2']] ?? null) : null);
        $s2_perguruan_tinggi = isset($map['nama pt s2']) ? ($row[$map['nama pt s2']] ?? null) : (isset($map['perguruan tinggi s2']) ? ($row[$map['perguruan tinggi s2']] ?? null) : null);
        $s2_fakultas = isset($map['fakultas s2']) ? ($row[$map['fakultas s2']] ?? null) : null;
        $s2_prodi = isset($map['prodi s2']) ? ($row[$map['prodi s2']] ?? null) : (isset($map['program studi s2']) ? ($row[$map['program studi s2']] ?? null) : null);
        $s2_ipk = isset($map['ipk s2']) ? ($row[$map['ipk s2']] ?? null) : null;
        $s2_gelar = isset($map['gelar s2']) ? ($row[$map['gelar s2']] ?? null) : null;

        // Payment Check
        $bank = isset($map['bank']) ? ($row[$map['bank']] ?? null) : null;
        $kanal = isset($map['kanal']) ? ($row[$map['kanal']] ?? null) : null;
        $tgl_bayar = isset($map['tanggal/jam']) ? ($row[$map['tanggal/jam']] ?? null) : null;
        $status_pembayaran = (!empty($bank) && !empty($kanal) && !empty($tgl_bayar)) ? 1 : 0;

        // NEW: Prepare payment details (only saved for lulus_berkas)
        $payment_method = null;
        $payment_date = null;
        if ($status_pembayaran && !empty($bank) && !empty($kanal)) {
            $payment_method = strtoupper(trim($bank)) . '(' . strtoupper(trim($kanal)) . ')';
        }
        if ($status_pembayaran && !empty($tgl_bayar)) {
            $payment_date = $this->parsePaymentDate($tgl_bayar);
        }

        $saveData = [
            'semester_id' => $semesterId,
            'nama_lengkap' => $nama,
            'email' => $email,
            'tempat_lahir' => $tempat_lahir,
            'tgl_lahir' => $tgl_lahir,
            'kode_prodi' => $kode_prodi,
            'nama_prodi' => $nama_prodi,

            // New Fields
            'alamat_ktp' => $alamat_ktp,
            'kecamatan' => $kecamatan,
            'kota' => $kota,
            'provinsi' => $provinsi,
            'kode_pos' => $kode_pos,
            'no_hp' => $no_hp,
            'agama' => $agama,
            'jenis_kelamin' => $jenis_kelamin,
            'status_pernikahan' => $status_pernikahan,
            'pekerjaan' => $pekerjaan,
            'instansi_pekerjaan' => $instansi_pekerjaan,
            'alamat_pekerjaan' => $alamat_pekerjaan,
            'telpon_pekerjaan' => $telpon_pekerjaan,
            's1_tahun_masuk' => $s1_tahun_masuk,
            's1_tahun_tamat' => $s1_tahun_tamat,
            's1_perguruan_tinggi' => $s1_perguruan_tinggi,
            's1_fakultas' => $s1_fakultas,
            's1_prodi' => $s1_prodi,
            's1_ipk' => $s1_ipk,
            's1_gelar' => $s1_gelar,

            // S2 Fields
            's2_tahun_masuk' => $s2_tahun_masuk,
            's2_tahun_tamat' => $s2_tahun_tamat,
            's2_perguruan_tinggi' => $s2_perguruan_tinggi,
            's2_fakultas' => $s2_fakultas,
            's2_prodi' => $s2_prodi,
            's2_ipk' => $s2_ipk,
            's2_gelar' => $s2_gelar,

            'status_berkas' => $defaultStatus,
        ];

        // ADD: Conditional Billing and Payment Status for Legacy
        if ($isLegacy) {
            if (!empty($no_billing)) {
                $saveData['no_billing'] = $no_billing;
            }
            // User says: "kalau itu kosong berarti pembayaran belum lunas, data tetap dimasukkan"
            // So we ALWAYS update status_pembayaran in legacy if mode is full
            if ($importMode === 'full') {
                $saveData['status_pembayaran'] = $status_pembayaran;
                // NEW: Only save payment details for lulus_berkas
                if ($defaultStatus === 'lulus') {
                    if ($payment_method)
                        $saveData['payment_method'] = $payment_method;
                    if ($payment_date)
                        $saveData['payment_date'] = $payment_date;
                    if ($no_billing)
                        $saveData['transaction_id'] = $no_billing;
                }
            }
        } else {
            // Standard Mode: always include
            $saveData['no_billing'] = $no_billing;
            if ($importMode === 'full') {
                $saveData['status_pembayaran'] = $status_pembayaran;
                // NEW: Only save payment details for lulus_berkas
                if ($defaultStatus === 'lulus') {
                    if ($payment_method)
                        $saveData['payment_method'] = $payment_method;
                    if ($payment_date)
                        $saveData['payment_date'] = $payment_date;
                    if ($no_billing)
                        $saveData['transaction_id'] = $no_billing;
                }
            }
        }

        // Removed Filename detection to respect user selection
        // $originalName = Request::files('file')['name'];
        // if (str_contains(strtolower($originalName), 'lulus')) {
        //     $saveData['status_berkas'] = 'lulus';
        // } elseif (str_contains(strtolower($originalName), 'gagal')) {
        //     $saveData['status_berkas'] = 'gagal';
        // }

        $existing = Participant::where('email', $email)->first();

        if ($existing) {
            // Data sudah ada - cek import behavior
            if ($importBehavior === 'insert_new_only') {
                // Skip existing data
                return [
                    'status' => 'skipped',
                    'name' => $nama ?? 'Unknown',
                    'email' => $email
                ];
            } elseif ($importBehavior === 'update_existing' || $importBehavior === 'insert_and_update') {
                // Update existing data
                \App\Utils\Database::connection()->update('participants')
                    ->params($saveData)
                    ->where('id', $existing['id'])
                    ->execute();

                return [
                    'status' => 'updated',
                    'name' => $nama ?? 'Unknown',
                    'email' => $email
                ];
            }
        } else {
            // Data baru - insert
            \App\Utils\Database::connection()->insert('participants')
                ->params($saveData)
                ->execute();

            return [
                'status' => 'new',
                'name' => $nama ?? 'Unknown',
                'email' => $email
            ];
        }
    }

    private function processRowUjian($row, $map, $semesterId, $rowIdx, $importMode, $defaultStatus, $isLegacy = false, $importBehavior = 'insert_new_only')
    {
        $emailRaw = isset($map['email']) ? ($row[$map['email']] ?? null) : null;
        if (!$emailRaw)
            throw new \Exception("Email kosong.");

        $email = strtolower(trim($emailRaw));

        $nomor_peserta = isset($map['nomor peserta']) ? ($row[$map['nomor peserta']] ?? null) : null;

        $participant = Participant::where('email', $email)->first();
        if (!$participant)
            throw new \Exception("Peserta dengan email $email tidak ditemukan.");

        $updateData = [
            'nomor_peserta' => $nomor_peserta,
            'semester_id' => $semesterId
        ];

        // LOGIC: Jika ada nomor peserta, berarti sudah bayar
        // Nomor peserta hanya diberikan ke peserta yang sudah melakukan pembayaran
        if (!empty($nomor_peserta)) {
            $updateData['status_pembayaran'] = 1;
        }

        // Payment Check - ONLY if mode is 'full' (or standard/custom status handles it)
        // If importMode is 'update_no_peserta', we SKIP payment check.
        // If importMode is 'full' (Standard/Status), we check payment.
        if ($importMode === 'full') {
            $bank = isset($map['bank']) ? ($row[$map['bank']] ?? null) : null;
            $kanal = isset($map['kanal']) ? ($row[$map['kanal']] ?? null) : null;
            $tgl_bayar = isset($map['tanggal/jam']) ? ($row[$map['tanggal/jam']] ?? null) : null;
            $is_paid = (!empty($bank) && !empty($kanal) && !empty($tgl_bayar));

            if ($is_paid) {
                $updateData['status_pembayaran'] = 1;
            }
        }

        // Check import behavior for ujian updates
        if ($importBehavior === 'insert_new_only' && !empty($participant['nomor_peserta'])) {
            // Already has nomor_peserta, skip
            return [
                'status' => 'skipped',
                'name' => $participant['nama_lengkap'] ?? 'Unknown',
                'email' => $email
            ];
        }

        \App\Utils\Database::connection()->update('participants')
            ->params($updateData)
            ->where('id', $participant['id'])
            ->execute();

        return [
            'status' => 'updated',
            'name' => $participant['nama_lengkap'] ?? 'Unknown',
            'email' => $email
        ];
    }


    private function parseDateID($dateStr)
    {
        // "10 MARET 1991"
        if (!$dateStr)
            return null;

        $months = [
            'JANUARI' => '01',
            'FEBRUARI' => '02',
            'MARET' => '03',
            'APRIL' => '04',
            'MEI' => '05',
            'JUNI' => '06',
            'JULI' => '07',
            'AGUSTUS' => '08',
            'SEPTEMBER' => '09',
            'OKTOBER' => '10',
            'NOVEMBER' => '11',
            'DESEMBER' => '12'
        ];

        $parts = explode(' ', strtoupper($dateStr));
        if (count($parts) < 3)
            return date('Y-m-d', strtotime($dateStr)); // Fallback

        $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
        $month = $months[$parts[1]] ?? '01';
        $year = $parts[2];

        return "$year-$month-$day";
    }

    private function parsePaymentDate($dateStr)
    {
        // Input: "09/12/2025 13:50" or "02/01/2026 09:06:29"
        if (!$dateStr)
            return null;

        try {
            // Try parsing dd/mm/yyyy HH:ii:ss or dd/mm/yyyy HH:ii
            $dt = \DateTime::createFromFormat('d/m/Y H:i:s', trim($dateStr));
            if (!$dt) {
                $dt = \DateTime::createFromFormat('d/m/Y H:i', trim($dateStr));
            }
            return $dt ? $dt->format('Y-m-d H:i:s') : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function autoDownload()
    {
        if (!isset($_SESSION['admin'])) {
            response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $type = request()->get('type'); // 'dikirim', 'lulus', 'gagal', 'kartu'
        $semesterId = request()->get('semester_id');
        $isLegacy = request()->get('is_legacy') == 1;
        $importBehavior = request()->get('import_behavior') ?? 'insert_new_only'; // NEW

        if (!$type || !$semesterId) {
            response()->json(['success' => false, 'message' => 'Type atau semester tidak dipilih'], 400);
            return;
        }

        // Get session cookie
        $sessionCookie = \App\Models\Setting::get('admisipasca_session_cookie', '');
        if (empty($sessionCookie)) {
            response()->json(['success' => false, 'message' => 'Session cookie belum dikonfigurasi'], 400);
            return;
        }

        // Map type to URL
        $urls = [
            'dikirim' => 'https://admisipasca.ulm.ac.id/administrator/formulir/downloadFormulir/dikirim/all',
            'lulus' => 'https://admisipasca.ulm.ac.id/administrator/formulir/downloadFormulir/lulus/all',
            'gagal' => 'https://admisipasca.ulm.ac.id/administrator/formulir/downloadFormulir/gagal/all',
            'kartu' => 'https://admisipasca.ulm.ac.id/administrator/kartu/downloadListKartu/all'
        ];

        // Map type to import mode
        $importTypeMapping = [
            'dikirim' => ['mode' => 'full', 'status' => 'pending'],
            'lulus' => ['mode' => 'full', 'status' => 'lulus'],
            'gagal' => ['mode' => 'full', 'status' => 'gagal'],
            'kartu' => ['mode' => 'update_no_peserta', 'status' => 'pending']
        ];

        try {
            // Download Excel via cURL
            $ch = curl_init($urls[$type]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_COOKIE, $sessionCookie);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);

            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || empty($content)) {
                throw new \Exception("Gagal download dari server. HTTP Code: $httpCode");
            }

            // Save to temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'import_') . '.xlsx';
            file_put_contents($tempFile, $content);

            // Parse Excel
            libxml_use_internal_errors(true);
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tempFile);
            } catch (\Exception $e) {
                throw new \Exception("Gagal membaca file Excel: " . $e->getMessage());
            } finally {
                libxml_clear_errors();
            }

            $successCount = 0;
            $errorCount = 0;
            $skippedCount = 0;  // NEW
            $updatedCount = 0;  // NEW
            $importMode = $importTypeMapping[$type]['mode'];
            $defaultStatus = $importTypeMapping[$type]['status'];

            // Process each sheet
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $data = $sheet->toArray();
                if (count($data) < 2)
                    continue;

                $headers = array_map(function ($h) {
                    return strtolower(trim($h ?? ''));
                }, $data[0]);

                $map = array_flip($headers);

                // Detect file type
                $hasNomorPeserta = in_array('nomor peserta', $headers) || in_array('status kelulusan', $headers);
                $hasBilling = (in_array('no. billing', $headers) || in_array('billing', $headers)) && in_array('nama ibu', $headers);
                $hasLegacyHeader = $isLegacy && in_array('tempat, tanggal lahir', $headers);

                $processType = null;
                if ($hasNomorPeserta) {
                    $processType = 'ujian';
                } elseif ($hasBilling || $hasLegacyHeader) {
                    $processType = 'formulir';
                } else {
                    continue; // Skip unrecognized sheets
                }

                // Process rows
                $total = count($data) - 1;
                for ($i = 1; $i <= $total; $i++) {
                    $row = $data[$i];
                    try {
                        $result = null;
                        if ($processType === 'formulir') {
                            $result = $this->processRowFormulir($row, $map, $semesterId, $i, $importMode, $defaultStatus, $isLegacy, $importBehavior);
                        } else {
                            $result = $this->processRowUjian($row, $map, $semesterId, $i, $importMode, $defaultStatus, $isLegacy, $importBehavior);
                        }

                        // Track based on result status
                        if ($result['status'] === 'new') {
                            $successCount++;
                        } elseif ($result['status'] === 'skipped') {
                            $skippedCount++;
                        } elseif ($result['status'] === 'updated') {
                            $updatedCount++;
                        }
                    } catch (\Exception $e) {
                        $errorCount++;
                    }
                }
            }

            // Clean up temp file
            @unlink($tempFile);

            response()->json([
                'success' => true,
                'message' => 'Auto-import berhasil',
                'imported' => $successCount,
                'skipped' => $skippedCount,
                'updated' => $updatedCount,
                'failed' => $errorCount,
                'type' => $type,
                'behavior' => $importBehavior
            ]);

        } catch (\Exception $e) {
            response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
