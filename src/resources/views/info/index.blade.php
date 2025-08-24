@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/info.css') }}">
@endsection

@section('content')
<div class="info-wrap">

  <header class="info-head">
    <h1 class="info-title">インフォメーション</h1>

    {{-- もしコントローラから $isClosedToday / $openingHours を渡していれば、当日の営業状況を表示 --}}
    @isset($isClosedToday)
      @if($isClosedToday)
        <span class="badge badge--closed">本日は定休日です</span>
      @else
        <span class="badge badge--open">本日の営業時間：{{ $openingHours ?? '9:00〜16:30' }}</span>
      @endif
    @endisset
  </header>

  {{-- 営業案内 --}}
  <section class="card">
    <h2 class="card-title">営業案内</h2>
    <dl class="defs">
      <div class="row">
        <dt>所在地</dt>
        <dd>〒916-0027 福井県鯖江市桜町3-8-9</dd>
      </div>
      <div class="row">
        <dt>電話番号</dt>
        <dd>TEL 0778-52-2737</dd>
      </div>
      <div class="row">
        <dt>開園時間</dt>
        <dd>9:00 ～ 16:30</dd>
      </div>
      <div class="row">
        <dt>休園日</dt>
        <dd>
          毎週月曜日（祝日・祭日の場合は火曜日）／ 年末年始（12月29日～1月3日）
        </dd>
      </div>
    </dl>
  </section>

  {{-- アクセス --}}
  <section class="card">
    <h2 class="card-title">アクセス</h2>

    <div class="grid-2">
      <div class="map">
        {{-- Google マップ埋め込み（住所に合わせたクエリ） --}}
        <iframe
          title="西山動物園 地図"
          src="https://www.google.com/maps?q=福井県鯖江市桜町3-8-9&hl=ja&z=16&output=embed"
          width="100%" height="340" style="border:0;" allowfullscreen="" loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"></iframe>
        <div class="map-link">
          <a href="https://www.google.com/maps/search/?api=1&query=福井県鯖江市桜町3-8-9" target="_blank" rel="noopener">
            Googleマップで開く
          </a>
        </div>
      </div>

      <div class="access-detail">
        <dl class="defs">
          <div class="row">
            <dt>最寄り駅</dt>
            <dd>
              福井鉄道「西鯖江駅」より 徒歩10分<br>
              福井鉄道「西山公園駅」より 徒歩5分
            </dd>
          </div>
          <div class="row">
            <dt>バス</dt>
            <dd>
              つつじバス「鯖江市役所」〜「中央線で西山公園東下車」徒歩1分
            </dd>
          </div>
          <div class="row">
            <dt>お車</dt>
            <dd>鯖江ICより 車で5分</dd>
          </div>
          <div class="row">
            <dt>駐車場</dt>
            <dd>
              西山公園南にある鯖江市嚮陽会館前駐車場（2時間以内無料）をご利用ください。
            </dd>
          </div>
        </dl>
      </div>
    </div>
  </section>

</div>
@endsection
