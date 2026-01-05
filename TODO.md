# TODO - v1.3.0 (Advanced Reporting & Analytics)

## 1. Modul Laporan Eksekutif
- [ ] **Laporan Statistik Pendaftar**: Breakdown berdasarkan Gender, Asal Universitas, IPK Asal, dan Prodi Pilihan.
- [ ] **Laporan Pembayaran**: Rekapitulasi status pembayaran `paid`, `unpaid`, dan total revenue per prodi.
- [ ] **Laporan Tingkat Kelulusan**: Rasio pendaftar vs yang lulus seleksi per gelombang/semester.

## 2. Enhanced Export System
- [ ] **Custom Excel Export**: User bisa memilih kolom apa saja yang ingin di-export (tidak hardcode semua kolom).
- [ ] **PDF Resmi**: Generate laporan siap cetak dengan Kop Surat Pascasarjana dan slot tanda tangan Direktur/Kaprodi.

## 3. Perbaikan & Optimasi (Technical Debt)
- [ ] **Audit Log**: Mencatat aktivitas user (siapa mengubah apa), terutama untuk perubahan nilai dan status kelulusan.
- [ ] **Security Hardening**: Review akses file upload agar terlindungi via route protection (tidak akses langsung ke `/public/storage` jika memungkinkan/diperlukan).
