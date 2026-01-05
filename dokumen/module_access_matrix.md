# SIDA Pasca ULM - Matriks Modul & Akses Role

> **Terakhir Diperbarui:** 6 Januari 2026  
> **Versi Sistem:** 1.2.0

---

## Ringkasan Role

| Role | Kode | Deskripsi |
|------|------|-----------|
| **Super Admin** | `superadmin` | Akses penuh ke seluruh sistem |
| **Administrator** | `admin` | Operasional: import, upload, verifikasi (TIDAK bisa edit/hapus peserta) |
| **UPKH** | `upkh` | Verifikasi fisik, lihat data, download dokumen |
| **Tata Usaha** | `tu` | Penjadwalan ujian, kehadiran, cetak dokumen |
| **Admin Prodi** | `admin_prodi` | Data prodi sendiri + Input Nilai Bidang |

---

## Legend

| Simbol | Arti |
|--------|------|
| ✅ | Akses Penuh (CRUD) |
| 📤 | Upload/Manage Saja |
| 👁️ | View Only |
| ❌ | Tidak Ada Akses |
| 🔒 | Terbatas (prodi sendiri) |

---

## 1. Dashboard & Monitoring

| Modul | Superadmin | Admin | UPKH | TU | Admin Prodi |
|-------|:----------:|:-----:|:----:|:--:|:-----------:|
| Dashboard | ✅ | ✅ | ✅ | ✅ | 🔒 |
| Statistik Global | ✅ | ✅ | ✅ | ✅ | ❌ |
| Statistik Prodi | ✅ | ✅ | ✅ | ✅ | 🔒 |

---

## 2. Manajemen Peserta

| Modul | Superadmin | Admin | UPKH | TU | Admin Prodi |
|-------|:----------:|:-----:|:----:|:--:|:-----------:|
| Daftar Peserta | ✅ | 👁️ | 👁️ | 👁️ | 🔒 |
| Detail Peserta | ✅ | 👁️ | 👁️ | 👁️ | 🔒 |
| Edit Peserta | ✅ | ❌ | ❌ | ❌ | ❌ |
| Hapus Peserta | ✅ | ❌ | ❌ | ❌ | ❌ |
| Upload Foto/Dokumen | ✅ | 📤 | ❌ | ❌ | ❌ |
| Export Excel | ✅ | ✅ | ❌ | ❌ | 🔒 |

---

## 3. Import & Export Data

| Modul | Superadmin | Admin | UPKH | TU | Admin Prodi |
|-------|:----------:|:-----:|:----:|:--:|:-----------:|
| Import Data | ✅ | ✅ | ❌ | ❌ | ❌ |
| Auto Download Dokumen | ✅ | ✅ | ❌ | ❌ | ❌ |
| Download Dokumen ZIP | ✅ | ✅ | ✅ | ❌ | 🔒 |

---

## 4. Verifikasi Dokumen

| Modul | Superadmin | Admin | UPKH | TU | Admin Prodi |
|-------|:----------:|:-----:|:----:|:--:|:-----------:|
| Daftar Verifikasi | ✅ | ✅ | ✅ | ❌ | ❌ |
| Verifikasi Detail | ✅ | ✅ | ✅ | ❌ | ❌ |
| Import Template | ✅ | ✅ | ✅ | ❌ | ❌ |

---

## 5. Penjadwalan Ujian

| Modul | Superadmin | Admin | UPKH | TU | Admin Prodi |
|-------|:----------:|:-----:|:----:|:--:|:-----------:|
| Scheduler | ✅ | ✅ | ❌ | ✅ | ❌ |
| View per Ruang | ✅ | ✅ | ❌ | ✅ | ❌ |
| Assign/Unassign | ✅ | ✅ | ❌ | ✅ | ❌ |

---

## 6. Kehadiran & Absensi

| Modul | Superadmin | Admin | UPKH | TU | Admin Prodi |
|-------|:----------:|:-----:|:----:|:--:|:-----------:|
| Daftar Kehadiran | ✅ | ✅ | ❌ | ✅ | ❌ |
| Entry Kehadiran | ✅ | ✅ | ❌ | ✅ | ❌ |
| Cetak Daftar Hadir | ✅ | ✅ | ❌ | ✅ | ❌ |

---

## 7. Master Data

| Modul | Superadmin | Admin | UPKH | TU | Admin Prodi |
|-------|:----------:|:-----:|:----:|:--:|:-----------:|
| Ruang Ujian (CRUD) | ✅ | ✅ | ❌ | ✅ | ❌ |
| Sesi Ujian (CRUD) | ✅ | ✅ | ❌ | ✅ | ❌ |
| Semester (CRUD) | ✅ | ❌ | ❌ | ❌ | ❌ |

