<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function index()
    {
        // 実際はDBから取得など
        // $topics = Topic::latest()->paginate(10);

        return view('topics.index'); // 変数を渡す場合は compact('topics')
    }
}
