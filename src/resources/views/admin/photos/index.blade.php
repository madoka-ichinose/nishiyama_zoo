@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_photos.css') }}">
@endsection

@section('content')
<div class="admin-wrap">
  <div class="admin-head">
    <h1 class="admin-title">投稿承認管理</h1>

    {{-- フィルタ / 検索 --}}
    <div class="filters">
      <div class="tabs">
        @php $status = request('status','pending'); @endphp
        <a class="tab {{ $status==='pending'?'is-active':'' }}"
           href="{{ route('admin.photos.index',['status'=>'pending','q'=>request('q')]) }}">審査中</a>
        <a class="tab {{ $status==='approved'?'is-active':'' }}"
           href="{{ route('admin.photos.index',['status'=>'approved','q'=>request('q')]) }}">承認済</a>
        <a class="tab {{ $status==='all'?'is-active':'' }}"
           href="{{ route('admin.photos.index',['status'=>'all','q'=>request('q')]) }}">すべて</a>
      </div>

      <form class="search" method="GET" action="{{ route('admin.photos.index') }}">
        <input type="hidden" name="status" value="{{ $status }}">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="動物名・投稿者・コメント">
        <button type="submit">検索</button>
      </form>
    </div>
  </div>

  {{-- フラッシュメッセージ --}}
  @if(session('status'))
    <div class="flash-success">
      {{ session('status') }}
    </div>
  @endif

  {{-- 一覧テーブル --}}
  <table class="table">
    <thead>
      <tr>
        <th class="hide-sm">画像</th>
        <th>概要</th>
        <th class="hide-sm">コメント</th>
        <th>状態</th>
        <th>操作</th>
      </tr>
    </thead>
    <tbody>
      @forelse($photos as $photo)
        <tr>
          <td class="hide-sm thumb-col">
            <a href="{{ asset('storage/'.$photo->image_path) }}" target="_blank" rel="noopener">
              <img class="thumb" src="{{ asset('storage/'.$photo->image_path) }}" alt="thumb">
            </a>
          </td>

          <td>
            <div><strong>{{ $photo->animal->name ?? '（動物未設定）' }}</strong></div>
            <div class="muted">
              投稿者：{{ $photo->user->name ?? '不明' }}
              <br>投稿日時：{{ $photo->created_at?->format('Y/m/d H:i') }}
              @if($photo->contest_id)
                <br>コンテスト応募：あり
              @endif
            </div>
          </td>

          <td class="hide-sm comment-col">
            @if($photo->comment)
              <div class="comment-ellipsis">
                {{ $photo->comment }}
              </div>
            @else
              <span class="muted">（コメントなし）</span>
            @endif
          </td>

          <td class="state-col">
            {{-- 承認ステータス --}}
            @if($photo->is_approved)
              <span class="badge badge--approved">承認済</span>
              @if(!empty($photo->approved_at))
                <div class="muted mt6">{{ \Carbon\Carbon::parse($photo->approved_at)->format('Y/m/d') }}</div>
              @endif
            @else
              <span class="badge badge--pending">審査中</span>
            @endif

            {{-- 公開ステータス --}}
            <div class="muted mt6">
              表示：{{ $photo->is_visible ? '公開中' : '非公開' }}
              @if(!$photo->is_visible && $photo->hidden_at)
                <br>非公開日：{{ \Carbon\Carbon::parse($photo->hidden_at)->format('Y/m/d') }}
              @endif
            </div>
          </td>

          <td class="ops-col">
            <div class="actions">
              {{-- 承認（未承認のときだけ） --}}
              @unless($photo->is_approved)
                <form method="POST" action="{{ route('admin.photos.approve',$photo->id) }}">
                  @csrf @method('PUT')
                  <button type="submit" class="btn btn--approve">承認する</button>
                </form>
              @endunless

              {{-- 公開/非公開トグル --}}
              @if($photo->is_visible)
                {{-- 非公開にする --}}
                <form method="POST" action="{{ route('admin.photos.hide',$photo->id) }}"
                      onsubmit="return confirm('この投稿を非公開にしますか？');">
                  @csrf @method('PUT')
                  {{-- 非公開理由を扱うなら hidden/入力欄を追加 --}}
                  <button type="submit" class="btn btn--delete">非公開にする</button>
                </form>
              @else
                {{-- 再公開する --}}
                <form method="POST" action="{{ route('admin.photos.unhide',$photo->id) }}"
                      onsubmit="return confirm('この投稿を再公開しますか？');">
                  @csrf @method('PUT')
                  <button type="submit" class="btn btn--approve">再公開する</button>
                </form>
              @endif
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="5" class="empty">該当する投稿がありません。</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  {{-- ページネーション --}}
  <div class="pagination">
    {{ $photos->appends(['status'=>request('status'),'q'=>request('q')])->links() }}
  </div>
</div>
@endsection
