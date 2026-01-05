# Rencana Implementasi: Alur Kerja Lengkap PMB Pascasarjana

## 1. Evaluasi & Analisis Sistem Saat Ini

Berdasarkan struktur database dan logika kode yang ada, berikut adalah kesenjangan (gap) antara sistem saat ini dengan alur kerja yang diharapkan:

| Fitur / Tahapan | Status Saat Ini | Masalah / Gap | Solusi |
| :--- | :--- | :--- | :--- |
| **Status Online** | `status_berkas` digunakan (Pending, Lulus, Gagal). Nilai ini sering tertimpa oleh proses import. | Ambigu. Status 'Lulus' dari server admisi dianggap sebagai status final berkas, padahal ada tahap fisik. | Pertahankan `status_berkas` sebagai **Status Pemberkasan Online**. Pastikan import hanya mengupdate ini. |
| **Status Fisik** | Tidak ada kolom eksplisit. Hanya ada checklist dokumen (boolean) dan logika parsial. | Belum ada status `valid/invalid` yang tegas untuk Verifikasi Fisik di database. | Buat kolom baru `status_verifikasi_fisik` (Pending, Lengkap, Tidak Lengkap). |
| **Kartu Ujian** | Bisa didownload jika `status_berkas == 'lulus'` (Online) ATAU punya nomor peserta. | Logika saat ini terlalu longgar. Peserta bisa download kartu meski belum verifikasi fisik. | Kunci download kartu: Hanya jika `status_verifikasi_fisik == 'valid'` (Lengkap). |
| **Kelulusan Akhir** | Belum ada modul sama sekali. | Admin tidak bisa memproses SK Rektor atau menentukan siapa yang diterima menjadi mahasiswa. | Buat modul **Kelulusan Akhir** (Input manual / Import SK) + kolom `status_kelulusan_akhir`. |

---

## 2. Rencana Implementasi Teknis

### Phase 1: Database Migration (Separation of Concerns)
Memisahkan status agar tidak saling menimpa.

```sql
ALTER TABLE participants ADD COLUMN status_verifikasi_fisik VARCHAR(20) DEFAULT 'pending'; -- pending, lengkap, tidak_lengkap
ALTER TABLE participants ADD COLUMN status_kelulusan_akhir VARCHAR(20) DEFAULT 'pending'; -- pending, lulus, tidak_lulus
ALTER TABLE participants ADD COLUMN sk_kelulusan VARCHAR(100) NULL; -- Nomor SK Rektor
ALTER TABLE participants ADD COLUMN nilai_tpa_total DECIMAL(5,2) NULL;
ALTER TABLE participants ADD COLUMN nilai_bidang_total DECIMAL(5,2) NULL;

-- NEW TABLES FOR ASSESSMENT
CREATE TABLE assessment_components (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    prodi_id VARCHAR(10) NULL, -- NULL = Global (TPA), Value = Custom Prodi (Bidang)
    type VARCHAR(20) NOT NULL, -- 'TPA' or 'BIDANG'
    nama_komponen VARCHAR(100) NOT NULL, -- e.g. "Structure", "Wawancara"
    bobot_persen INTEGER DEFAULT 0, -- Optional weighting
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE assessment_scores (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    participant_id INTEGER NOT NULL,
    component_id INTEGER NOT NULL,
    score DECIMAL(5,2) DEFAULT 0,
    created_by VARCHAR(50), -- Auditing who input the score
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(participant_id) REFERENCES participants(id),
    FOREIGN KEY(component_id) REFERENCES assessment_components(id)
);

CREATE TABLE prodi_configs (
    kode_prodi VARCHAR(10) PRIMARY KEY,
    jenjang VARCHAR(5) NOT NULL, -- 'S2' or 'S3'
    min_tpa DECIMAL(5,2) DEFAULT 450, -- Def 450 (S2) or 500 (S3)
    min_bidang DECIMAL(5,2) DEFAULT 0
);

CREATE TABLE prodi_quotas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    semester_id INTEGER NOT NULL,
    kode_prodi VARCHAR(10) NOT NULL,
    daya_tampung INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(semester_id) REFERENCES semesters(id),
    UNIQUE(semester_id, kode_prodi)
);
```

### Phase 2: Update Logika Import (Sync)
Memastikan import data dari server utama memetakan status dengan benar.
- Jika data server = "Lulus Verifikasi Online", update local `status_berkas` = 'lulus'.
- Set `status_pembayaran` = 1 (Otomatis Lunas).
- **JANGAN** menyentuh `status_verifikasi_fisik` (Default tetap pending sampai diverifikasi manual).

