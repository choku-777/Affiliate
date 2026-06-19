{{--
  サイト共通テーマ（トップLPの色合い・書体に合わせる）。
  layouts/app.blade.php と layouts/admin.blade.php の <head> で @include する（単一の真実源）。
  Bootstrap 5.3.3 CDN の「後ろ」に読み込むこと（上書きのため）。
  ※ ステータスの意味色（success/danger/warning/info/secondary）は意図的に触らない。
--}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Shippori+Mincho:wght@600;700&family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap" rel="stylesheet">
<style>
    :root {
        --brand-paper:      #f4eee1;
        --brand-paper-2:    #faf6ec;
        --brand-ink:        #2c2820;
        --brand-green:      #56653f;
        --brand-green-deep: #485438;
        --brand-terra:      #c1784c;
        --brand-terra-deep: #a85f38;
        --brand-gold:       #e6b566;
    }

    /* 背景・文字・書体 */
    body { background-color: var(--brand-paper); color: var(--brand-ink); font-family: 'Zen Kaku Gothic New', system-ui, sans-serif; }
    .bg-light { background-color: var(--brand-paper) !important; }
    h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6, .navbar-brand {
        font-family: 'Shippori Mincho', serif; letter-spacing: .01em;
    }

    /* ナビバー：ダーク → オリーブ緑 */
    .navbar.bg-dark { background-color: var(--brand-green) !important; }

    /* 本文中のリンク（ナビ・ボタンは別ルールが優先されるので影響しない） */
    main a:not(.btn):not(.nav-link):not(.navbar-brand) { color: var(--brand-terra-deep); }
    main a:not(.btn):not(.nav-link):not(.navbar-brand):hover { color: var(--brand-terra); }

    /* プライマリボタン → テラコッタ（Bootstrap5.3のコンポーネント変数で上書き） */
    .btn-primary {
        --bs-btn-bg: var(--brand-terra); --bs-btn-border-color: var(--brand-terra);
        --bs-btn-hover-bg: var(--brand-terra-deep); --bs-btn-hover-border-color: var(--brand-terra-deep);
        --bs-btn-active-bg: var(--brand-terra-deep); --bs-btn-active-border-color: var(--brand-terra-deep);
        --bs-btn-disabled-bg: var(--brand-terra); --bs-btn-disabled-border-color: var(--brand-terra);
    }
    .btn-outline-primary {
        --bs-btn-color: var(--brand-terra-deep); --bs-btn-border-color: var(--brand-terra);
        --bs-btn-hover-bg: var(--brand-terra); --bs-btn-hover-border-color: var(--brand-terra);
        --bs-btn-active-bg: var(--brand-terra-deep); --bs-btn-active-border-color: var(--brand-terra-deep);
    }

    /* primary 系の塗り（ロールバッジ・統計カード等）→ 世界観の緑。※成果/承認の意味色には使われていない */
    .bg-primary { background-color: var(--brand-green) !important; }
    .text-bg-primary { background-color: var(--brand-green) !important; color: #fff !important; }
    .text-primary { color: var(--brand-terra-deep) !important; }
    .border-primary { border-color: var(--brand-terra) !important; }

    /* カード：角丸・温かみのある枠・紙白 */
    .card {
        --bs-card-bg: #fffdf7;
        --bs-card-border-color: rgba(44,40,32,.10);
        --bs-card-border-radius: 16px;
        box-shadow: 0 14px 32px -22px rgba(44,40,32,.25);
    }

    /* フォーム：フォーカスをテラコッタに */
    .form-control:focus, .form-select:focus, .form-check-input:focus {
        border-color: var(--brand-terra);
        box-shadow: 0 0 0 .2rem rgba(193,120,76,.20);
    }
    .form-check-input:checked { background-color: var(--brand-terra); border-color: var(--brand-terra); }

    /* テーブル見出しの罫線を少し締める */
    .table > :not(caption) > * > * { border-bottom-color: rgba(44,40,32,.12); }
</style>
