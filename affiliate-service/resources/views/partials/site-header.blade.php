{{--
  公開ページ共通ヘッダー（トップと統一）。
  landing.blade.php と layouts/app.blade.php の両方で @include する（単一の真実源）。
  馬肉特急・うましっぽともに文字、先頭に「馬」マーク、末尾に「アンバサダー」。
  右側はログイン状態で出し分け（ログイン中＝マイページ/お問い合わせ/ログアウト、未ログイン＝お問い合わせ/ログイン）。
  Bootstrap非依存（landing は Bootstrap を読まないため、必要CSSは自己内包）。
--}}
<style>
  .site-header{position:sticky;top:0;z-index:60;backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);background:rgba(72,84,56,0.72);border-bottom:1px solid rgba(255,255,255,0.10);}
  .site-header__inner{max-width:1180px;margin:0 auto;padding:11px clamp(18px,5vw,56px);display:flex;align-items:center;justify-content:space-between;gap:12px;}
  .site-header__brand{display:flex;align-items:center;gap:10px;text-decoration:none;min-width:0;}
  .site-header__badge{flex:none;width:34px;height:34px;border-radius:50%;border:1px solid rgba(230,181,102,0.55);display:flex;align-items:center;justify-content:center;font-family:'Shippori Mincho',serif;font-weight:700;font-size:16px;color:#e6b566;}
  .site-header__name{font-family:'Shippori Mincho',serif;font-weight:600;font-size:clamp(13px,1.5vw,15px);letter-spacing:0.04em;color:#f4eee1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
  .site-header__name .sep{opacity:0.45;margin:0 4px;}
  .site-header__name .amb{opacity:0.82;margin-left:6px;font-size:.82em;}
  .site-header__nav{display:flex;align-items:center;gap:8px;flex-wrap:nowrap;flex:none;}
  .sh-link{font-size:13px;color:rgba(244,238,225,0.85);text-decoration:none;padding:9px 14px;border-radius:999px;transition:background .2s ease;}
  .sh-link:hover{background:rgba(255,255,255,0.10);color:#fff;}
  .sh-btn{font-size:13px;font-weight:700;color:#4e5c3c;background:#f4eee1;border:0;text-decoration:none;padding:9px 18px;border-radius:999px;transition:transform .2s ease;cursor:pointer;}
  .sh-btn:hover{transform:translateY(-1px);color:#4e5c3c;}
  .sh-btn--ghost{background:transparent;color:#f4eee1;border:1px solid rgba(244,238,225,0.5);font-weight:600;}
  .sh-btn--ghost:hover{background:rgba(255,255,255,0.10);color:#fff;}
  @media (max-width:575.98px){
    .site-header__inner{padding:9px 14px;gap:8px;}
    .site-header__name{font-size:12.5px;}
    .site-header__name .sep{margin:0 3px;}
    .site-header__name .amb{display:none;}
    .site-header__badge{width:30px;height:30px;font-size:14px;}
    .site-header__nav{gap:6px;}
    .sh-link--contact{display:none;}
    .sh-link{font-size:12px;padding:8px 10px;}
    .sh-btn{font-size:12px;padding:8px 14px;}
  }
</style>
<header class="site-header">
  <div class="site-header__inner">
    <a href="{{ route('home') }}" class="site-header__brand">
      <span class="site-header__badge" aria-hidden="true">馬</span>
      <span class="site-header__name">馬肉特急 <span class="sep">·</span> うましっぽ <span class="amb">アンバサダー</span></span>
    </a>
    <nav class="site-header__nav">
      @if (session('affiliate_authenticated'))
        <a class="sh-link" href="{{ route('affiliate.mypage') }}">マイページ</a>
        <a class="sh-link sh-link--contact" href="{{ route('inquiry.create') }}">お問い合わせ</a>
        <form method="post" action="{{ route('affiliate.logout') }}" style="display:inline;margin:0;">
          @csrf
          <button type="submit" class="sh-btn sh-btn--ghost">ログアウト</button>
        </form>
      @else
        <a class="sh-link sh-link--contact" href="{{ route('inquiry.create') }}">お問い合わせ</a>
        <a class="sh-btn" href="{{ route('affiliate.login') }}">ログイン</a>
      @endif
    </nav>
  </div>
</header>
