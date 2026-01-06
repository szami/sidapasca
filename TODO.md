# Task: Sistem Manajemen Berita/Informasi, Panduan Role & Reorganisasi Halaman Peserta

## 📋 Overview
Membuat sistem manajemen berita/informasi dengan Summernote editor (support image-only atau image+text) dan sistem panduan per role yang dapat diakses oleh semua role. Halaman peserta akan direorganisasi menjadi multi-tab dengan informasi yang lebih terstruktur.

**New Features:**
- 📰 News Management dengan opsi: Text+Image atau Image-Only
- 📚 Guide Management per Role (Superadmin, Admin, Admin Prodi, Participant)
- 📑 Multi-tab Participant Dashboard

---

## 🗂️ Database Design

### [ ] 1. Buat Tabel `news`
**File:** `database/migrations/create_news_table.sql`

```sql
CREATE TABLE IF NOT EXISTS news (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    content TEXT, -- HTML dari Summernote (nullable untuk image-only)
    content_type TEXT DEFAULT 'text_image', -- text_image, image_only
    image_url TEXT, -- Path ke gambar utama
    category TEXT DEFAULT 'umum', -- umum, pengumuman, informasi, dll
    is_published BOOLEAN DEFAULT 0,
    published_at DATETIME,
    created_by TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

**Kolom:**
- `id` - Primary key
- `title` - Judul berita
- `content` - Konten HTML dari Summernote (nullable untuk image-only)
- `content_type` - Tipe konten: `text_image` atau `image_only`
- `image_url` - Path ke gambar utama (untuk image-only mode)
- `category` - Kategori berita (umum, pengumuman, informasi)
- `is_published` - Status publish (0 = draft, 1 = published)
- `published_at` - Tanggal publish
- `created_by` - Username admin yang membuat
- `created_at` - Timestamp created
- `updated_at` - Timestamp updated

---

### [ ] 1b. Buat Tabel `guides`
**File:** `database/migrations/create_guides_table.sql`

```sql
CREATE TABLE IF NOT EXISTS guides (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    content TEXT NOT NULL, -- HTML dari Summernote
    role TEXT NOT NULL, -- superadmin, admin, admin_prodi, participant
    order_index INTEGER DEFAULT 0, -- Urutan tampilan
    is_active BOOLEAN DEFAULT 1,
    created_by TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

**Kolom:**
- `id` - Primary key
- `title` - Judul panduan
- `content` - Konten HTML dari Summernote
- `role` - Target role: `superadmin`, `admin`, `admin_prodi`, `participant`
- `order_index` - Urutan tampilan (untuk sorting)
- `is_active` - Status aktif (0 = inactive, 1 = active)
- `created_by` - Username admin yang membuat
- `created_at` - Timestamp created
- `updated_at` - Timestamp updated

---

## 🔧 Backend Implementation

### [ ] 2. Buat Model `News`
**File:** `app/Models/News.php`

**Methods:**
- `all()` - Get all news
- `find($id)` - Get by ID
- `getPublished()` - Get published news only
- `create($data)` - Create new news
- `update($id, $data)` - Update news
- `delete($id)` - Delete news
- `publish($id)` - Publish news
- `unpublish($id)` - Unpublish news

---

### [ ] 2b. Buat Model `Guide`
**File:** `app/Models/Guide.php`

**Methods:**
- `all()` - Get all guides
- `find($id)` - Get by ID
- `getByRole($role)` - Get active guides by role (ordered by order_index)
- `create($data)` - Create new guide
- `update($id, $data)` - Update guide
- `delete($id)` - Delete guide
- `activate($id)` - Activate guide
- `deactivate($id)` - Deactivate guide
- `reorder($id, $newOrder)` - Update order_index

---

### [ ] 3. Buat Controller `NewsController`
**File:** `app/Controllers/NewsController.php`

**Methods:**
- `index()` - Admin: List all news (DataTables)
- `create()` - Admin: Show create form
- `store()` - Admin: Save new news (handle image upload)
- `edit($id)` - Admin: Show edit form
- `update($id)` - Admin: Update news (handle image upload)
- `delete($id)` - Admin: Delete news (delete image file)
- `publish($id)` - Admin: Publish news
- `apiData()` - Admin: DataTables JSON endpoint
- `getPublished()` - Public: Get published news for participants
- `uploadImage()` - Admin: Handle Summernote image upload

---

### [ ] 3b. Buat Controller `GuideController`
**File:** `app/Controllers/GuideController.php`

**Methods:**
- `index()` - Admin: List all guides (DataTables)
- `create()` - Admin: Show create form
- `store()` - Admin: Save new guide
- `edit($id)` - Admin: Show edit form
- `update($id)` - Admin: Update guide
- `delete($id)` - Admin: Delete guide
- `activate($id)` - Admin: Activate guide
- `deactivate($id)` - Admin: Deactivate guide
- `reorder()` - Admin: Reorder guides (drag & drop)
- `apiData()` - Admin: DataTables JSON endpoint
- `getByRole()` - Public: Get active guides by role
- `uploadImage()` - Admin: Handle Summernote image upload

---

### [ ] 4. Tambahkan Routes
**File:** `index.php`

```php
// Admin Routes - News
$app->get('/admin/news', 'NewsController@index');
$app->get('/admin/news/create', 'NewsController@create');
$app->post('/admin/news/store', 'NewsController@store');
$app->get('/admin/news/edit/{id}', 'NewsController@edit');
$app->post('/admin/news/update/{id}', 'NewsController@update');
$app->post('/admin/news/delete/{id}', 'NewsController@delete');
$app->post('/admin/news/publish/{id}', 'NewsController@publish');
$app->get('/admin/news/api-data', 'NewsController@apiData');
$app->post('/admin/news/upload-image', 'NewsController@uploadImage');

// Admin Routes - Guides
$app->get('/admin/guides', 'GuideController@index');
$app->get('/admin/guides/create', 'GuideController@create');
$app->post('/admin/guides/store', 'GuideController@store');
$app->get('/admin/guides/edit/{id}', 'GuideController@edit');
$app->post('/admin/guides/update/{id}', 'GuideController@update');
$app->post('/admin/guides/delete/{id}', 'GuideController@delete');
$app->post('/admin/guides/activate/{id}', 'GuideController@activate');
$app->post('/admin/guides/deactivate/{id}', 'GuideController@deactivate');
$app->post('/admin/guides/reorder', 'GuideController@reorder');
$app->get('/admin/guides/api-data', 'GuideController@apiData');
$app->post('/admin/guides/upload-image', 'GuideController@uploadImage');

// Public API (for participants)
$app->get('/api/news/published', 'NewsController@getPublished');
$app->get('/api/guides/role/{role}', 'GuideController@getByRole');
```

---

## 🎨 Admin Interface

### [ ] 5. Buat View: News Index (List)
**File:** `app/views/admin/news/index.php`

**Features:**
- DataTables dengan kolom: ID, Title, Category, Status, Published At, Actions
- Filter by category
- Filter by status (published/draft)
- Search by title
- Actions: Edit, Delete, Publish/Unpublish
- Button "Tambah Berita Baru"

---

### [ ] 6. Buat View: News Create/Edit Form
**File:** `app/views/admin/news/form.php`

**Form Fields:**
- Title (text input)
- Content Type (radio buttons):
  - `text_image` - Text + Image (default)
  - `image_only` - Image Only
- Category (select: Umum, Pengumuman, Informasi)
- Image Upload (file input)
  - For `text_image`: Optional main image
  - For `image_only`: Required single image
- Content (Summernote editor)
  - Hidden when `image_only` selected
  - Visible when `text_image` selected
- Status (checkbox: Publish immediately)
- Submit button

**JavaScript Logic:**
```javascript
// Toggle content editor based on content_type
$('input[name="content_type"]').change(function() {
    const contentType = $(this).val();
    if (contentType === 'image_only') {
        $('#content-editor').hide();
        $('#image-upload').attr('required', true);
        $('#image-label').text('Gambar Utama *');
    } else {
        $('#content-editor').show();
        $('#image-upload').attr('required', false);
        $('#image-label').text('Gambar Utama (Opsional)');
    }
});
```

**Summernote Configuration:**
```javascript
$('#content').summernote({
    height: 400,
    toolbar: [
        ['style', ['style']],
        ['font', ['bold', 'italic', 'underline', 'clear']],
        ['fontname', ['fontname']],
        ['color', ['color']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['table', ['table']],
        ['insert', ['link', 'picture']],
        ['view', ['fullscreen', 'codeview', 'help']]
    ],
    callbacks: {
        onImageUpload: function(files) {
            uploadImage(files[0], '/admin/news/upload-image');
        }
    }
});
```

---

### [ ] 6b. Buat View: Guide Index (List)
**File:** `app/views/admin/guides/index.php`

**Features:**
- DataTables dengan kolom: ID, Title, Role, Order, Status, Actions
- Filter by role
- Filter by status (active/inactive)
- Search by title
- Drag & drop reordering (sortable)
- Actions: Edit, Delete, Activate/Deactivate, Reorder
- Button "Tambah Panduan Baru"

---

### [ ] 6c. Buat View: Guide Create/Edit Form
**File:** `app/views/admin/guides/form.php`

**Form Fields:**
- Title (text input)
- Target Role (select: Superadmin, Admin, Admin Prodi, Participant)
- Order Index (number input)
- Content (Summernote editor)
- Status (checkbox: Active)
- Submit button

**Summernote Configuration:**
```javascript
$('#content').summernote({
    height: 500,
    toolbar: [
        ['style', ['style']],
        ['font', ['bold', 'italic', 'underline', 'clear']],
        ['fontname', ['fontname']],
        ['fontsize', ['fontsize']],
        ['color', ['color']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['height', ['height']],
        ['table', ['table']],
        ['insert', ['link', 'picture', 'video']],
        ['view', ['fullscreen', 'codeview', 'help']]
    ],
    callbacks: {
        onImageUpload: function(files) {
            uploadImage(files[0], '/admin/guides/upload-image');
        }
    }
});
```

---

### [ ] 7. Tambahkan Menu di Sidebar Admin
**File:** `app/views/layouts/admin.php`

**Menu Items:**
```html
<li class="nav-item">
    <a href="/admin/news" class="nav-link">
        <i class="nav-icon fas fa-newspaper"></i>
        <p>Berita & Informasi</p>
    </a>
</li>
<li class="nav-item">
    <a href="/admin/guides" class="nav-link">
        <i class="nav-icon fas fa-book"></i>
        <p>Panduan</p>
    </a>
</li>
```

---

### [ ] 7b. Tambahkan Button "Panduan" di Navbar
**File:** `app/views/layouts/admin.php` & `app/views/layouts/participant.php`

**Navbar Button:**
```html
<li class="nav-item">
    <a href="#" class="nav-link" data-toggle="modal" data-target="#guideModal">
        <i class="fas fa-question-circle"></i> Panduan
    </a>
</li>
```

**Guide Modal:**
```html
<div class="modal fade" id="guideModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Panduan Penggunaan</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="guideContent">
                <!-- Loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
// Load guides based on user role
$('#guideModal').on('show.bs.modal', function() {
    const role = '<?= $_SESSION["admin_role"] ?? "participant" ?>';
    fetch(`/api/guides/role/${role}`)
        .then(r => r.json())
        .then(guides => {
            let html = '';
            guides.forEach((guide, index) => {
                html += `
                    <div class="guide-item mb-4">
                        <h5>${index + 1}. ${guide.title}</h5>
                        <div class="guide-content">${guide.content}</div>
                    </div>
                `;
            });
            $('#guideContent').html(html || '<p class="text-muted">Belum ada panduan tersedia.</p>');
        });
});
</script>
```

---

## 👤 Participant Interface - Reorganisasi Halaman

### [ ] 8. Modifikasi Halaman Peserta Menjadi Multi-Tab
**File:** `app/views/participant/dashboard.php`

**Tab Structure:**
1. **Tab Biodata** - Data pribadi peserta
2. **Tab Dokumen** - Upload/view dokumen (foto, KTP, ijazah, transkrip, rekomendasi jika ada)
3. **Tab Informasi Berkas Fisik** - Status kelengkapan berkas fisik + Note UPKH
4. **Tab Berita/Informasi** - List berita yang dipublish
5. **Tab Hasil Seleksi** - Hasil seleksi (jika ada)

---

### [ ] 9. Implementasi Tab Biodata
**Content:**
- Nama Lengkap
- Email
- Nomor Peserta
- Program Studi
- Jalur Pendaftaran
- Status Pembayaran
- Status Verifikasi
- Informasi Ujian (tanggal, waktu, lokasi)

---

### [ ] 10. Implementasi Tab Dokumen
**Content:**
- List dokumen dengan status (✓ Uploaded / ✗ Belum)
- Preview dokumen (image/PDF.js)
- Upload button untuk setiap dokumen
- **Conditional:** Rekomendasi hanya muncul jika ada file

---

### [ ] 11. Implementasi Tab Informasi Berkas Fisik
**File:** Tambahkan field di tabel `participants`

**Migration:**
```sql
ALTER TABLE participants ADD COLUMN berkas_fisik_status TEXT DEFAULT 'belum_lengkap';
-- belum_lengkap, lengkap, kurang_lengkap
ALTER TABLE participants ADD COLUMN berkas_fisik_note TEXT;
-- Catatan dari petugas UPKH
```

**Content:**
- Status Kelengkapan: Badge (Lengkap/Belum Lengkap/Kurang Lengkap)
- Checklist dokumen fisik:
  - [ ] Foto 3x4 (2 lembar)
  - [ ] Fotokopi KTP
  - [ ] Fotokopi Ijazah S1 (legalisir)
  - [ ] Fotokopi Transkrip S1 (legalisir)
  - [ ] Ijazah S2 (untuk S3, legalisir)
  - [ ] Transkrip S2 (untuk S3, legalisir)
  - [ ] Surat Rekomendasi (jika ada)
- **Note UPKH:** Tampilkan jika ada catatan dari petugas
  ```html
  <?php if (!empty($p['berkas_fisik_note'])): ?>
      <div class="alert alert-info">
          <i class="fas fa-info-circle"></i> <strong>Catatan Petugas:</strong>
          <p><?= nl2br(htmlspecialchars($p['berkas_fisik_note'])) ?></p>
      </div>
  <?php endif; ?>
  ```

---

### [ ] 12. Implementasi Tab Berita/Informasi
**Content:**
- List berita yang published (dari API `/api/news/published`)
- Card layout dengan:
  - Title
  - Category badge
  - Published date
  - **For `text_image`:**
    - Main image (if exists)
    - Excerpt (first 150 chars)
    - "Baca Selengkapnya" button → Modal
  - **For `image_only`:**
    - Full image display
    - Click to enlarge → Modal
- Modal untuk full content:
  - **For `text_image`:** Summernote HTML rendering
  - **For `image_only`:** Full-size image

**HTML Structure:**
```html
<div class="tab-pane fade" id="tab-berita">
    <div class="row" id="newsContainer">
        <!-- Loaded via JavaScript -->
    </div>
</div>
```

**JavaScript:**
```javascript
// Fetch published news
fetch('/api/news/published')
    .then(r => r.json())
    .then(data => {
        let html = '';
        data.forEach(news => {
            if (news.content_type === 'image_only') {
                // Image-only card
                html += `
                    <div class="col-md-6 mb-4">
                        <div class="news-card card h-100">
                            <img src="${news.image_url}" class="card-img-top news-image-only" 
                                 alt="${news.title}" 
                                 onclick="showImageModal('${news.image_url}', '${news.title}')">
                            <div class="card-body">
                                <h5 class="card-title">${news.title}</h5>
                                <span class="badge news-category-badge">${news.category}</span>
                                <p class="news-date mt-2">
                                    <i class="far fa-calendar"></i> ${formatDate(news.published_at)}
                                </p>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                // Text + Image card
                html += `
                    <div class="col-md-6 mb-4">
                        <div class="news-card card h-100">
                            ${news.image_url ? `<img src="${news.image_url}" class="card-img-top" alt="${news.title}">` : ''}
                            <div class="card-body">
                                <h5 class="card-title">${news.title}</h5>
                                <span class="badge news-category-badge">${news.category}</span>
                                <p class="news-date">
                                    <i class="far fa-calendar"></i> ${formatDate(news.published_at)}
                                </p>
                                <p class="card-text">${truncate(stripHtml(news.content), 150)}</p>
                                <button class="btn btn-primary btn-sm" onclick="showNewsModal(${news.id})">
                                    Baca Selengkapnya
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }
        });
        $('#newsContainer').html(html || '<p class="col-12 text-muted">Belum ada berita.</p>');
    });

// Show image modal (for image-only news)
function showImageModal(imageUrl, title) {
    $('#imageModalTitle').text(title);
    $('#imageModalImg').attr('src', imageUrl);
    $('#imageModal').modal('show');
}

// Show news modal (for text+image news)
function showNewsModal(newsId) {
    fetch(`/api/news/${newsId}`)
        .then(r => r.json())
        .then(news => {
            $('#newsModalTitle').text(news.title);
            $('#newsModalContent').html(news.content);
            $('#newsModal').modal('show');
        });
}
```

**Modals:**
```html
<!-- Image Modal (for image-only) -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalTitle"></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="imageModalImg" src="" class="img-fluid" alt="">
            </div>
        </div>
    </div>
</div>

<!-- News Modal (for text+image) -->
<div class="modal fade" id="newsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newsModalTitle"></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="newsModalContent">
                <!-- HTML content from Summernote -->
            </div>
        </div>
    </div>
</div>
```

---

### [ ] 13. Implementasi Tab Hasil Seleksi
**File:** Tambahkan field di tabel `participants`

**Migration:**
```sql
ALTER TABLE participants ADD COLUMN hasil_seleksi TEXT DEFAULT 'belum_ada';
-- belum_ada, lulus, tidak_lulus, cadangan
ALTER TABLE participants ADD COLUMN hasil_seleksi_note TEXT;
-- Catatan hasil seleksi
ALTER TABLE participants ADD COLUMN hasil_seleksi_date DATETIME;
-- Tanggal pengumuman
```

**Content:**
- Status Hasil: Badge besar (Lulus/Tidak Lulus/Cadangan/Belum Ada)
- Tanggal Pengumuman
- Note/Keterangan (jika ada)
- Download Surat Kelulusan (jika lulus)

---

## 🔐 Admin: Manajemen Berkas Fisik & Hasil Seleksi

### [ ] 14. Tambahkan Form di Admin Participants Edit
**File:** `app/views/admin/participants/edit.php`

**Section 1: Berkas Fisik**
```html
<div class="card">
    <div class="card-header">
        <h3>Informasi Berkas Fisik</h3>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label>Status Kelengkapan</label>
            <select name="berkas_fisik_status" class="form-control">
                <option value="belum_lengkap">Belum Lengkap</option>
                <option value="lengkap">Lengkap</option>
                <option value="kurang_lengkap">Kurang Lengkap</option>
            </select>
        </div>
        <div class="form-group">
            <label>Catatan UPKH</label>
            <textarea name="berkas_fisik_note" class="form-control" rows="4"></textarea>
        </div>
    </div>
</div>
```

**Section 2: Hasil Seleksi**
```html
<div class="card">
    <div class="card-header">
        <h3>Hasil Seleksi</h3>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label>Status Hasil</label>
            <select name="hasil_seleksi" class="form-control">
                <option value="belum_ada">Belum Ada</option>
                <option value="lulus">Lulus</option>
                <option value="tidak_lulus">Tidak Lulus</option>
                <option value="cadangan">Cadangan</option>
            </select>
        </div>
        <div class="form-group">
            <label>Tanggal Pengumuman</label>
            <input type="datetime-local" name="hasil_seleksi_date" class="form-control">
        </div>
        <div class="form-group">
            <label>Catatan/Keterangan</label>
            <textarea name="hasil_seleksi_note" class="form-control" rows="4"></textarea>
        </div>
    </div>
</div>
```

---

### [ ] 15. Update ParticipantController
**File:** `app/Controllers/ParticipantController.php`

**Method `update()`:**
- Tambahkan handling untuk `berkas_fisik_status`, `berkas_fisik_note`
- Tambahkan handling untuk `hasil_seleksi`, `hasil_seleksi_date`, `hasil_seleksi_note`

---

## 🎨 UI/UX Enhancements

### [ ] 16. Design Tab Navigation
**Bootstrap Nav Tabs:**
```html
<ul class="nav nav-tabs nav-tabs-premium" id="participantTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#tab-biodata">
            <i class="fas fa-user"></i> Biodata
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#tab-dokumen">
            <i class="fas fa-folder"></i> Dokumen
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#tab-berkas-fisik">
            <i class="fas fa-clipboard-check"></i> Berkas Fisik
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#tab-berita">
            <i class="fas fa-newspaper"></i> Berita
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#tab-hasil">
            <i class="fas fa-trophy"></i> Hasil Seleksi
        </a>
    </li>
</ul>
```

---

### [ ] 17. Styling untuk News Cards
**CSS:**
```css
.news-card {
    border-radius: 12px;
    transition: transform 0.3s, box-shadow 0.3s;
    border: 1px solid #e0e0e0;
}

.news-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}

.news-category-badge {
    font-size: 0.75rem;
    padding: 4px 12px;
    border-radius: 20px;
}

.news-date {
    color: #6c757d;
    font-size: 0.85rem;
}
```

---

## ✅ Testing Checklist

### [ ] 18. Admin Testing
- [ ] **News Management:**
  - [ ] Create news dengan text+image mode
  - [ ] Create news dengan image-only mode
  - [ ] Edit news
  - [ ] Delete news (verify image file deleted)
  - [ ] Publish/unpublish news
  - [ ] Filter by category
  - [ ] Filter by status
  - [ ] Search news
  - [ ] Upload image via Summernote
- [ ] **Guide Management:**
  - [ ] Create guide untuk setiap role
  - [ ] Edit guide
  - [ ] Delete guide
  - [ ] Activate/deactivate guide
  - [ ] Reorder guides (drag & drop)
  - [ ] Filter by role
  - [ ] Search guides
  - [ ] Upload image via Summernote
- [ ] **Participant Management:**
  - [ ] Update berkas fisik status & note
  - [ ] Update hasil seleksi

### [ ] 19. Participant Testing
- [ ] View tab Biodata
- [ ] View tab Dokumen (termasuk rekomendasi jika ada)
- [ ] View tab Berkas Fisik (dengan note UPKH jika ada)
- [ ] **View tab Berita:**
  - [ ] View text+image news
  - [ ] View image-only news
  - [ ] Click "Baca Selengkapnya" (text+image)
  - [ ] Click image to enlarge (image-only)
  - [ ] Modal display correctly
- [ ] View tab Hasil Seleksi
- [ ] **View Panduan:**
  - [ ] Click "Panduan" button in navbar
  - [ ] Modal shows guides for participant role
  - [ ] HTML content renders correctly
- [ ] Responsive design (mobile/tablet)

### [ ] 20. Role Testing
- [ ] **Superadmin:**
  - [ ] Access all news & guides
  - [ ] View panduan superadmin
- [ ] **Admin:**
  - [ ] Access all news & guides
  - [ ] View panduan admin
- [ ] **Admin Prodi:**
  - [ ] Access news & guides
  - [ ] View panduan admin_prodi
  - [ ] Only see prodi-specific data
- [ ] **Participant:**
  - [ ] View published news only
  - [ ] View panduan participant
  - [ ] Only see own data

---

## 📦 Dependencies

### [ ] 21. Summernote Integration
**Files to add:**
```html
<!-- CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
```

---

## 📝 Documentation

### [ ] 22. Update README.md
- Dokumentasi fitur News Management
- Dokumentasi tab structure halaman peserta
- Screenshot admin interface
- Screenshot participant interface

### [ ] 23. Update CHANGELOG.md
```markdown
## [1.4.0] - 2026-01-07

### Added
- News Management System dengan Summernote editor
- Multi-tab participant dashboard (Biodata, Dokumen, Berkas Fisik, Berita, Hasil Seleksi)
- Informasi Berkas Fisik dengan catatan UPKH
- Hasil Seleksi management
- Conditional rekomendasi tab (hanya muncul jika ada file)

### Changed
- Reorganisasi halaman peserta menjadi tab-based navigation
- Enhanced document management dengan PDF.js viewer
```

---

## 🚀 Deployment Steps

### [ ] 24. Run Migrations
```bash
# Run SQL migrations
sqlite3 storage/database.sqlite < database/migrations/create_news_table.sql
sqlite3 storage/database.sqlite < database/migrations/add_berkas_fisik_fields.sql
sqlite3 storage/database.sqlite < database/migrations/add_hasil_seleksi_fields.sql
```

### [ ] 25. Clear Cache (if any)
### [ ] 26. Test in Production Environment

---

## 📊 Priority Order

**Phase 1: Database & Backend (High Priority)**
1. Task 1: Create news table
2. Task 1b: Create guides table
3. Task 2: Create News model
4. Task 2b: Create Guide model
5. Task 3: Create NewsController
6. Task 3b: Create GuideController
7. Task 4: Add routes

**Phase 2: Admin Interface (High Priority)**
8. Task 5: News index view
9. Task 6: News form view (with image-only support)
10. Task 6b: Guide index view
11. Task 6c: Guide form view
12. Task 7: Add sidebar menu (news & guides)
13. Task 7b: Add panduan button in navbar
14. Task 14: Add berkas fisik & hasil seleksi forms
15. Task 15: Update ParticipantController

**Phase 3: Participant Interface (Medium Priority)**
16. Task 8: Reorganize participant dashboard
17. Tasks 9-11: Implement Biodata, Dokumen, Berkas Fisik tabs
18. Task 12: Implement Berita tab (with image-only support)
19. Task 13: Implement Hasil Seleksi tab

**Phase 4: Polish & Testing (Medium Priority)**
20. Tasks 16-17: UI/UX enhancements
21. Tasks 18-20: Testing (news, guides, image-only)

**Phase 5: Documentation & Deployment (Low Priority)**
22. Task 21: Summernote integration
23. Tasks 22-23: Documentation updates
24. Tasks 24-26: Deployment

---

## 📌 Notes

**General:**
- Semua role (superadmin, admin, admin_prodi, participant) dapat melihat berita & panduan
- Admin dapat manage berita & panduan
- Participant hanya bisa view berita yang published & panduan sesuai role-nya

**News Features:**
- Support 2 mode: `text_image` (default) dan `image_only`
- Image-only: Hanya gambar tanpa konten text
- Text+image: Konten Summernote + optional main image
- Image upload via Summernote untuk inline images
- Main image upload via form untuk featured image

**Guide Features:**
- Panduan per role: superadmin, admin, admin_prodi, participant
- Sortable dengan drag & drop (order_index)
- Activate/deactivate untuk show/hide
- Accessible via navbar button (modal)
- Auto-load berdasarkan role user yang login

**Conditional Display:**
- Tab rekomendasi hanya muncul jika `rekomendasi_filename` tidak empty
- Note UPKH hanya muncul jika `berkas_fisik_note` tidak empty
- Hasil seleksi hanya muncul jika status bukan "belum_ada"
- Panduan hanya muncul jika ada guide aktif untuk role tersebut

**Image Storage:**
- News images: `/storage/news/`
- Guide images: `/storage/guides/`
- Summernote inline images: `/storage/uploads/`

---

## 🎯 Total Tasks: 32

- **Database:** 2 tasks (news, guides)
- **Backend:** 6 tasks (models, controllers, routes)
- **Admin Interface:** 8 tasks (views, forms, menus)
- **Participant Interface:** 6 tasks (tabs, modals)
- **UI/UX:** 2 tasks
- **Testing:** 3 tasks
- **Documentation:** 3 tasks
- **Deployment:** 2 tasks
