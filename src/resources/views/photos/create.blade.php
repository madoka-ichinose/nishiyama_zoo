@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/photos_create.css') }}">
@endsection

@section('content')
<div class="photo-create">
    <h1 class="photo-create__title">写真を投稿する</h1>

    {{-- バリデーションエラー表示 --}}
    @if ($errors->any())
        <div class="photo-create__errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('photos.store') }}" method="POST" enctype="multipart/form-data" class="photo-create__form">
        @csrf

        {{-- 写真ファイル --}}
        <div class="form-group">
            <label for="image" class="form-label">写真ファイル <span class="required">必須</span></label>
            <input type="file" name="image" id="image" accept="image/*" required>
        </div>

        {{-- 動物選択 --}}
        <div class="form-group">
            <label for="animal_id" class="form-label">動物の名前 <span class="required">必須</span></label>
            <select name="animal_id" id="animal_id" required>
                <option value="">選択してください</option>
                @foreach($animals as $animal)
                    <option value="{{ $animal->id }}" {{ old('animal_id') == $animal->id ? 'selected' : '' }}>
                        {{ $animal->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- コメント --}}
        <div class="form-group">
            <label for="comment" class="form-label">コメント（任意）</label>
            <textarea name="comment" id="comment" rows="4">{{ old('comment') }}</textarea>
        </div>

        @if(!empty($activeContest))
  <div class="form-group">
      <label class="form-label">フォトコンテスト</label>
      <label style="display:flex;align-items:center;gap:.5rem;">
          <input type="checkbox" name="contest_id" value="{{ $activeContest->id }}"
                 {{ old('contest_id') ? 'checked' : '' }}>
          <span>
              「{{ $activeContest->title }}」に応募する
              <small style="margin-left:.5rem; color:#666;">
                  期間：{{ $activeContest->start_at->format('Y/m/d') }}〜{{ $activeContest->end_at->format('Y/m/d') }}
              </small>
          </span>
      </label>
  </div>
@endif

        {{-- 送信ボタン --}}
        <div class="form-group form-submit">
            <button type="submit" class="submit-btn">投稿する</button>
        </div>
    </form>
</div>
@endsection
