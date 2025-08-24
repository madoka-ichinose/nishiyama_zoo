@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_users.css') }}">
@endsection

@section('content')
<div class="admin-wrap">
  <div class="admin-head">
    <h1 class="admin-title">ユーザー管理</h1>

    {{-- フィルタ / 検索 --}}
    <div class="filters">
      <div class="tabs">
        @php $role = request('role','all'); @endphp
        <a class="tab {{ $role==='all'?'is-active':'' }}"
           href="{{ route('admin.users.index', ['role'=>'all','q'=>request('q')]) }}">すべて</a>
        <a class="tab {{ $role==='admin'?'is-active':'' }}"
           href="{{ route('admin.users.index', ['role'=>'admin','q'=>request('q')]) }}">管理者</a>
        <a class="tab {{ $role==='member'?'is-active':'' }}"
           href="{{ route('admin.users.index', ['role'=>'member','q'=>request('q')]) }}">一般</a>
      </div>

      <form class="search" method="GET" action="{{ route('admin.users.index') }}">
        <input type="hidden" name="role" value="{{ $role }}">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="名前・メールで検索">
        <button type="submit">検索</button>
      </form>
    </div>
  </div>

  {{-- フラッシュメッセージ（任意） --}}
  @if (session('status'))
    <div class="flash-success">{{ session('status') }}</div>
  @endif

  {{-- 一覧テーブル --}}
  <table class="table">
    <thead>
      <tr>
        <th class="hide-sm" style="width:80px;">ID</th>
        <th>ユーザー</th>
        <th class="hide-sm">メール</th>
        <th class="hide-sm" style="width:120px;">投稿数</th>
        <th style="width:160px;">登録日</th>
        <th style="width:220px;">権限 / 操作</th>
      </tr>
    </thead>
    <tbody>
      @forelse($users as $u)
        <tr>
          <td class="hide-sm">{{ $u->id }}</td>

          <td>
            <div class="user-name">{{ $u->name ?? '（未設定）' }}</div>
            <div class="muted">
              @if($u->email_verified_at)
                <span class="badge badge--verified">メール認証済</span>
              @else
                <span class="badge badge--unverified">未認証</span>
              @endif
            </div>
          </td>

          <td class="hide-sm">{{ $u->email }}</td>

          <td class="hide-sm" style="text-align:right;">
            {{ number_format($u->photos_count ?? 0) }}
          </td>

          <td>{{ optional($u->created_at)->format('Y/m/d') }}</td>

          <td>
            <div class="role-and-actions">
              <span class="badge {{ $u->is_admin ? 'badge--admin' : 'badge--member' }}">
                {{ $u->is_admin ? '管理者' : '一般' }}
              </span>

              <div class="actions">
                {{-- ルートが存在する場合のみ表示（任意機能） --}}
                @if(!$u->is_admin && Route::has('admin.users.promote'))
                  <form method="POST" action="{{ route('admin.users.promote', $u->id) }}"
                        onsubmit="return confirm('このユーザーを管理者にしますか？');">
                    @csrf @method('PUT')
                    <button type="submit" class="btn btn--promote">管理者に昇格</button>
                  </form>
                @endif

                @if($u->is_admin && Route::has('admin.users.demote'))
                  <form method="POST" action="{{ route('admin.users.demote', $u->id) }}"
                        onsubmit="return confirm('このユーザーを一般に戻しますか？');">
                    @csrf @method('PUT')
                    <button type="submit" class="btn btn--demote">一般に降格</button>
                  </form>
                @endif

                @if(Route::has('admin.users.destroy'))
                  <form method="POST" action="{{ route('admin.users.destroy', $u->id) }}"
                        onsubmit="return confirm('このユーザーを削除しますか？（取り消し不可）');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn--delete">削除</button>
                  </form>
                @endif
              </div>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="empty">該当するユーザーがいません。</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  {{-- ページネーション --}}
  <div class="pagination">
    {{ $users->appends(['role'=>request('role'),'q'=>request('q')])->links() }}
  </div>
</div>
@endsection
