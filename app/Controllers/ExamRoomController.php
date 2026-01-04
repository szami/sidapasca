<?php

namespace App\Controllers;

use Leaf\Http\Request;
use App\Utils\Database;
use App\Utils\View;

class ExamRoomController
{
    public function index()
    {
        if (!isset($_SESSION['admin'])) {
            response()->redirect('/admin/login');
            return;
        }

        $rooms = Database::connection()->select('exam_rooms')->orderBy('id', 'desc')->fetchAll();
        echo View::render('admin.master.rooms.index', ['rooms' => $rooms]);
    }

    public function create()
    {
        if (!isset($_SESSION['admin']))
            return;
        echo View::render('admin.master.rooms.create');
    }

    public function store()
    {
        if (!isset($_SESSION['admin']))
            return;

        $data = Request::body();
        $insertData = [
            'fakultas' => $data['fakultas'],
            'nama_ruang' => $data['nama_ruang'],
            'kapasitas' => $data['kapasitas']
        ];

        Database::connection()->insert('exam_rooms')->params($insertData)->execute();
        response()->redirect('/admin/master/rooms');
    }

    public function edit($id)
    {
        if (!isset($_SESSION['admin']))
            return;

        $room = Database::connection()->select('exam_rooms')->where('id', $id)->first();
        echo View::render('admin.master.rooms.edit', ['room' => $room]);
    }

    public function update($id)
    {
        if (!isset($_SESSION['admin']))
            return;

        $data = Request::body();
        $updateData = [
            'fakultas' => $data['fakultas'],
            'nama_ruang' => $data['nama_ruang'],
            'kapasitas' => $data['kapasitas']
        ];

        Database::connection()->update('exam_rooms')->params($updateData)->where('id', $id)->execute();
        response()->redirect('/admin/master/rooms');
    }

    public function destroy($id)
    {
        if (!isset($_SESSION['admin']))
            return;

        Database::connection()->delete('exam_rooms')->where('id', $id)->execute();
        response()->redirect('/admin/master/rooms');
    }
}