---

## 8. Cetak Dokumen

| Modul | Superadmin | Admin | UPKH | TU | Admin Prodi |
|-------|:----------:|:-----:|:----:|:--:|:-----------:|
| Kartu Ujian | ✅ | ✅ | ✅ | ✅ | ❌ |
| Formulir Pendaftaran | ✅ | ✅ | ✅ | ✅ | ❌ |
| Daftar Hadir | ✅ | ✅ | ❌ | ✅ | ❌ |
| Jadwal CAT | ✅ | ✅ | ❌ | ✅ | ❌ |
| Desain Kartu Ujian | ✅ | ✅ | ❌ | ❌ | ❌ |

---

## 9. Assessment & Nilai

| Modul | Superadmin | Admin | UPKH | TU | Admin Prodi |
|-------|:----------:|:-----:|:----:|:--:|:-----------:|
| Komponen Nilai (CRUD) | ✅ | ✅ | ❌ | ❌ | ❌ |
| Input Nilai TPA | ✅ | ✅ | ❌ | ❌ | ❌ |
| Input Nilai Bidang | ✅ | ✅ | ❌ | ❌ | 🔒 |
| Keputusan Akhir | ✅ | ✅ | ❌ | ❌ | ❌ |

---

## 10. Kelulusan

| Modul | Superadmin | Admin | UPKH | TU | Admin Prodi |
|-------|:----------:|:-----:|:----:|:--:|:-----------:|
| Daya Tampung Prodi | ✅ | ✅ | ❌ | ❌ | ❌ |
| Rapat Kelulusan | ✅ | ✅ | ❌ | ❌ | 👁️ |
| Eksekusi Kelulusan | ✅ | ✅ | ❌ | ❌ | ❌ |

---

## 11. Email & Komunikasi

| Modul | Superadmin | Admin | UPKH | TU | Admin Prodi |
|-------|:----------:|:-----:|:----:|:--:|:-----------:|
| Konfigurasi Email | ✅ | ❌ | ❌ | ❌ | ❌ |
| Template Email | ✅ | ✅ | ❌ | ✅ | ❌ |
| Kirim Reminder | ✅ | ✅ | ❌ | ✅ | ❌ |
| Riwayat Reminder | ✅ | ✅ | ❌ | ✅ | ❌ |

---

## 12. Pengaturan Sistem

| Modul | Superadmin | Admin | UPKH | TU | Admin Prodi |
|-------|:----------:|:-----:|:----:|:--:|:-----------:|
| Pengaturan Umum | ✅ | ✅ | ❌ | ❌ | ❌ |
| Backup/Restore DB | ✅ | ✅ | ❌ | ❌ | ❌ |
| Clean Semester | ✅ | ✅ | ❌ | ❌ | ❌ |

---

## 13. Manajemen User

| Modul | Superadmin | Admin | UPKH | TU | Admin Prodi |
|-------|:----------:|:-----:|:----:|:--:|:-----------:|
| Daftar User (CRUD) | ✅ | ❌ | ❌ | ❌ | ❌ |
| Ubah Password Sendiri | ✅ | ✅ | ✅ | ✅ | ✅ |

---

## 14. Sistem & Update

| Modul | Superadmin | Admin | UPKH | TU | Admin Prodi |
|-------|:----------:|:-----:|:----:|:--:|:-----------:|
| System Update | ✅ | ❌ | ❌ | ❌ | ❌ |

---

## Permission Methods (RoleHelper)

| Method | Roles |
|--------|-------|
| `canEditParticipant()` | Superadmin |
| `canDeleteParticipant()` | Superadmin |
| `canUploadDocuments()` | Superadmin, Admin |
| `canValidatePhysical()` | Superadmin, Admin, UPKH |
| `canManageSchedule()` | Superadmin, Admin, TU |
| `canManageUsers()` | Superadmin |
| `canImportExport()` | Superadmin, Admin |
| `canManageSettings()` | Superadmin, Admin |
| `canManageEmail()` | Superadmin, Admin, TU |
| `canPrintCards()` | Superadmin, Admin, UPKH |
| `canPrintSchedule()` | Superadmin, Admin, TU |
| `canManageMasterData()` | Superadmin, Admin, TU |
| `canDownloadDocuments()` | Superadmin, Admin, UPKH, Admin Prodi |
| `canViewReports()` | Superadmin, Admin, TU, Admin Prodi |
