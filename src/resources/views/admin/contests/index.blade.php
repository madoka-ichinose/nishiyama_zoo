@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_contests.css') }}">
@endsection

@section('content')
<div class="admin-wrap">
  <div class="admin-head">
    <h1 class="admin-title">コンテスト管理</h1>
  </div>

  {{-- フラッシュメッセージ --}}
  @if(session('status'))
    <div class="flash-success">{{ session('status') }}</div>
  @endif

  {{-- バリデーションエラー --}}
  @if ($errors->any())
    <div class="flash-error">
      <ul>
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="grid">
    {{-- 作成フォーム --}}
    <section class="card">
      <h2 class="card-title">新規コンテストを作成</h2>
      <form class="contest-form" method="POST" action="{{ route('admin.contests.store') }}">
        @csrf
        <div class="form-row">
          <label for="title">タイトル <span class="req">必須</span></label>
          <input id="title" type="text" name="title" value="{{ old('title') }}" required>
        </div>

        <div class="form-row">
          <label for="start_at">開始日時 <span class="req">必須</span></label>
          <input id="start_at" type="datetime-local" name="start_at"
                 value="{{ old('start_at') }}" required>
        </div>

        <div class="form-row">
          <label for="end_at">終了日時 <span class="req">必須</span></label>
          <input id="end_at" type="datetime-local" name="end_at"
                 value="{{ old('end_at') }}" required>
        </div>

        <div class="form-row">
          <label for="description">説明（任意）</label>
          <textarea id="description" name="description" rows="3">{{ old('description') }}</textarea>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn--primary">作成する</button>
        </div>
      </form>
    </section>

    {{-- 一覧 --}}
    <section class="card">
      <h2 class="card-title">コンテスト一覧</h2>

      @php
        $now = \Carbon\Carbon::now();
        $counts = [
          'active'   => $contests->filter(fn($c) => $now->between($c->start_at, $c->end_at))->count(),
          'upcoming' => $contests->filter(fn($c) => $now->lt($c->start_at))->count(),
          'ended'    => $contests->filter(fn($c) => $now->gt($c->end_at))->count(),
        ];
      @endphp

      <div class="summary">
        <span class="badge badge--active">開催中 {{ $counts['active'] }}</span>
        <span class="badge badge--upcoming">予定 {{ $counts['upcoming'] }}</span>
        <span class="badge badge--ended">終了 {{ $counts['ended'] }}</span>
      </div>

      <table class="table">
        <thead>
          <tr>
            <th style="width:70px;">ID</th>
            <th>タイトル</th>
            <th style="width:220px;">期間</th>
            <th style="width:120px;">状態</th>
            <th style="width:260px;">操作</th>
          </tr>
        </thead>
        <tbody>
          @forelse($contests as $c)
            @php
              $isActive   = $now->between($c->start_at, $c->end_at);
              $isUpcoming = $now->lt($c->start_at);
              $isEnded    = $now->gt($c->end_at);
            @endphp
            <tr>
              <td>{{ $c->id }}</td>
              <td>
                <div class="title">{{ $c->title }}</div>
                @if($c->description)
                  <div class="muted ellipsis">{{ $c->description }}</div>
                @endif
              </td>
              <td>
                <div>{{ $c->start_at?->format('Y/m/d H:i') }} ～</div>
                <div>{{ $c->end_at?->format('Y/m/d H:i') }}</div>
              </td>
              <td>
                @if($isActive)
                  <span class="badge badge--active">開催中</span>
                @elseif($isUpcoming)
                  <span class="badge badge--upcoming">予定</span>
                @else
                  <span class="badge badge--ended">終了</span>
                @endif
              </td>
              <td>
                <div class="actions">
                    <a href="{{ route('admin.contests.edit', $c->id) }}" class="btn btn--ghost">編集</a>

                    <a class="btn" href="{{ route('admin.contests.photos.index', $c->id) }}">応募写真を見る</a>

                  @if(Route::has('admin.contests.endNow') && $isActive)
                    <form method="POST" action="{{ route('admin.contests.endNow', $c->id) }}"
                          onsubmit="return confirm('このコンテストを今すぐ終了しますか？');">
                      @csrf @method('PUT')
                      <button type="submit" class="btn btn--warn">今すぐ終了</button>
                    </form>
                  @endif

                  {{-- 任意：削除 --}}
                  @if(Route::has('admin.contests.destroy'))
                    <form method="POST" action="{{ route('admin.contests.destroy', $c->id) }}"
                          onsubmit="return confirm('このコンテストを削除しますか？（取り消し不可）');">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn btn--danger">削除</button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="empty">コンテストはまだありません。</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </section>
  </div>
</div>
@endsection
