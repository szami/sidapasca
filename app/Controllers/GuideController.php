<?php

namespace App\Controllers;

use App\Models\Guide;
use App\Utils\View;
use Leaf\Http\Request;

class GuideController
{
    public function index()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin');
            exit;
        }

        echo View::render('admin.guides.index', ['title' => 'Manajemen Panduan']);
    }

    public function create()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin');
            exit;
        }

        echo View::render('admin.guides.form', ['title' => 'Tambah Panduan']);
    }

    public function store()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin');
            exit;
        }

        $insertData = [
            'title' => request()->get('title'),
            'content' => request()->get('content', false),
            'role' => request()->get('role'),
            'order_index' => request()->get('order_index') ?? 0,
            'is_active' => request()->get('is_active') ? 1 : 0,
            'created_by' => $_SESSION['admin_username'] ?? 'admin'
        ];

        Guide::create($insertData);
        header('Location: /admin/guides');
        exit;
    }

    public function edit($id)
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin');
            exit;
        }

        $guide = Guide::find($id);
        echo View::render('admin.guides.form', ['guide' => $guide, 'title' => 'Edit Panduan']);
    }

    public function update($id)
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin');
            exit;
        }

        $updateData = [
            'title' => request()->get('title'),
            'content' => request()->get('content', false),
            'role' => request()->get('role'),
            'order_index' => request()->get('order_index') ?? 0,
            'is_active' => request()->get('is_active') ? 1 : 0,
        ];

        Guide::update($id, $updateData);
        header('Location: /admin/guides');
        exit;
    }

    public function delete($id)
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin');
            exit;
        }

        Guide::delete($id);
        header('Location: /admin/guides');
        exit;
    }

    public function activate($id)
    {
        if (!isset($_SESSION['admin'])) {
            return;
        }
        Guide::activate($id);
        header('Location: /admin/guides');
    }

    public function deactivate($id)
    {
        if (!isset($_SESSION['admin'])) {
            return;
        }
        Guide::deactivate($id);
        header('Location: /admin/guides');
    }

    public function apiData()
    {
        if (!isset($_SESSION['admin'])) {
            response()->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $role = $_SESSION['admin_role'] ?? 'admin';
        $isAdmin = in_array($role, [\App\Utils\RoleHelper::ROLE_SUPERADMIN, \App\Utils\RoleHelper::ROLE_ADMIN]);

        if ($isAdmin) {
            $guides = Guide::all();
        } else {
            $guides = Guide::getByRole($role);
        }

        response()->json(['data' => $guides]);
    }

    public function getByRole($role)
    {
        $guides = Guide::getByRole($role);
        response()->json($guides);
    }

    public function uploadImage()
    {
        if (!isset($_SESSION['admin'])) {
            response()->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $file = $_FILES['file'] ?? null;
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $targetDir = dirname(__DIR__, 2) . "/public/uploads/guides";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $filename = uniqid() . '_' . basename($file['name']);
            $targetPath = $targetDir . '/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                response()->json("/public/uploads/guides/$filename");
            } else {
                response()->json(['error' => 'Move failed'], 500);
            }
        } else {
            response()->json(['error' => 'Upload failed'], 400);
        }
    }
}
