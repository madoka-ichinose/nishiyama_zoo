@extends('layouts.app')

@section('css')
<style>
  .admin-login{max-width:520px;margin:40px auto;padding:24px;border:1px solid #ddd;border-radius:10px;background:#fff}
  .admin-login h1{font-size:22px;margin:0 0 16px;border-left:5px solid #66cdaa;padding-left:10px}
  .form-group{margin:12px 0}
  label{display:block;margin-bottom:6px;font-weight:600}
  input[type="email"],input[type="password"]{width:100%;height:44px;padding:10px;border:1px solid #ccc;border-radius:6px}
  .actions{display:flex;justify-content:space-between;align-items:center;margin-top:12px}
  .btn{display:inline-block;background:#66cdaa;color:#fff;padding:10px 16px;border-radius:8px;text-decoration:none;border:none;cursor:pointer}
  .error{background:#ffe8e8;border:1px solid #ffc9c9;color:#a33;padding:10px 14px;border-radius:6px;margin-bottom:12px}
</style>
@endsection

@section('content')
<div class="admin-login">
  <h1>管理者ログイン</h1>

  @if ($errors->any())
    <div class="error">
      <ul style="margin:0;padding-left:18px;">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.login.submit') }}">
    @csrf
    <div class="form-group">
      <label for="email">メールアドレス</label>
      <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
    </div>

    <div class="form-group">
      <label for="password">パスワード</label>
      <input id="password" type="password" name="password" required>
    </div>

    <div class="actions">
      <label style="display:flex;align-items:center;gap:.4rem;">
        <input type="checkbox" name="remember"> ログイン情報を保持
      </label>
      <button class="btn" type="submit">ログイン</button>
    </div>
  </form>
</div>
@endsection
