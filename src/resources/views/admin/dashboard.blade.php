@extends('layouts.app')

@section('css')
<style>
  .admin-wrap{max-width:1200px;margin:24px auto;padding:0 15px}
  .admin-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;gap:12px;flex-wrap:wrap}
  .admin-title{font-size:24px;margin:0;border-left:5px solid #66cdaa;padding-left:10px}
  .cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px}
  .card{background:#fff;border:1px solid #e5e5e5;border-radius:10px;padding:16px}
  .card h3{margin:0 0 6px;font-size:14px;color:#666}
  .card p{margin:0;font-size:28px;font-weight:700}
  .btn{display:inline-block;background:#66cdaa;color:#fff;padding:8px 12px;border-radius:8px;text-decoration:none}
  .admin-actions{display:flex;gap:8px;flex-wrap:wrap}
  .logout-btn{background:#d9534f}
</style>
@endsection

@section('content')
<div class="admin-wrap">
  <div class="admin-head">
    <h1 class="admin-title">管理ダッシュボード</h1>
    <div class="admin-actions">
      <a class="btn" href="{{ route('admin.users.index') }}">会員管理</a>
      <a class="btn" href="{{ route('admin.photos.index') }}">投稿承認管理</a>
      <a class="btn" href="{{ route('admin.contests.index') }}">コンテスト管理</a>
      <form action="{{ route('admin.logout') }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" class="btn logout-btn">ログアウト</button>
      </form>
    </div>
  </div>

  <div class="cards">
    <div class="card">
      <h3>会員数</h3>
      <p>{{ number_format($userCount) }}</p>
    </div>
    <div class="card">
      <h3>総投稿数</h3>
      <p>{{ number_format($photoCount) }}</p>
    </div>
    <div class="card">
      <h3>未承認の投稿</h3>
      <p>{{ number_format($pendingPhotoCount) }}</p>
    </div>
    <div class="card">
      <h3>開催中のコンテスト</h3>
      <p>{{ number_format($activeContestCount) }}</p>
    </div>
  </div>
</div>
@endsection
