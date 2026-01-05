# Walkthrough: Modul Penilaian & Kelulusan

Dokumen ini menjelaskan cara menggunakan fitur Penilaian (Assessment) dan Kelulusan Akhir pada sistem PMB Pascasarjana.

## 1. Modul Penilaian (Assessment)

### A. Komponen Penilaian
Admin dapat mengatur komponen penilaian melalui menu **Assessment > Komponen Nilai**.
- **Tipe TPA**: Komponen global (misal: "Score TPA").
- **Tipe Bidang**: Komponen khusus Prodi (misal: "Wawancara", "Review Proposal"). Bisa diberi bobot (persen).

### B. Input Nilai
Menu: **Assessment > Input Nilai**.
1. **Filter Peserta**: Pilih Semester dan Prodi.
2. **Input**: Klik "Input Nilai" pada peserta.
3. **Tab Nilai TPA**: Hanya dapat diakses oleh Superadmin/Admin.
4. **Tab Nilai Bidang**:
   - Jika Prodi Anda memiliki rincian komponen, isi nilai angka (0-100).
   - **WAJIB**: Isi "Rekomendasi Akhir Tes Bidang" (LULUS / TIDAK LULUS).
   - Status ini bersifat krusial. Jika "TIDAK LULUS", peserta otomatis gugur.

---

## 2. Modul Kelulusan (Graduation)

### A. Manajemen Kuota
Menu: **Kelulusan > Daya Tampung**.
- Atur "Daya Tampung" per Prodi untuk semester aktif.
- Angka ini digunakan sebagai batas default (Cut-off rank) pada rapat kelulusan.

### B. Rapat Kelulusan (Board)
Menu: **Kelulusan > Rapat Kelulusan**.
Halaman ini adalah dashboard utama untuk penentuan kelulusan akhir.

**Fitur Utama:**
1. **Ranking Otomatis**: Peserta diurutkan berdasarkan Total Score (TPA + Bidang) per Prodi.
2. **Indikator Status**:
   - **ELIGIBLE (Hijau)**: Peserta lulus Verifikasi Fisik, TPA >= Threshold (450/500), dan Lulus Tes Bidang.
   - **NOT ELIGIBLE (Merah)**: Tidak memenuhi salah satu syarat vital. Hover pada badge untuk melihat alasannya.
3. **Seleksi Default**:
   - Sistem secara otomatis mencentang peserta ELIGIBLE yang masuk dalam kuota (Ranking teratas).
   - Peserta di luar kuota (Waiting List) tidak dicentang.
4. **Manual Override (Keputusan Pimpinan)**:
   - Anda dapat **MENCENTANG** manual peserta yang "Not Eligible" (misal: Nilai TPA kurang sedikit tapi direkomendasikan Prodi).
   - Anda dapat **MENGHAPUS CENTANG** peserta yang Eligible (Gagal karena alasan lain).

### C. Eksekusi Final
1. Masukkan **Nomor SK Kelulusan** pada kolom input di atas tabel.
2. Klik tombol **"Proses Kelulusan Final"**.
3. Sistem akan menyimpan status akhir:
   - Peserta Tercentang -> **LULUS**.
   - Peserta Tidak Tercentang -> **TIDAK LULUS**.
4. Gunakan tombol **"Export SK"** untuk mengunduh daftar lulusan dalam format CSV untuk BAAK.

---

## Catatan Penting
- **Strict Logic**: Peserta dengan `status_verifikasi_fisik` != 'lengkap' atau `status_tes_bidang` == 'tidak_lulus' secara default ditandai MERAH (Not Eligible). Override manual tetap dimungkinkan namun harus dengan pertimbangan matang.
