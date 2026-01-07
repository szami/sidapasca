CREATE TABLE IF NOT EXISTS news (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    content TEXT, -- HTML from Summernote (nullable for image-only)
    content_type TEXT DEFAULT 'text_image', -- text_image, image_only
    image_url TEXT, -- Path to main image
    category TEXT DEFAULT 'umum', -- umum, pengumuman, informasi, etc
    is_published BOOLEAN DEFAULT 0,
    published_at DATETIME,
    created_by TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
