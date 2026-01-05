# Tata Cara Penilaian Bidang (Admin Prodi)

## 1. Ringkasan
Modul penilaian bidang memungkinkan Admin Prodi untuk mengelola komponen penilaian dan memberikan nilai kepada peserta ujian sesuai dengan kriteria masing-masing program studi.

---

## 2. Akses Menu
- Login sebagai **Admin Prodi**
- Menu yang tersedia:
  - **Komponen Nilai** → Kelola komponen dan threshold
  - **Penilaian Bidang** → Input nilai peserta

---

## 3. Pengaturan Komponen Nilai

### 3.1 Membuat Komponen Baru
1. Buka menu **Komponen Nilai**
2. Klik tombol **Tambah Komponen**
3. Isi **Nama Komponen** (contoh: Tes Wawancara, Tes Tertulis)
4. Isi **Bobot %** (opsional, lihat perhitungan di bawah)
5. Klik **Simpan**

> **Catatan**: Admin Prodi hanya bisa membuat komponen tipe BIDANG untuk prodi sendiri.

### 3.2 Pengaturan Nilai Minimum (Threshold)
1. Di halaman **Komponen Nilai**, temukan card **Pengaturan Nilai Minimum Kelulusan**
2. Masukkan nilai minimum total untuk kelulusan
3. Klik **Simpan Pengaturan**

| Threshold | Perilaku |
|-----------|----------|
| 0 | Auto-suggest OFF - Tentukan status secara manual |
| > 0 | Auto-suggest ON - Status otomatis berdasarkan total nilai |

---

## 4. Perhitungan Nilai Akhir

### 4.1 Tanpa Bobot (Semua bobot = 0)
```
Nilai Akhir = Komponen1 + Komponen2 + ... + KomponenN
```

**Contoh:**
| Komponen | Nilai |
|----------|-------|
| Wawancara | 80 |
| Tes Tulis | 90 |
| **Total** | **170** |

### 4.2 Dengan Bobot
```
Nilai Akhir = (Komponen1 × Bobot1%) + (Komponen2 × Bobot2%) + ...
```

**Contoh (Bobot total 100%):**
| Komponen | Nilai | Bobot | Kontribusi |
|----------|-------|-------|------------|
| Wawancara | 80 | 40% | 32 |
| Tes Tulis | 90 | 60% | 54 |
| **Total** | - | 100% | **86** |

---

## 5. Input Nilai Peserta

### 5.1 Langkah-langkah
1. Buka menu **Penilaian Bidang**
2. Daftar peserta prodi Anda akan tampil (semester aktif)
3. Klik tombol **Input Nilai** pada baris peserta
4. Isi nilai untuk setiap komponen
5. Pilih **Status Rekomendasi** (jika threshold = 0)
6. Klik **Simpan**

### 5.2 Status Rekomendasi
- **Disarankan Lulus** → `status_tes_bidang = lulus`
- **Tidak Disarankan** → `status_tes_bidang = tidak_lulus`

### 5.3 Auto-Suggest (jika threshold > 0)
Setelah nilai disimpan:
- Total nilai ≥ Threshold → Status otomatis = **Lulus**
- Total nilai < Threshold → Status otomatis = **Tidak Lulus**

> **Override Manual**: Anda tetap bisa mengubah status via radio button meskipun auto-suggest aktif.

---

## 6. Import/Export Nilai

### 6.1 Download Template Excel
1. Di halaman **Penilaian Bidang**, klik **Template Excel**
2. File Excel berisi kolom: Nomor Peserta, Nama, [Komponen1], [Komponen2], ..., Status Rekomendasi

### 6.2 Import Nilai dari Excel
1. Isi nilai di file template
2. Klik **Import Excel**
3. Upload file
4. Nilai akan diproses dan disimpan

---

## 7. Alur Keputusan Akhir

```
┌─────────────────────────────────────────────────────────────┐
│                      ALUR PENILAIAN                        │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Admin Prodi Input Nilai Komponen                           │
│           ↓                                                 │
│  Sistem Hitung Total (Sum/Weighted)                         │
│           ↓                                                 │
│  Threshold > 0? ──── Ya ──→ Auto-set status_tes_bidang     │
│       │                                                     │
│      Tidak                                                  │
│       ↓                                                     │
│  Admin Prodi Pilih Status Manual                            │
│           ↓                                                 │
│  status_tes_bidang tersimpan (lulus/tidak_lulus)            │
│           ↓                                                 │
│  Admin Pusat Review di Rapat Kelulusan                      │
│           ↓                                                 │
│  Keputusan Akhir (status_kelulusan_akhir)                   │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 8. FAQ

**Q: Saya tidak bisa melihat menu Rapat Kelulusan?**
A: Menu tersebut hanya untuk Admin Pusat/Superadmin. Tugas Anda sebagai Admin Prodi adalah memberikan rekomendasi.

**Q: Bagaimana jika saya ingin mengubah nilai yang sudah disimpan?**
A: Klik **Input Nilai** pada peserta yang sama, ubah nilai, lalu simpan ulang.

**Q: Apa bedanya status_tes_bidang dan status_kelulusan_akhir?**
A: `status_tes_bidang` = rekomendasi dari Admin Prodi. `status_kelulusan_akhir` = keputusan final dari Rapat Kelulusan.

---

*Dokumen ini dibuat: 5 Januari 2026*
