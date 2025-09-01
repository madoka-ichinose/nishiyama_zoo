@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_contest_photos.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="page-title">コンテスト応募写真：{{ $contest->title }}</h1>

    {{-- フラッシュ --}}
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- タブ（ステータス） --}}
    @php($tabs = ['' => 'すべて', 'pending' => '承認待ち', 'approved' => '承認済み', 'rejected' => '却下'])
    <ul class="tabs">
        @foreach ($tabs as $key => $label)
            <li class="{{ (string)$status === (string)$key ? 'active' : '' }}">
                <a href="{{ route('admin.contests.photos.index',
                        array_merge(
                            ['contest' => $contest->id],
                            request()->except('page','status'),
                            ['status' => $key]
                        )) }}">{{ $label }}</a>
            </li>
        @endforeach
    </ul>

    {{-- 検索フォーム --}}
    <form method="get" class="filter-form">
        <input type="hidden" name="status" value="{{ $status }}">
        <div class="row">
            <label>キーワード
                <input type="text" name="q" value="{{ $kw }}" placeholder="キャプション/非表示理由">
            </label>
            <label>投稿者
                <input type="text" name="user" value="{{ $uname }}" placeholder="ユーザー名を含む">
            </label>
            <label>表示状態
                <select name="visible">
                    <option value="">すべて</option>
                    <option value="1" @selected($visible==='1')>表示</option>
                    <option value="0" @selected($visible==='0')>非表示</option>
                </select>
            </label>
            <label>期間
                <input type="date" name="from" value="{{ $from }}">～
                <input type="date" name="to" value="{{ $to }}">
            </label>
            <label>並び順
                <select name="sort">
                    @php($opts=['-id'=>'新しい順','id'=>'古い順','-created_at'=>'投稿が新しい順','created_at'=>'投稿が古い順'])
                    @foreach($opts as $v=>$t)
                        <option value="{{ $v }}" @selected($sort===$v)>{{ $t }}</option>
                    @endforeach
                </select>
            </label>
            <button class="btn">検索</button>
            <a class="btn secondary" href="{{ route('admin.contests.photos.index', $contest->id) }}">リセット</a>
        </div>
    </form>

    {{-- 集計カード --}}
    <div class="stats">
        <div class="card">合計 <strong>{{ $totals['total'] }}</strong></div>
        <div class="card">承認待ち <strong>{{ $totals['pending'] }}</strong></div>
        <div class="card">承認済み <strong>{{ $totals['approved'] }}</strong></div>
        <div class="card">却下 <strong>{{ $totals['rejected'] }}</strong></div>
        <div class="card">表示中 <strong>{{ $totals['visible'] }}</strong></div>
        <div class="card">非表示 <strong>{{ $totals['hidden'] }}</strong></div>
        <div class="card">参加ユーザー数 <strong>{{ $totals['participants'] }}</strong></div>
    </div>

    {{-- CSV出力 --}}
    <div class="export">
        <a class="btn" href="{{ route('admin.contests.photos.export', array_merge(['contest'=>$contest->id], request()->query())) }}">CSVエクスポート</a>
    </div>

    {{-- 一括操作 --}}
    <form method="post" action="{{ route('admin.contests.photos.bulk', $contest->id) }}" id="bulk-form">
        @csrf
        <div class="bulk">
            <select name="action" required>
                <option value="">一括操作を選択</option>
                <option value="approve">承認にする</option>
                <option value="reject">却下にする</option>
                <option value="hide">非表示にする</option>
                <option value="unhide">表示にする</option>
                <option value="delete">削除（論理削除）</option>
            </select>
            <input type="text" name="reason" placeholder="非表示理由（任意）">
            <button class="btn danger" onclick="return confirm('選択行に一括適用します。よろしいですか？')">適用</button>
        </div>

        {{-- 一覧テーブル --}}
