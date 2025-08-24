@extends('layouts.app')

@section('css')
{{-- 既存の管理用CSSを流用（例：admin_contests.cssなどがある場合） --}}
{{-- <link rel="stylesheet" href="{{ asset('css/admin_contests.css') }}"> --}}
<style>
  .admin-wrap{max-width:1200px;margin:24px auto;padding:0 15px}
  .admin-title{font-size:24px;margin:0 0 16px;border-left:5px solid #66cdaa;padding-left:10px}
  .card{background:#fff;border:1px solid #e5e5e5;border-radius:10px;padding:14px}
  .card-title{font-size:18px;margin:0 0 10px}
  .contest-form .form-row{display:flex;flex-direction:column;gap:6px;margin-bottom:12px}
  .contest-form label{font-weight:600}
  .req{color:#d9534f;font-size:12px;margin-left:6px}
  .contest-form input[type="text"],
  .contest-form input[type="datetime-local"],
  .contest-form textarea{
    border:1px solid #ccc;border-radius:8px;padding:10px;font-size:14px
  }
  .contest-form textarea{resize:vertical;min-height:120px}
  .form-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}
  .btn{display:inline-block;padding:8px 12px;border-radius:6px;text-decoration:none;font-weight:600;font-size:13px;border:none;cursor:pointer}
  .btn--primary{background:#66cdaa;color:#fff}
  .btn--ghost{background:#f5f5f5}
  .btn--warn{background:#f0ad4e;color:#fff}
  .btn--danger{background:#d9534f;color:#fff}
  .flash-success{background:#f0fff7;border:1px solid #bfe8d7;color:#246b4f;padding:10px 14px;border-radius:6px;margin-bottom:12px}
  .flash-error{background:#fff5f5;border:1px solid #f5c2c7;color:#842029;padding:10px 14px;border-radius:6px;margin-bottom:12px}
</style>
@endsection

@section('content')
<div class="admin-wrap">
  <h1 class="admin-title">コンテスト編集</h1>

  {{-- フラッシュ --}}
  @if(session('status'))
    <div class="flash-success">{{ session('status') }}</div>
  @endif

  {{-- バリデーション --}}
  @if ($errors->any())
    <div class="flash-error">
      <ul style="margin:0;padding-left:18px;">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card">
    <h2 class="card-title">内容を編集</h2>

    <form class="contest-form" method="POST" action="{{ route('admin.contests.update', $contest) }}">
      @csrf
      @method('PUT')

      {{-- タイトル --}}
      <div class="form-row">
        <label for="title">コンテスト名 <span class="req">必須</span></label>
        <input type="text" id="title" name="title"
               value="{{ old('title', $contest->title) }}" required>
      </div>

      {{-- 開始日時 --}}
      <div class="form-row">
        <label for="start_at">開始日時 <span class="req">必須</span></label>
        <input type="datetime-local" id="start_at" name="start_at"
               value="{{ old('start_at', $contest->start_at?->format('Y-m-d\TH:i')) }}" required>
      </div>

      {{-- 終了日時 --}}
      <div class="form-row">
        <label for="end_at">終了日時 <span class="req">必須</span></label>
        <input type="datetime-local" id="end_at" name="end_at"
               value="{{ old('end_at', $contest->end_at?->format('Y-m-d\TH:i')) }}" required>
      </div>

      {{-- 説明 --}}
      <div class="form-row">
        <label for="description">説明（任意）</label>
        <textarea id="description" name="description" rows="6"
          placeholder="応募ルール、ハッシュタグ、注意事項など">{{ old('description', $contest->description) }}</textarea>
      </div>

      <div class="form-actions">
        <a href="{{ route('admin.contests.index') }}" class="btn btn--ghost">一覧に戻る</a>
        <button type="submit" class="btn btn--primary">更新する</button>

        {{-- 任意：今すぐ終了（end_atを現在に） --}}
        @if(now()->lt($contest->end_at))
          <form method="POST" action="{{ route('admin.contests.endNow', $contest) }}" style="display:inline;">
            @csrf @method('PUT')
            <button type="submit" class="btn btn--warn">今すぐ終了</button>
          </form>
        @endif

        {{-- 任意：削除 --}}
        <form method="POST" action="{{ route('admin.contests.destroy', $contest) }}"
              onsubmit="return confirm('このコンテストを削除しますか？');"
              style="display:inline;">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn--danger">削除</button>
        </form>
      </div>
    </form>
  </div>
</div>
@endsection
