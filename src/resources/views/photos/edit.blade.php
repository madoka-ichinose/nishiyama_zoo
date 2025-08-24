@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/photos_edit.css') }}">
@endsection

@section('content')
<div class="photo-edit">
  <h1 class="photo-edit__title">投稿の編集</h1>

  {{-- フラッシュ --}}
  @if(session('status'))
    <div class="photo-edit__flash">{{ session('status') }}</div>
  @endif

  {{-- バリデーションエラー --}}
  @if ($errors->any())
    <div class="photo-edit__errors">
      <ul>
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('photos.update', $photo) }}" method="POST" enctype="multipart/form-data" class="photo-edit__form">
    @csrf
    @method('PUT')

    {{-- 現在の写真プレビュー --}}
    <div class="form-group">
      <label class="form-label">現在の写真</label>
      <div class="photo-edit__preview">
        <img src="{{ asset('storage/'.$photo->image_path) }}" alt="現在の写真">
      </div>
    </div>

    {{-- 画像の差し替え（任意） --}}
    <div class="form-group">
      <label for="image" class="form-label">写真を差し替える（任意）</label>
      <input type="file" name="image" id="image" accept="image/*">
      <p class="form-help">※ 未選択なら現状のままです。最大 4MB など。</p>
    </div>

    {{-- 動物の選択 --}}
    <div class="form-group">
      <label for="animal_id" class="form-label">動物名 <span class="required">必須</span></label>
      <select name="animal_id" id="animal_id" required>
        <option value="">選択してください</option>
        @foreach($animals as $animal)
          <option value="{{ $animal->id }}" {{ old('animal_id', $photo->animal_id) == $animal->id ? 'selected' : '' }}>
            {{ $animal->name }}
          </option>
        @endforeach
      </select>
    </div>

    {{-- コメント --}}
    <div class="form-group">
      <label for="comment" class="form-label">コメント（任意）</label>
      <textarea name="comment" id="comment" rows="4">{{ old('comment', $photo->comment) }}</textarea>
    </div>

    {{-- 承認状態の参考表示（編集画面で変更はしない想定） --}}
    <div class="form-group">
      <label class="form-label">現在の公開状態</label>
      <p class="muted">
        承認：{{ $photo->is_approved ? '承認済み' : '未承認' }}　
        表示：{{ $photo->is_visible ? '公開中' : '非公開' }}
      </p>
    </div>

    <div class="form-actions">
      <a href="{{ route('mypage.index') }}" class="btn btn--ghost">戻る</a>
      <button type="submit" class="btn btn--primary">更新する</button>
    </div>
  </form>
</div>
@endsection
