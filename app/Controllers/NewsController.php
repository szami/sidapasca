<?php

namespace App\Controllers;

use App\Models\News;
use App\Utils\View;
use Leaf\Http\Request;

class NewsController
{
    public function index()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin');
            exit;
        }

        echo View::render('admin.news.index', ['title' => 'Manajemen Berita']);
    }

    public function create()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin');
            exit;
        }

        echo View::render('admin.news.form', ['title' => 'Tambah Berita']);
    }

    public function store()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin');
            exit;
        }

        $title = request()->get('title');
        $content = request()->get('content');
        $contentType = request()->get('content_type') ?? 'text_image';
        $category = request()->get('category') ?? 'umum';
        $isPublished = request()->get('is_published');

        $file = $_FILES['image'] ?? null;
        $imageUrl = null;

        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $imageUrl = $this->handleImageUpload($file);
        }

        $insertData = [
            'title' => $title,
            'content' => $content,
            'content_type' => $contentType,
            'category' => $category,
            'image_url' => $imageUrl,
            'is_published' => $isPublished ? 1 : 0,
            'published_at' => $isPublished ? date('Y-m-d H:i:s') : null,
            'created_by' => $_SESSION['admin_username'] ?? 'admin'
        ];

        News::create($insertData);
        header('Location: /admin/news');
        exit;
    }

    public function edit($id)
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin');
            exit;
        }

        $news = News::find($id);
        echo View::render('admin.news.form', ['news' => $news, 'title' => 'Edit Berita']);
    }

    public function update($id)
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin');
            exit;
        }

        $title = request()->get('title');
        $content = request()->get('content');
        $contentType = request()->get('content_type') ?? 'text_image';
        $category = request()->get('category') ?? 'umum';
        $isPublished = request()->get('is_published');
        $wasPublished = request()->get('was_published') == 1;

        $file = $_FILES['image'] ?? null;

        $updateData = [
            'title' => $title,
            'content' => $content,
            'content_type' => $contentType,
            'category' => $category,
        ];

        // Handle published status
        if ($isPublished) {
            $updateData['is_published'] = 1;
            if (!$wasPublished) {
                $updateData['published_at'] = date('Y-m-d H:i:s');
            }
        } else {
            $updateData['is_published'] = 0;
        }

        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $updateData['image_url'] = $this->handleImageUpload($file);
        }

        News::update($id, $updateData);
        header('Location: /admin/news');
        exit;
    }

    public function delete($id)
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin');
            exit;
        }

        $news = News::find($id);
        if ($news && $news['image_url']) {
            $this->deleteImage($news['image_url']);
        }

        News::delete($id);
        header('Location: /admin/news');
        exit;
    }

    public function apiData()
    {
        if (!isset($_SESSION['admin'])) {
            response()->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $news = News::all();
        response()->json(['data' => $news]);
    }

    public function getPublished()
    {
        // Public API for participants
        $news = News::getPublished();
        response()->json($news);
    }

    public function get($id)
    {
        // Public API for fetching single news content
        $news = News::find($id);
        if ($news && $news['is_published']) {
            response()->json($news);
        } else {
            response()->json(['error' => 'Not found or not published'], 404);
        }
    }

    public function uploadImage()
    {
        if (!isset($_SESSION['admin'])) {
            response()->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $file = $_FILES['file'] ?? null;
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $url = $this->handleImageUpload($file, 'content');
            response()->json($url); // Summernote expects just the URL string or specific JSON format? 
            // Summernote expects image URL as string usually.
            // But let's verify Summernote specific requirement if needed. 
            // Usually we return URL.
        } else {
            response()->json(['error' => 'Upload failed'], 400);
        }
    }

    private function handleImageUpload($file, $subfolder = 'covers')
    {
        $targetDir = dirname(__DIR__, 2) . "/public/uploads/news/$subfolder";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $filename = uniqid() . '_' . basename($file['name']);
        $targetPath = $targetDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return "/public/uploads/news/$subfolder/$filename";
        }
        return null;
    }

    private function deleteImage($url)
    {
        // URL is something like /public/uploads/news/covers/filename.jpg
        $path = dirname(__DIR__, 2) . $url;
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
