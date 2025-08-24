@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
@endsection

@section('content')
<div class="home">
  <section class="hero">
    <video class="hero__video" autoplay muted loop playsinline>
        <source src="{{ asset('videos/red_panda.mp4') }}" type="video/mp4">
        お使いのブラウザは動画再生に対応していません。
    </video>

    <div class="hero__overlay">
  <div class="hero__text">
    @if(!empty($isClosedToday) && $isClosedToday)
      <p class="hero__openlabel">本日は</p>
      <p class="hero__openhours">定休日</p>
    @else
      <p class="hero__openlabel">本日の営業時間は</p>
      <p class="hero__openhours">{{ $openingHours ?? '9:00〜16:30' }}</p>
    @endif
  </div>
</div>
  </section>

  @if($contest)
    {{-- 開催中の場合 --}}
    <section class="contest">
        <p class="contest__lead">
            {{ $contest->title }} 開催中！<br class="sp">
            <span class="contest__deadline">
                {{ $contest->end_at->format('n月j日') }} まで
            </span>
        </p>
        <a href="{{ route('photos.create') }}" class="contest__button">写真応募はこちらから</a>
    </section>
@else
    {{-- 非開催中の場合 --}}
    <section class="contest">
        <p class="contest__lead">
            みなさんの思い出を共有しませんか？
        </p>
        <a href="{{ route('photos.create') }}" class="contest__button">写真投稿はこちらから</a>
    </section>
@endif

  <div class="photo__and--topics">
  <section class="pickup">
    <h2 class="section-title"><span>PICK UP</span> 投稿写真</h2>

    <div class="pickup__grid">
      @forelse(($pickupPhotos ?? []) as $photo)
        <article class="card">
          <a href="{{ route('photos.show', $photo) }}" class="card__link">
            <img src="{{ asset($photo->image_path) }}" alt="{{ $photo->animal->name }}" class="card__media">
            <div class="card__body">
              <div class="card__row">
                <span class="card__name">{{ $photo->animal->name }}</span>
                <time class="card__time">{{ $photo->created_at->format('Y/m/d') }}</time>
              </div>
              @if($photo->comment)
                <p class="card__comment">{{ $photo->comment }}</p>
              @endif
            </div>
          </a>
        </article>
      @empty
        <article class="card card--placeholder">
          <div class="card__ph"></div>
          <div class="card__body">
            <div class="card__row"><span class="card__name">名前</span><span class="card__time">投稿日時</span></div>
            <p class="card__comment">コメント</p>
          </div>
        </article>
        <article class="card card--placeholder">
          <div class="card__ph"></div>
          <div class="card__body">
            <div class="card__row"><span class="card__name">名前</span><span class="card__time">投稿日時</span></div>
            <p class="card__comment">コメント</p>
          </div>
        </article>
         <article class="card card--placeholder">
          <div class="card__ph"></div>
          <div class="card__body">
            <div class="card__row"><span class="card__name">名前</span><span class="card__time">投稿日時</span></div>
            <p class="card__comment">コメント</p>
          </div>
        </article>
      @endforelse
    </div>
  </section>

  <section class="topics">
    <img class="topics__panda" src="{{ asset('images/red_panda.png') }}" 
     alt="レッサーパンダ" >
    <h2 class="section-title">TOPICS &amp; EVENT</h2>

    <div class="topics__tabs">
      <a href="{{ route('top', ['filter' => 'all']) }}" class="topics__tab {{ (request('filter')==='all'||!request()->has('filter'))?'is-active':'' }}">すべて</a>
      <a href="{{ route('top', ['filter' => 'topic']) }}" class="topics__tab {{ request('filter')==='topic'?'is-active':'' }}">トピック</a>
      <a href="{{ route('top', ['filter' => 'event']) }}" class="topics__tab {{ request('filter')==='event'?'is-active':'' }}">イベント</a>
    </div>

    <div class="topics__list">
      @forelse(($topics ?? []) as $t)
        <article class="topics-card">
          <a href="{{ $t->url ?? '#' }}" class="topics-card__link">
            <div class="topics-card__thumb">
              <img src="{{ asset($t->image_path ?? 'images/topic_placeholder.jpg') }}" alt="" class="topics-card__img">
            </div>
            <div class="topics-card__body">
              <h3 class="topics-card__title">{{ $t->title ?? 'イベント名／お知らせ見出し' }}</h3>
              <p class="topics-card__meta">
                <span class="topics-card__label">{{ strtoupper($t->type ?? 'TOPIC') }}</span>
                @if(!empty($t->date)) <time>{{ \Carbon\Carbon::parse($t->date)->format('Y/m/d') }}</time> @endif
              </p>
              @if(!empty($t->excerpt))
                <p class="topics-card__excerpt">{{ $t->excerpt }}</p>
              @endif
            </div>
          </a>
        </article>
      @empty
        {{-- プレースホルダ2件 --}}
        <article class="topics-card topics-card--placeholder">
          <div class="topics-card__thumb"></div>
          <div class="topics-card__body">
            <h3 class="topics-card__title">イベント名／お知らせ見出し</h3>
            <p class="topics-card__meta"><span class="topics-card__label">TOPIC</span></p>
            <p class="topics-card__excerpt">イベント名／場所／詳細など</p>
          </div>
        </article>

        <article class="topics-card topics-card--placeholder">
          <div class="topics-card__thumb"></div>
          <div class="topics-card__body">
            <h3 class="topics-card__title">イベント名／お知らせ見出し</h3>
            <p class="topics-card__meta"><span class="topics-card__label">EVENT</span></p>
            <p class="topics-card__excerpt">イベント名／場所／詳細など</p>
          </div>
        </article>
      @endforelse
    </div>
  </section>
</div>
</div>
@endsection
