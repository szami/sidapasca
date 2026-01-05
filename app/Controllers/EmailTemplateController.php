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
            // 'body' => Request::get('body', false), // Don't sanitize HTML
            'body' => $_POST['body'] ?? '', // Use raw POST to ensure HTML is preserved and avoid type errors
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
            // 'body' => Request::get('body', false), // Don't sanitize HTML
            'body' => $_POST['body'] ?? '', // Use raw POST to ensure HTML is preserved and avoid type errors
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
    public function apiData()
    {
        $this->checkAuth();

        $db = \App\Utils\Database::connection();

        // DataTables parameters
        $draw = intval(Request::get('draw') ?? 1);
        $start = intval(Request::get('start') ?? 0);
        $length = intval(Request::get('length') ?? 10);
        $search = Request::get('search')['value'] ?? '';
        $orderColumnIndex = Request::get('order')[0]['column'] ?? 0;
        $orderDir = Request::get('order')[0]['dir'] ?? 'asc';

        $columns = [
            0 => 'id',
            1 => 'name',
            2 => 'subject',
            3 => 'description',
        ];
        $orderBy = $columns[$orderColumnIndex] ?? 'id';

        // Base WHERE
        $whereClause = "WHERE 1=1";

        // Search
        if (!empty($search)) {
            $searchEscaped = str_replace("'", "''", $search);
            $whereClause .= " AND (name LIKE '%$searchEscaped%' 
                             OR subject LIKE '%$searchEscaped%'
                             OR description LIKE '%$searchEscaped%')";
        }

        $totalRecordsSql = "SELECT COUNT(*) as total FROM email_templates";
        $totalRes = $db->query($totalRecordsSql)->fetchAssoc();
        $totalRecords = $totalRes['total'] ?? 0;

        $filteredRecordsSql = "SELECT COUNT(*) as total FROM email_templates $whereClause";
        $filteredRes = $db->query($filteredRecordsSql)->fetchAssoc();
        $recordsFiltered = $filteredRes['total'] ?? 0;

        $sql = "SELECT id, name, subject, description FROM email_templates $whereClause 
                ORDER BY $orderBy $orderDir 
                LIMIT $length OFFSET $start";
        $data = $db->query($sql)->fetchAll();

        response()->json([
            "draw" => $draw,
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ]);
    }
}