### Phase 3: Modul Verifikasi Fisik (Strict Mode)
Update controller `DocumentVerificationController`:
- Saat tombol "Simpan Validasi" ditekan:
    - Cek semua checklist (KTP, Ijazah, Transkrip, dll).
    - Jika semua dicentang -> Set `status_verifikasi_fisik` = 'lengkap'.
    - Jika ada yang kurang -> Set = 'tidak_lengkap'.
- Tambahkan filter di Dashboard: "Sudah Verifikasi Online, Belum Verifikasi Fisik".

### Phase 4: Restriksi Kartu Ujian
Update logic di `ParticipantController` (download card) dan `view.php`:
- `canDownloadCard()` return true **HANYA JIKA** `status_verifikasi_fisik == 'lengkap'`.
- Tampilkan pesan error jika user mencoba download tapi status fisik belum lengkap.

### Phase 5: Modul Penilaian (Assessments)
**A. Konfigurasi Komponen & Threshold**
- Superadmin/Admin: Setup komponen TPA global (Misal: Listening, Reading, Structure).
- Admin Prodi: Setup komponen Tes Bidang khusus prodi (Misal: Wawancara, Review Proposal).
- Konfigurasi Passing Grade:
  - S2: Min TPA 450.
  - S3: Min TPA 500.
  - Prodi: Min Tes Bidang (Custom).

**B. Input Nilai**
- **TPA**: Hanya bisa diinput oleh Superadmin/Admin.
- **Tes Bidang**: Bisa diinput oleh Admin Prodi (untuk pesertanya sendiri) atau Superadmin.
- **Auto-Calculation**: System menjumlahkan skor komponen menjadi Total TPA & Total Bidang.

### Phase 6: Kelulusan Akhir (Subjective Decision & Quota)
Modul untuk Rapat Pimpinan (Sortable Table & Batch Action):

1.  **Quota Management**:
    -   Admin Input "Daya Tampung" per Prodi untuk Semester Aktif.

2.  **Graduation Board (Rapat Lulus)**:
    -   **Tampilan**: Tabel Peserta per Prodi, diurutkan berdasarkan Total Score (TPA + Bidang).
    -   **Indikator**: 
        -   Kolom "Status Eligible" (Hijau/Merah) berdasarkan Threshold TPA/Bidang/Fisik.
        -   Kolom "Score Rank" (Ranking skor di prodi tersebut).
    -   **Selection Logic**:
        -   Default CHECK "Lulus" untuk Top N peserta (N = Daya Tampung), asalkan Eligible.
        -   **Override**: Pimpinan bisa UNCHECK peserta eligible (Gagal), atau CHECK peserta not-eligible (Lulus Bersyarat/Kebijakan).
    -   **Validation**: Warning jika jumlah Lulus > Daya Tampung.

3.  **Final Execution**:
    -   Save "Final Verification". Status DB Update -> `status_kelulusan_akhir`.
    -   **Export Excel**: Generate data final untuk BAAK (Format SK Rektor).
    -   Publish: Toggle "Umumkan" agar peserta bisa lihat di dashboard.

---

## 3. Langkah Verifikasi (Testing Plan)

### A. Verifikasi Alur Pemberkasan
1. **Import Data:** Import peserta baru. Pastikan `status_berkas` = Lulus (jika dari server lulus), tapi `status_verifikasi_fisik` = Pending.
2. **Cek Kartu:** Login sebagai peserta (atau simulasi link download). Pastikan **TIDAK BISA** download kartu.

### B. Verifikasi Dokumen Fisik
1. **Lakukan Verifikasi:** Login sebagai UPKH/Admin. Buka menu Verifikasi Fisik user tersebut.
2. **Set Lengkap:** Centang semua dan Simpan.
3. **Cek Database:** Pastikan `status_verifikasi_fisik` berubah jadi 'lengkap'.
4. **Cek Kartu:** Coba download kartu lagi. Harusnya **BISA**.

### C. Verifikasi Kelulusan Akhir
1. **Input Kelulusan:** Buka modul Kelulusan Akhir. Set user tersebut "LULUS" + No SK.
2. **Cek Dashboard:** Pastikan statistik mahasiswa diterima bertambah.
3. **View Peserta:** Di halaman detail peserta, muncul status "DITERIMA SEBAGAI MAHASISWA".

---

## 4. User Approval Required
Apakah rencana restriksi download kartu ujian (wajib lolos verifikasi fisik) sudah sesuai? Ini akan mengubah behavior sistem yang sekarang mungkin membolehkan download kartu lebih awal.
