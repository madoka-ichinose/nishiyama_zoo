@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/photos.css') }}">
@endsection

@section('content')
<div class="photos-page">
    <div class="photos-header">
        <h1 class="photos-title">みんなの写真一覧</h1>

        {{-- ログインしている場合のみ投稿ボタン表示 --}}
        @auth
            <a href="{{ route('photos.create') }}" class="photo-upload-btn">写真を投稿する</a>
        @else
            <a href="{{ route('login') }}" class="photo-upload-btn">ログインして投稿</a>
        @endauth
    </div>

    @if ($photos->count() > 0)
        <div class="photos-grid">
            @foreach ($photos as $photo)
                <div class="photo-card">
                    {{-- 写真表示 --}}
                    <div class="photo-card__image">
  <a href="#"
     class="photo-card__trigger"
     data-img="{{ asset('storage/' . $photo->image_path) }}"
     data-author="{{ e($photo->user->name ?? '匿名') }}"
     data-comment="{{ e($photo->comment ?? '') }}"
     data-date="{{ e($photo->created_at->format('Y/m/d')) }}"
     aria-label="写真を拡大表示">
    <img src="{{ asset('storage/' . $photo->image_path) }}" alt="投稿写真" loading="lazy">
  </a>
</div>


                    {{-- 投稿者情報 --}}
                    <div class="photo-card__body">
                        <p class="photo-card__author">{{ $photo->user->name }}</p>
                        <p class="photo-card__comment">{{ $photo->comment }}</p>
                        <p class="photo-card__date">{{ $photo->created_at->format('Y/m/d') }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ページネーション --}}
        <div class="pagination">
            {{ $photos->links() }}
        </div>
    @else
        <p class="no-photos">まだ承認された写真がありません。</p>
    @endif
    {{-- グリッドの後ろ（@endforelse や @endif の“直前”でもOK）に追加：モーダル本体 --}}
<div id="photoModal" class="modal" aria-hidden="true">
  <div class="modal__overlay" data-close></div>

  <div class="modal__dialog" role="dialog" aria-modal="true" aria-labelledby="photoModalTitle">
    <button class="modal__close" type="button" aria-label="閉じる" data-close>&times;</button>

    <div class="modal__media">
      <img id="modalImg" src="" alt="拡大写真">
    </div>

    <div class="modal__body">
      <h3 id="photoModalTitle" class="modal__author"></h3>
      <p class="modal__comment"></p>
      <p class="modal__date"></p>
    </div>
  </div>
</div>

{{-- ページ末尾でOK（layoutに@stackが無い想定）。JS --}}
<script>
  (function(){
    const modal      = document.getElementById('photoModal');
    const imgEl      = document.getElementById('modalImg');
    const authorEl   = modal.querySelector('.modal__author');
    const commentEl  = modal.querySelector('.modal__comment');
    const dateEl     = modal.querySelector('.modal__date');
    const triggers   = document.querySelectorAll('.photo-card__trigger');

    const open = (src, author, comment, date) => {
      imgEl.src      = src;
      imgEl.alt      = (author ? author + 'さんの写真' : '拡大写真');
      authorEl.textContent  = author || '投稿者不明';
      commentEl.textContent = comment || '（コメントなし）';
      dateEl.textContent    = date || '';
      modal.classList.add('is-open');
      document.body.style.overflow = 'hidden'; // 背景スクロール抑止
      modal.setAttribute('aria-hidden', 'false');
    };

    const close = () => {
      modal.classList.remove('is-open');
      document.body.style.overflow = '';
      modal.setAttribute('aria-hidden', 'true');
      // 画像の読み込み解除（任意）
      imgEl.src = '';
    };

    triggers.forEach(t => {
      t.addEventListener('click', (e) => {
        e.preventDefault();
        const src     = t.dataset.img;
        const author  = t.dataset.author || '';
        const comment = t.dataset.comment || '';
        const date    = t.dataset.date || '';
        open(src, author, comment, date);
      });
    });

    // オーバーレイ＆×ボタン
    modal.addEventListener('click', (e) => {
      if (e.target.hasAttribute('data-close')) close();
    });

    // ESCキーで閉じる
    window.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && modal.classList.contains('is-open')) close();
    });
  })();
</script>

</div>
@endsection