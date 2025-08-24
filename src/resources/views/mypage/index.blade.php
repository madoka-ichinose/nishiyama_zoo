@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
@endsection

@section('content')
<div class="mypage-wrap">

  {{-- ステータスメッセージ --}}
  @if (session('status'))
    <div class="alert">{{ session('status') }}</div>
  @endif

  <div class="mypage-head">
    <h1 class="mypage-title">マイページ</h1>
    <a href="{{ route('photos.create') }}" class="btn btn--edit">写真を投稿する</a>
  </div>

  {{-- サマリー --}}
  <p class="meta">
    合計：{{ $photos->total() }}件
    承認済：{{ $approvedCount ?? $photos->where('is_approved',1)->count() }}件
    審査中：{{ $pendingCount ?? $photos->where('is_approved',0)->count() }}件
  </p>

  @if($photos->count())
    <div class="photos-grid">
      @foreach ($photos as $photo)
        <article class="photo-card">

          {{-- 画像 --}}
          <img
            class="photo-card__media"
            src="{{ asset('storage/' . $photo->image_path) }}"
            alt="{{ $photo->animal->name ?? '投稿写真' }}"
          >

          <div class="photo-card__body">
            {{-- 承認バッジ --}}
            @php
              $approved = (int)($photo->is_approved ?? 0) === 1;
            @endphp
            <span class="badge {{ $approved ? 'badge--approved' : 'badge--pending' }}">
              {{ $approved ? '承認済' : '審査中' }}
            </span>

            {{-- 動物名・日付 --}}
            <div class="meta">
              {{ $photo->animal->name ?? '動物未設定' }}<br>
              <time>{{ $photo->created_at->format('Y/m/d') }}</time>
              @if($photo->contest_id)
                <br>コンテスト応募中
              @endif
            </div>

            {{-- コメント --}}
            @if($photo->comment)
              <p class="comment">{{ $photo->comment }}</p>
            @endif

            {{-- アクション --}}
            <div class="actions">
              @if($approved)
                <a href="{{ route('photos.index', $photo->id) }}" class="btn btn--view" target="_blank">公開ページを見る</a>
              @endif

              <form action="{{ route('photos.destroy', $photo->id) }}" method="POST" onsubmit="return confirm('この投稿を削除しますか？');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn--delete">削除</button>
              </form>
            </div>
          </div>
        </article>
      @endforeach
    </div>

    {{-- ページネーション --}}
    <div class="pagination">
      {{ $photos->links() }}
    </div>
  @else
    <div class="alert">
      まだ投稿がありません。最初の写真を投稿してみましょう！
      <div style="margin-top:10px;">
        <a href="{{ route('photos.create') }}" class="btn btn--edit">写真を投稿する</a>
      </div>
    </div>
  @endif
</div>
@endsection
