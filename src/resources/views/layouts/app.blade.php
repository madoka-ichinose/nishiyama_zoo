<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>西山動物園</title>
  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/common.css') }}">
  @yield('css')
</head>
<body>
    <div class="wrapper">
    <header class="header">
    <div class="header__inner">
      <a class="header__logo" href="{{ route('top') }}">西山動物園 Nishiyama Zoo</a>

      <nav class="header-nav">
        <ul class="header-nav__list">
          {{-- 共通（誰でも） --}}
          <li class="header-nav__item">
            <a class="header-nav__link" href="{{ route('photos.index') }}">写真</a>
          </li>
          
          <li class="header-nav__item">
            <a class="header-nav__link" href="{{ route('info.index') }}">インフォメーション</a>
          </li>

          @guest
            {{-- ログイン前 --}}
            <li class="header-nav__item">
              <a class="header-nav__link" href="{{ route('login') }}">ログイン</a>
            </li>
          @endguest

          @auth
            {{-- ログイン後のみ --}}
            <li class="header-nav__item">
              <a class="header-nav__link" href="{{ route('photos.create') }}">投稿</a>
            </li>
            <li class="header-nav__item">
              <a class="header-nav__link" href="{{ route('mypage.index') }}">マイページ</a>
            </li>

            @if(auth()->user()->is_admin ?? false)
              {{-- 任意：管理者への導線 --}}
              <li class="header-nav__item">
                <a class="header-nav__link" href="{{ route('admin.dashboard') }}">管理</a>
              </li>
            @endif

            <li class="header-nav__item">
              <form class="header-nav__form" action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="header-nav__button">ログアウト</button>
              </form>
            </li>
          @endauth
        </ul>
      </nav>
    </div>
  </header>

  <main class="main">
    @yield('content')
  </main>

  <section class="footband">
    <div class="footband__inner">
      <div class="footer_name">西山動物園</div>
      <div footer_address>〒916-0027 福井県鯖江市桜町3-8-9<br>
      TEL 0778-52-2737<br>
      開演時間 9時～16時30分<br>
      定休日 毎週月曜日（祝日・祭日の場合は火曜日）および年末年始12月29日から1月3日まで
      </div>
    </div>
  </section>
</div>
@stack('scripts')
</body>
</html>
