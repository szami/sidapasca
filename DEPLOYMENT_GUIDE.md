# Panduan Deployment & Sinkronisasi (Dev vs Actual)

Dokumen ini menjelaskan cara aman memindahkan kode dari server **Development (devsida)** ke server **Actual (sidapasca-ulm)** tanpa merusak database atau menimpa file dokumen pendaftar.

## 1. Arsitektur Folder & Data
Meskipun kedua folder berada di server yang sama, pemisahan data dijamin oleh sistem **Git** melalui file `.gitignore`.

| Komponen | Status | Penjelasan |
| :--- | :--- | :--- |
| **Kode Program** | **Sinkron** | PHP, HTML, CSS, JS dikirim via Git. |
| **Database SQLite** | **Terpisah** | Folder `devsida` punya database sendiri, `sidapasca-ulm` punya sendiri. Tidak akan saling menimpa. |
| **File Dokumen** | **Terpisah** | Folder `storage/photos` dan `storage/documents` diabaikan oleh Git. |

## 2. Alur Penggunaan (Workflow)

### Tahap 1: Di Folder Development (`devsida`)
Gunakan folder ini untuk mencoba fitur baru, testing input, atau modifikasi tampilan.
1.  Buka [devsida.inovasidigital.link](https://devsida.inovasidigital.link).
2.  Setelah puas dengan perubahan, lakukan **Git Push** ke repository GitHub/GitLab.

### Tahap 2: Di Folder Actual (`sidapasca-ulm`)
Gunakan folder ini untuk publik/pendaftar asli.
1.  Gunakan tool [deploy.php](file:///c:/laragon/www/sidapasca-ulm/deploy.php) dengan cara akses URL: `https://sidapasca-ulm.inovasidigital.link/deploy.php?token=sidapasca_deploy_2026_xyz`.
2.  Script ini akan menarik kode terbaru dari Git.
3.  Jika ada perubahan struktur tabel, script ini otomatis menjalankan `migrate` ke database actual tersebut.

---

## 3. Cara Mencegah Data Tumpang Tindih

### A. Database (SQLite)
Sistem menggunakan file `storage/database.sqlite`. Karena file ini masuk dalam daftar `.gitignore`, maka:
*   Saat Anda `push` dari dev, file database Anda **tidak ikut terkirim**.
*   Saat Anda `pull` di actual, database pendaftar di server **tidak akan terhapus**.

> [!TIP]
> **Jika ingin menambah kolom baru:**
> Cukup buat file migrasi (atau update `migrate.php`). Saat Anda jalankan `deploy.php`, kolom baru akan ditambahkan ke database actual secara otomatis tanpa menghapus data yang sudah ada.

### B. File Dokumen (Foto, KTP, Ijazah)
Semua file yang diupload peserta tersimpan di folder `storage/`.
*   Folder `storage/photos/`, `storage/documents/`, dan `storage/20*/` sudah saya **Exclude** (kecualikan) dari Git.
*   Artinya, foto-foto testing di server Dev tidak akan muncul di server Actual, dan sebaliknya.

---

## 4. Checklist Keamanan Sebelum Update
1.  **Backup Database**: Meskipun sistem sudah melakukan backup otomatis ke `storage/backups` sebelum update, sangat disarankan mendownload file `database.sqlite` secara manual sesekali.
2.  **Cek .env**: Pastikan file `.env` di masing-masing folder memiliki pengaturan yang benar (misal: `APP_ENV` untuk dev diset `local`, untuk actual diset `production`).
3.  **Ubah Token**: Untuk keamanan ekstra, ubah variabel `DEPLOY_TOKEN` di file `deploy.php` di kedua server dengan kata kunci yang berbeda dan rahasia.

---
**SIDA PASCA - Advanced Synchronization Guide**
