<?php

namespace App\Controllers;

use App\Models\EmailTemplate;
use App\Utils\View;
use Leaf\Http\Request;

class EmailTemplateController
{
    private function checkAuth()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin');
            exit;
        }
    }

    public function index()
    {
        $this->checkAuth();

        $templates = EmailTemplate::all();

        echo View::render('admin.email.templates', [
            'templates' => $templates
        ]);
    }

    public function create()
    {
        $this->checkAuth();

        $data = [
            'name' => Request::get('name'),
            'subject' => Request::get('subject'),
            'body' => Request::get('body', false), // Don't sanitize HTML
            'description' => Request::get('description')
        ];

        EmailTemplate::create($data);

        header('Location: /admin/email/templates?success=created');
        exit;
    }

    public function update($id)
    {
        $this->checkAuth();

        $data = [
            'name' => Request::get('name'),
            'subject' => Request::get('subject'),
            'body' => Request::get('body', false), // Don't sanitize HTML
            'description' => Request::get('description')
        ];

        EmailTemplate::update($id, $data);

        header('Location: /admin/email/templates?success=updated');
        exit;
    }

    public function delete($id)
    {
        $this->checkAuth();

        EmailTemplate::delete($id);

        header('Location: /admin/email/templates?success=deleted');
        exit;
    }

    public function get($id)
    {
        $this->checkAuth();

        $template = EmailTemplate::find($id);

        header('Content-Type: application/json');
        echo json_encode($template);
        exit;
    }
}