<table class="table">
    <thead>
        <tr>
            <th><input type="checkbox" id="check-all"></th>
            <th>ID</th>
            <th>写真</th>
            <th>コメント</th>
            <th>投稿者</th>
            <th>状態</th>
            <th>表示</th>
            <th>投稿日時</th>
            <th>非表示理由</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($photos as $p)
        <tr>
            <td><input type="checkbox" name="ids[]" value="{{ $p->id }}"></td>
            <td>{{ $p->id }}</td>
            <td>
                {{-- サムネイルをクリックでモーダル表示 --}}
                <a href="#"
                   class="thumb-trigger"
                   data-img="{{ asset('storage/'.$p->image_path) }}"
                   data-comment="{{ $p->comment }}"
                   data-user="{{ optional($p->user)->name }}"
                   data-date="{{ $p->created_at?->format('Y-m-d H:i') }}">
                    <img src="{{ asset('storage/'.$p->image_path) }}" alt="photo {{ $p->id }}" class="thumb">
                </a>
            </td>
            <td class="caption">{{ $p->comment }}</td>
            <td>{{ optional($p->user)->name }}</td>
            <td>
                @php($st = $p->status)
                <span class="badge {{ $st }}">{{ [
                    'pending'=>'承認待ち','approved'=>'承認済み','rejected'=>'却下'
                ][$st] ?? $st }}</span>
            </td>
            <td>{!! $p->is_visible ? '<span class="ok">表示</span>' : '<span class="ng">非表示</span>' !!}</td>
            <td>{{ $p->created_at?->format('Y-m-d H:i') }}</td>
            <td>{{ $p->hidden_reason }}</td>
        </tr>
        @empty
        <tr><td colspan="9" class="empty">該当データがありません</td></tr>
        @endforelse
    </tbody>
</table>

    </form>

    {{ $photos->links() }}

    {{-- 簡易トレンド（直近30日） --}}
    <details class="mt-lg">
        <summary>直近30日の投稿数推移</summary>
        <div class="trend">
            @foreach($daily as $d)
                <div class="bar" style="--v: {{ $d->cnt }}" title="{{ $d->d }}: {{ $d->cnt }}"></div>
            @endforeach
        </div>
        <small>バーの高さは件数相対値です</small>
    </details>
</div>

{{-- モーダル --}}
<div id="photoModal" class="modal" aria-hidden="true">
  <div class="modal__overlay" data-close></div>
  <div class="modal__dialog" role="dialog" aria-modal="true">
    <button class="modal__close" type="button" aria-label="閉じる" data-close>&times;</button>
    <div class="modal__media">
      <img id="modalImg" src="" alt="拡大画像">
    </div>
    <div class="modal__body">
      <p class="modal__user"></p>
      <p class="modal__comment"></p>
      <p class="modal__date"></p>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
    document.getElementById('check-all')?.addEventListener('change', function(e){
        document.querySelectorAll('input[name="ids[]"]').forEach(ch => ch.checked = e.target.checked);
    });
    document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('photoModal');
  const imgEl = document.getElementById('modalImg');
  const userEl = modal.querySelector('.modal__user');
  const commentEl = modal.querySelector('.modal__comment');
  const dateEl = modal.querySelector('.modal__date');

  document.querySelectorAll('.thumb-trigger').forEach(el => {
    el.addEventListener('click', e => {
      e.preventDefault();
      imgEl.src = el.dataset.img;
      userEl.textContent = el.dataset.user || '投稿者不明';
      commentEl.textContent = el.dataset.comment || '';
      dateEl.textContent = el.dataset.date || '';
      modal.classList.add('is-open');
      modal.setAttribute('aria-hidden', 'false');
    });
  });

  modal.addEventListener('click', e => {
    if (e.target.hasAttribute('data-close')) {
      modal.classList.remove('is-open');
      modal.setAttribute('aria-hidden', 'true');
      imgEl.src = '';
    }
  });

  window.addEventListener('keydown', e => {
    if (e.key === 'Escape' && modal.classList.contains('is-open')) {
      modal.classList.remove('is-open');
      modal.setAttribute('aria-hidden', 'true');
      imgEl.src = '';
    }
  });
});
</script>
@endpush
