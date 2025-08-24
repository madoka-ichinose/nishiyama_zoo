<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InfoController extends Controller
{
    public function index()
    {
        // 実際はDBから取得など
        // $infos = Info::latest()->paginate(10);

        return view('info.index'); // 変数を渡す場合は compact('infos')
    }
}
