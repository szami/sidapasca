# Implementasi Role-Based Access Control (RBAC) Baru

## 1. Analisis Role & Menu (Rekomendasi)

Berikut adalah rekomendasi akses menu berdasarkan data yang ada:

### **A. Superadmin (IT / Manajer)**
*   **Akses**: FULL ACCESS (Semua Menu).
*   **Fungsi Utama**: Manajemen Sistem, User, Error Fix, Override data.

### **B. Admin (Staff Operasional Pasca)**
*   **Akses**: Hampir setara Superadmin untuk operasional, TAPI terbatas pada area teknis (Update System).
*   **Menu**:
    *   **Dashboard**
    *   **Admisi Pasca**: Formulir Masuk, Lulus Berkas, Gagal Berkas, Verifikasi Berkas (Full).
    *   **Peserta Ujian**: Data Peserta (Edit/Update Dokumen), Laporan Admisi.
    *   **Tools**: Import Data, Import Berkas, Export Data, Download Berkas.
    *   **Master Data**: Semester, Ruang Ujian, Sesi Ujian.
    *   **Exam Management**: Jadwalkan Ujian, Kehadiran, Cetak Jadwal, Cetak Daftar Hadir.
    *   **Komunikasi**: Email Config, Template, Reminder.
    *   **Settings**: Desain Kartu, Pengaturan.
    *   **Akun**: Ganti Password.

### **C. UPKH (Unit Pelayanan / Verifikasi Fisik)**
*   **Fokus**: Verifikasi berkas fisik dan pelayanan cetak kartu/formulir di loket.
*   **Menu**:
    *   **Dashboard**
    *   **Admisi Pasca**:
        *   Formulir Masuk (View Only - untuk cek data)
        *   **Verifikasi Berkas** (Full Access - Core Task)
    *   **Peserta Ujian**:
        *   Data Peserta (View Only + Akses Tombol Cetak Kartu/Formulir).
    *   **Tools**:
        *   Download Berkas (Opsional, jika perlu cek softcopy). -> *Rekomendasi: YA*.
    *   **Laporan**: Laporan Checklist Fisik.
    *   **Akun**: Ganti Password.

### **D. TU (Tata Usaha)**
*   **Fokus**: Penjadwalan, Surat Menyurat (Laporan), Absensi.
*   **Menu**:
    *   **Dashboard**
    *   **Master Data**: Ruang Ujian, Sesi Ujian (Edit/Create).
    *   **Exam Management**:
        *   **Jadwalkan Ujian** (Full Access).
        *   Kehadiran Ujian.
        *   Cetak Jadwal.
        *   Cetak Daftar Hadir.
    *   **Laporan**: Laporan Admisi, Laporan Jadwal.
    *   **Akun**: Ganti Password.
    *   *(Note: Tidak akses Verifikasi Berkas atau Edit Data Peserta)*.

### **E. Admin Prodi**
*   **Fokus**: Monitoring pendaftar prodi sendiri.
*   **Menu**:
    *   **Dashboard** (Statistik Prodi)
    *   **Data Prodi** (Alias "Admisi Pasca" tapi terbatas):
        *   Lulus Berkas (List peserta lolos prodi ybs).
        *   **Formulir Masuk** (List pendaftar baru, View Only).
    *   **Tools**:
        *   **Download Berkas** (ZIP - Hanya Prodi ybs).
    *   **Laporan**: Laporan Rekap Admisi (Prodi ybs).
    *   **Akun**: Ganti Password.

---

### **F. Ringkasan Matrix Akses Menu**

| Menu / Fitur | Superadmin 🛠️ | Admin 💼 | UPKH 🗂️ | TU 📅 | Admin Prodi 🎓 |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **Dashboard** | ✅ | ✅ | ✅ | ✅ | ✅ (Prodi) |
| **Formulir & Data Peserta (Lihat)** | ✅ | ✅ | ✅ | ✅ | ✅ (Prodi) |
| **Data Peserta (Edit/Upload)** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Verifikasi Berkas Fisik** | ✅ | ✅ | ✅ (Fokus) | ❌ | ❌ |
| **Download Berkas (ZIP)** | ✅ | ✅ | ✅ | ❌ | ✅ (Prodi) |
| **Import / Export Data** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Master Data (Ruang/Sesi)** | ✅ | ✅ | ❌ | ✅ | ❌ |
| **Penjadwalan Ujian** | ✅ | ✅ | ❌ | ✅ (Fokus) | ❌ |
| **Cetak Kartu & Formulir** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Cetak Jadwal & Absen** | ✅ | ✅ | ❌ | ✅ | ❌ |
| **Email & Komunikasi** | ✅ | ✅ | ❌ | ✅? | ❌ |
| **Laporan Admisi** | ✅ | ✅ | ❌ | ✅ | ✅ (Prodi) |
| **Laporan Checklist Fisik** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Pengaturan Sistem** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Manajemen User** | ✅ | ❌ | ❌ | ❌ | ❌ |

---

## 2. Rencana Teknis

### A. Update `App\Utils\RoleHelper`
*   Define Constants: `ROLE_UPKH = 'upkh'`, `ROLE_TU = 'tu'`.
*   Method `canValidatePhysical()`: True for `Superadmin`, `Admin`, `UPKH`.
*   Method `canManageSchedule()`: True for `Superadmin`, `Admin`, `TU`.
*   Method `canManageUsers()`: True for `Superadmin` only.

### B. Update Sidebar (`layouts/admin.php`)
*   Refactor tampilan sidebar dengan Logic Block yang lebih bersih berdasarkan Role di atas.

### C. Restrictions di Controller
*   **DocumentVerificationController**: Tambahkan `if (!RoleHelper::canValidatePhysical()) redirect...`
*   **ExamSchedulerController**: Tambahkan `if (!RoleHelper::canManageSchedule()) redirect...`
*   **ParticipantController**:
    *   `uploadDocument`: Hanya Admin/Superadmin.
    *   `update`: Hanya Admin/Superadmin.

---

## 3. Saran Pengembangan Sistem (Future Improvements)

### A. Keamanan (Security)
1.  **CSRF Protection**: Implementasi token CSRF pada semua form POST untuk mencegah serangan Cross-Site Request Forgery.
2.  **Rate Limiting**: Batasi jumlah percobaan login gagal untuk mencegah brute force (misal: max 5x gagal, freeze 15 menit).
3.  **Session & Cookie Security**: Pastikan cookie menggunakan flag `HttpOnly` dan `Secure` (jika HTTPS).
4.  **Audit Trail / Activity Log**: Catat setiap aksi penting (Edit Peserta, Hapus, Jadwalkan) lengkap dengan `user_id`, `timestamp`, `ip_address`, dan `action_details`. Ini krusial untuk tracking insiden operasional.

### B. Performa & Skalabilitas (Performance)
1.  **Server-side DataTables**: Saat ini `DataTables` melakukan load semua data peserta (misal: 1000+) baru dipaging di browser. Sebaiknya ubah menjadi Server-side Processing agar hanya mengambil 10-25 data per halaman saja. Ini akan sangat mempercepat loading saat data mencapai ribuan.
2.  **Caching**: Implementasi caching sederhana (Redis atau File Cache) untuk data yang jarang berubah seperti `Semesters`, `Prodi List`, atau `Exam Rooms`.

### C. Fitur & Fungsionalitas (Features)
1.  **Notifikasi Real-time**: Gunakan WebSockets (opsional) atau Polling untuk memberitahu Admin/UPKH jika ada peserta baru mendaftar atau melakukan pembayaran, tanpa perlu refresh halaman.
2.  **Validasi Dokumen Digital**: Tambahkan fitur preview langsung di modal saat klik nama file, tanpa harus download dulu.
3.  **Bulk Action**: Fitur untuk melakukan aksi massal (misal: Set Lulus Berkas untuk 50 peserta sekaligus).
4.  **Mobile API**: Pisahkan logika backend menjadi API murni agar siap jika kedepannya akan dibuat aplikasi Android/iOS untuk peserta atau tim verifikasi.

### D. Arsitektur & Database
1.  **Database Migration Manager**: Saat ini migrasi masih manual via `SetupController`. Sebaiknya gunakan tool migrasi bawaan framework (jika ada) atau Phinx untuk version control struktur database.
2.  **Foreign Key Constraints**: Pastikan integritas data level database (semoga SQLite support foreign key diaktifkan) agar tidak ada *orphan data* (misal: nilai ujian tanpa peserta).
