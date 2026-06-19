{{--
    アンバサダー募集LP。
    デザイン「アンバサダーLP.dc.html」（claude.ai/design）を実装。
    画像は差し替え用プレースホルダー（class="lp-img"）。後で <img src="..."> に置き換える。
--}}
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>アンバサダー募集 | 馬肉特急 · 自然派いぬ生活</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Shippori+Mincho:wght@500;600;700;800&family=Zen+Kaku+Gothic+New:wght@400;500;700&family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500;1,600&display=swap" rel="stylesheet">
<style>
  html,body{margin:0;padding:0;}
  body{background:#f4eee1;-webkit-font-smoothing:antialiased;text-rendering:optimizeLegibility;}
  *{box-sizing:border-box;}
  ::selection{background:#c1784c;color:#fff;}
  ul{list-style:none;margin:0;padding:0;}
  a{-webkit-tap-highlight-color:transparent;}
  /* ホバー（デザインの style-hover を CSS 化） */
  .lp-navlink{transition:background .2s ease;}
  .lp-navlink:hover{background:rgba(255,255,255,0.08);}
  .lp-loginbtn{transition:transform .2s ease;}
  .lp-loginbtn:hover{transform:translateY(-1px);}
  .lp-cta{transition:transform .25s ease, box-shadow .25s ease;}
  .lp-cta:hover{transform:translateY(-2px);box-shadow:0 22px 46px -10px rgba(193,120,76,0.78);}
  .lp-card{transition:transform .25s ease, box-shadow .25s ease;}
  .lp-card:hover{transform:translateY(-4px);box-shadow:0 22px 44px -22px rgba(44,40,32,0.28);}
  /* 画像プレースホルダー（あとで <img> に差し替え） */
  .lp-img{position:relative;width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:repeating-linear-gradient(45deg, rgba(44,40,32,.045) 0 12px, rgba(44,40,32,.075) 12px 24px), #ece3d2;color:rgba(44,40,32,.5);font-family:'Zen Kaku Gothic New',sans-serif;font-size:12px;font-weight:700;text-align:center;padding:8px;letter-spacing:.04em;}
  /* FAQ（ネイティブ details で依存ゼロ） */
  .lp-faq summary{list-style:none;cursor:pointer;}
  .lp-faq summary::-webkit-details-marker{display:none;}
  .lp-faq-plus{display:inline-block;flex:none;font-size:22px;line-height:1;font-weight:300;transition:transform .35s ease, color .35s ease;color:#b3a994;}
  .lp-faq details[open] .lp-faq-plus{transform:rotate(135deg);color:#c1784c;}
  .lp-faq-a{overflow:hidden;}
  a:focus-visible,button:focus-visible,summary:focus-visible{outline:3px solid #c1784c;outline-offset:3px;border-radius:8px;}
  @media (prefers-reduced-motion: reduce){.lp-cta,.lp-card,.lp-faq-plus{transition:none;}}
</style>
</head>
<body>

<div style="font-family:'Zen Kaku Gothic New',sans-serif;color:#2c2820;background:#f4eee1;">

  <!-- ================= HEADER ================= -->
  <header style="position:sticky;top:0;z-index:60;backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);background:rgba(72,84,56,0.72);border-bottom:1px solid rgba(255,255,255,0.10);">
    <div style="max-width:1180px;margin:0 auto;padding:13px clamp(18px,5vw,56px);display:flex;align-items:center;justify-content:space-between;gap:16px;">
      <div style="display:flex;align-items:center;gap:11px;">
        <span style="flex:none;width:34px;height:34px;border-radius:50%;border:1px solid rgba(230,181,102,0.55);display:flex;align-items:center;justify-content:center;font-family:'Shippori Mincho',serif;font-weight:700;font-size:16px;color:#e6b566;">馬</span>
        <span style="font-family:'Shippori Mincho',serif;font-weight:600;font-size:clamp(13px,1.5vw,16px);letter-spacing:0.04em;color:#f4eee1;">馬肉特急 <span style="opacity:0.45;margin:0 2px;">·</span> 自然派いぬ生活</span>
      </div>
      <nav style="display:flex;align-items:center;gap:8px;">
        <a href="{{ route('inquiry.create') }}" class="lp-navlink" style="font-size:13px;color:rgba(244,238,225,0.82);text-decoration:none;padding:9px 14px;border-radius:999px;">お問い合わせ</a>
        <a href="{{ route('affiliate.login') }}" class="lp-loginbtn" style="font-size:13px;font-weight:700;color:#4e5c3c;background:#f4eee1;text-decoration:none;padding:9px 18px;border-radius:999px;">ログイン</a>
      </nav>
    </div>
  </header>

  <!-- ================= HERO ================= -->
  <section style="position:relative;overflow:hidden;background:#56653f;color:#f7f3e8;">
    <div style="position:absolute;top:-25%;right:-12%;width:62%;height:130%;background:radial-gradient(closest-side, rgba(193,120,76,0.22), transparent 72%);pointer-events:none;"></div>
    <div style="position:absolute;bottom:-30%;left:-15%;width:55%;height:120%;background:radial-gradient(closest-side, rgba(230,181,102,0.10), transparent 70%);pointer-events:none;"></div>
    <div style="position:relative;max-width:1120px;margin:0 auto;padding:clamp(52px,7vw,104px) clamp(20px,5vw,56px);display:flex;flex-wrap:wrap;align-items:center;gap:clamp(36px,5vw,72px);">
      <div style="flex:1 1 460px;min-width:280px;">
        <div style="display:inline-flex;align-items:center;gap:13px;margin-bottom:26px;">
          <span style="width:34px;height:1px;background:#c1784c;display:inline-block;"></span>
          <span style="font-size:11.5px;letter-spacing:0.22em;text-transform:uppercase;color:#cdb89a;font-weight:600;">馬肉を愛する人のアンバサダー</span>
        </div>
        <h1 style="font-family:'Shippori Mincho',serif;font-weight:700;font-size:clamp(1.85rem,3.9vw,3.05rem);line-height:1.42;letter-spacing:0.01em;word-break:keep-all;overflow-wrap:break-word;margin:0 0 26px;">“馬肉”を紹介して、<br><span style="color:#e6b566;">報酬</span>を受け取る。</h1>
        <p style="font-size:clamp(14px,1.5vw,16px);line-height:2.05;color:rgba(244,238,225,0.82);max-width:31em;margin:0 0 38px;">人が食べる新鮮な馬刺し「馬肉特急」と、愛犬のための自然派フード「自然派いぬ生活」。どちらも“馬肉”がテーマの2ブランドを紹介して、報酬を受け取れるアンバサダープログラムです。</p>
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:22px;">
          <a href="{{ route('register.create') }}" class="lp-cta" style="display:inline-flex;align-items:center;gap:10px;background:#c1784c;color:#fff;font-weight:700;font-size:clamp(15px,1.6vw,17px);padding:17px 34px;border-radius:999px;text-decoration:none;box-shadow:0 14px 34px -10px rgba(193,120,76,0.65);">無料ではじめる <span style="font-size:18px;">→</span></a>
          <span style="font-size:13px;color:rgba(244,238,225,0.72);">すでに登録済みの方は <a href="{{ route('affiliate.login') }}" style="color:#e6b566;text-decoration:underline;text-underline-offset:3px;">こちらからログイン</a></span>
        </div>
      </div>
      <div style="flex:0 1 380px;min-width:260px;max-width:420px;position:relative;">
        <div style="position:absolute;inset:-14px -14px 14px 14px;border:1px solid rgba(230,181,102,0.38);border-radius:26px;pointer-events:none;"></div>
        <div style="position:relative;border-radius:22px;overflow:hidden;aspect-ratio:4/5;box-shadow:0 34px 64px -22px rgba(0,0,0,0.55);">
          {{-- 画像：public/images/landing/hero.jpg（縦長 4:5 推奨） --}}
          <img src="{{ asset('images/landing/hero.jpg') }}" alt="メインビジュアル" style="width:100%;height:100%;object-fit:cover;display:block;">
        </div>
        <div style="position:absolute;left:-30px;bottom:34px;width:142px;height:142px;border-radius:50%;background:radial-gradient(circle at 35% 28%, #f3d792, #e6b566 52%, #d29a42);color:#3a2f1a;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1px;box-shadow:0 22px 44px -12px rgba(0,0,0,0.55), inset 0 0 0 2px rgba(255,255,255,0.55), inset 0 0 0 8px rgba(58,47,26,0.12);transform:rotate(-9deg);">
          <span style="font-size:12px;font-weight:700;letter-spacing:0.2em;">最大</span>
          <span style="font-family:'Shippori Mincho',serif;font-weight:800;font-size:46px;line-height:0.9;letter-spacing:-0.02em;">{{ $maxRateLabel }}<span style="font-size:22px;">%</span></span>
          <span style="font-size:13px;font-weight:700;letter-spacing:0.24em;">還元</span>
        </div>
      </div>
    </div>
  </section>

  <!-- ================= REWARD RATES ================= -->
  <section style="position:relative;background:#f4eee1;">
    <div style="max-width:1080px;margin:0 auto;padding:clamp(64px,9vw,118px) clamp(20px,5vw,56px);">
      <div style="text-align:center;">
        <div style="font-family:'Cormorant Garamond',serif;font-style:italic;font-size:clamp(18px,2vw,22px);letter-spacing:0.04em;color:#c1784c;margin-bottom:8px;">Special rates</div>
        <h2 style="font-family:'Shippori Mincho',serif;font-weight:700;font-size:clamp(1.7rem,3.6vw,2.5rem);line-height:1.4;letter-spacing:0.02em;margin:0;">いま登録すると、高還元率。</h2>
        <p style="font-size:clamp(13px,1.5vw,15px);line-height:1.9;color:#6f685c;max-width:30em;margin:16px auto 0;">紹介された購入額に対する、いまだけの特別なアンバサダー報酬率です。</p>
      </div>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:clamp(18px,2.5vw,28px);margin-top:clamp(38px,5vw,56px);">
        <div style="position:relative;overflow:hidden;background:linear-gradient(158deg,#69794c,#566640);color:#f7f3e8;border-radius:24px;padding:clamp(30px,3.5vw,42px);border:1px solid rgba(255,255,255,0.06);box-shadow:0 26px 54px -26px rgba(86,102,64,0.5);">
          <span style="position:absolute;top:22px;right:22px;font-size:11px;font-weight:700;letter-spacing:0.08em;background:rgba(230,181,102,0.92);color:#3a2f1a;padding:6px 12px;border-radius:999px;">高還元</span>
          <div style="font-weight:700;font-size:19px;">自然派いぬ生活</div>
          <div style="font-size:12.5px;color:rgba(244,238,225,0.68);margin-top:5px;">愛犬のための自然派フード</div>
          <div style="display:flex;align-items:baseline;gap:5px;margin:22px 0 12px;">
            <span style="font-family:'Shippori Mincho',serif;font-weight:700;font-size:clamp(3.6rem,8vw,5.2rem);line-height:0.9;">{{ $inuRateLabel }}</span>
            <span style="font-family:'Shippori Mincho',serif;font-size:1.7rem;">%</span>
          </div>
          <div style="font-size:13px;color:rgba(244,238,225,0.8);">紹介された ご購入額の <strong style="color:#e6b566;font-weight:700;">{{ $inuRateLabel }}%</strong> が報酬</div>
        </div>
        <div style="position:relative;overflow:hidden;background:linear-gradient(158deg,#c8814f,#a85f38);color:#fff;border-radius:24px;padding:clamp(30px,3.5vw,42px);border:1px solid rgba(255,255,255,0.12);box-shadow:0 26px 54px -26px rgba(168,95,56,0.5);">
          <div style="font-weight:700;font-size:19px;">馬肉特急</div>
          <div style="font-size:12.5px;color:rgba(255,255,255,0.78);margin-top:5px;">人が食べる、新鮮な馬刺し</div>
          <div style="display:flex;align-items:baseline;gap:5px;margin:22px 0 12px;">
            <span style="font-family:'Shippori Mincho',serif;font-weight:700;font-size:clamp(3.6rem,8vw,5.2rem);line-height:0.9;">{{ $umaRateLabel }}</span>
            <span style="font-family:'Shippori Mincho',serif;font-size:1.7rem;">%</span>
          </div>
          <div style="font-size:13px;color:rgba(255,255,255,0.88);">紹介された ご購入額の <strong style="font-weight:700;">{{ $umaRateLabel }}%</strong> が報酬</div>
        </div>
      </div>
      <p style="text-align:center;font-size:12px;color:#9a9079;margin:26px 0 0;">※ 期間限定の報酬料率です（終了時は事前にお知らせをします）。</p>
    </div>
  </section>

  <!-- ================= BENEFITS ================= -->
  <section style="background:#faf6ec;border-top:1px solid rgba(44,40,32,0.06);">
    <div style="max-width:1120px;margin:0 auto;padding:clamp(64px,9vw,118px) clamp(20px,5vw,56px);">
      <div style="text-align:center;margin-bottom:clamp(38px,5vw,56px);">
        <div style="font-family:'Cormorant Garamond',serif;font-style:italic;font-size:clamp(18px,2vw,22px);letter-spacing:0.04em;color:#c1784c;margin-bottom:8px;">Why it's easy</div>
        <h2 style="font-family:'Shippori Mincho',serif;font-weight:700;font-size:clamp(1.7rem,3.6vw,2.5rem);line-height:1.4;letter-spacing:0.02em;margin:0;">はじめやすくて、つづけやすい。</h2>
      </div>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:clamp(16px,2vw,22px);">
        <div class="lp-card" style="background:#fffdf7;border:1px solid rgba(44,40,32,0.07);border-radius:20px;padding:30px 26px;box-shadow:0 14px 32px -20px rgba(44,40,32,0.22);">
          <div style="width:56px;height:56px;border-radius:50%;background:#eef0e4;display:flex;align-items:center;justify-content:center;font-family:'Shippori Mincho',serif;font-weight:700;font-size:18px;color:#4c5a3a;margin-bottom:20px;">¥0</div>
          <div style="font-weight:700;font-size:16px;margin-bottom:9px;">はじめる費用は0円</div>
          <p style="font-size:13px;line-height:1.85;color:#6f685c;margin:0;">登録も利用も無料。在庫を持つ必要もありません。</p>
        </div>
        <div class="lp-card" style="background:#fffdf7;border:1px solid rgba(44,40,32,0.07);border-radius:20px;padding:30px 26px;box-shadow:0 14px 32px -20px rgba(44,40,32,0.22);">
          <div style="width:56px;height:56px;border-radius:50%;background:#eef0e4;display:flex;align-items:center;justify-content:center;font-family:'Shippori Mincho',serif;font-weight:700;font-size:14px;color:#4c5a3a;margin-bottom:20px;">URL</div>
          <div style="font-weight:700;font-size:16px;margin-bottom:9px;">専用URLを送るだけ</div>
          <p style="font-size:13px;line-height:1.85;color:#6f685c;margin:0;">あなた専用の紹介URLをシェアするだけ。発送も対応も不要です。</p>
        </div>
        <div class="lp-card" style="background:#fffdf7;border:1px solid rgba(44,40,32,0.07);border-radius:20px;padding:30px 26px;box-shadow:0 14px 32px -20px rgba(44,40,32,0.22);">
          <div style="width:56px;height:56px;border-radius:50%;background:#eef0e4;display:flex;align-items:center;justify-content:center;font-family:'Shippori Mincho',serif;font-weight:700;font-size:15px;color:#4c5a3a;margin-bottom:20px;">10日</div>
          <div style="font-weight:700;font-size:16px;margin-bottom:9px;">翌月10日に振込</div>
          <p style="font-size:13px;line-height:1.85;color:#6f685c;margin:0;">月末で締めて、翌月10日に銀行口座へ。最低{{ $minPayoutLabel }}円から。</p>
        </div>
        <div class="lp-card" style="background:#fffdf7;border:1px solid rgba(44,40,32,0.07);border-radius:20px;padding:30px 26px;box-shadow:0 14px 32px -20px rgba(44,40,32,0.22);">
          <div style="width:56px;height:56px;border-radius:50%;background:#f1e6dc;display:flex;align-items:center;justify-content:center;font-family:'Shippori Mincho',serif;font-weight:700;font-size:16px;color:#a85f38;margin-bottom:20px;">馬肉</div>
          <div style="font-weight:700;font-size:16px;margin-bottom:9px;">馬肉テーマで選べる</div>
          <p style="font-size:13px;line-height:1.85;color:#6f685c;margin:0;">人用の馬刺しと、犬用の馬肉フード。読者に合う方を紹介できます。</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ================= STEPS ================= -->
  <section style="position:relative;overflow:hidden;background:#62714a;color:#f7f3e8;">
    <div style="position:relative;max-width:1080px;margin:0 auto;padding:clamp(60px,8vw,104px) clamp(20px,5vw,56px);">
      <div style="text-align:center;margin-bottom:clamp(40px,5vw,60px);">
        <div style="font-family:'Cormorant Garamond',serif;font-style:italic;font-size:clamp(18px,2vw,22px);letter-spacing:0.04em;color:#e6b566;margin-bottom:8px;">How it works</div>
        <h2 style="font-family:'Shippori Mincho',serif;font-weight:700;font-size:clamp(1.7rem,3.6vw,2.5rem);line-height:1.4;letter-spacing:0.02em;margin:0;">はじめ方は、3歩。</h2>
      </div>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:clamp(20px,3vw,40px);">
        <div style="position:relative;">
          <div style="position:relative;aspect-ratio:16/10;border-radius:16px;overflow:hidden;margin-bottom:18px;box-shadow:0 16px 34px -22px rgba(0,0,0,0.5);">
            {{-- 画像：public/images/landing/step-1.jpg（横長 16:10 推奨） --}}
            <img src="{{ asset('images/landing/step-1.jpg') }}" alt="STEP 01" style="width:100%;height:100%;object-fit:cover;display:block;">
            <span style="position:absolute;top:12px;left:12px;width:38px;height:38px;border-radius:50%;background:rgba(35,44,28,0.82);-webkit-backdrop-filter:blur(4px);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond',serif;font-size:20px;color:#e6b566;">01</span>
          </div>
          <div style="font-size:11px;letter-spacing:0.2em;color:#e6b566;font-weight:600;margin-bottom:10px;">STEP 01</div>
          <div style="font-family:'Shippori Mincho',serif;font-weight:600;font-size:19px;margin-bottom:11px;">登録する</div>
          <p style="font-size:13.5px;line-height:1.9;color:rgba(244,238,225,0.78);margin:0;">フォームから申し込み。承認後、あなた専用の紹介URLをメールでお届けします。</p>
        </div>
        <div style="position:relative;">
          <div style="position:relative;aspect-ratio:16/10;border-radius:16px;overflow:hidden;margin-bottom:18px;box-shadow:0 16px 34px -22px rgba(0,0,0,0.5);">
            {{-- 画像：public/images/landing/step-2.jpg（横長 16:10 推奨） --}}
            <img src="{{ asset('images/landing/step-2.jpg') }}" alt="STEP 02" style="width:100%;height:100%;object-fit:cover;display:block;">
            <span style="position:absolute;top:12px;left:12px;width:38px;height:38px;border-radius:50%;background:rgba(35,44,28,0.82);-webkit-backdrop-filter:blur(4px);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond',serif;font-size:20px;color:#e6b566;">02</span>
          </div>
          <div style="font-size:11px;letter-spacing:0.2em;color:#e6b566;font-weight:600;margin-bottom:10px;">STEP 02</div>
          <div style="font-family:'Shippori Mincho',serif;font-weight:600;font-size:19px;margin-bottom:11px;">紹介する</div>
          <p style="font-size:13.5px;line-height:1.9;color:rgba(244,238,225,0.78);margin:0;">SNS・ブログ・お便りなどに、あなたの紹介URLをシェアするだけ。</p>
        </div>
        <div style="position:relative;">
          <div style="position:relative;aspect-ratio:16/10;border-radius:16px;overflow:hidden;margin-bottom:18px;box-shadow:0 16px 34px -22px rgba(0,0,0,0.5);">
            {{-- 画像：public/images/landing/step-3.jpg（横長 16:10 推奨） --}}
            <img src="{{ asset('images/landing/step-3.jpg') }}" alt="STEP 03" style="width:100%;height:100%;object-fit:cover;display:block;">
            <span style="position:absolute;top:12px;left:12px;width:38px;height:38px;border-radius:50%;background:rgba(35,44,28,0.82);-webkit-backdrop-filter:blur(4px);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond',serif;font-size:20px;color:#e6b566;">03</span>
          </div>
          <div style="font-size:11px;letter-spacing:0.2em;color:#e6b566;font-weight:600;margin-bottom:10px;">STEP 03</div>
          <div style="font-family:'Shippori Mincho',serif;font-weight:600;font-size:19px;margin-bottom:11px;">受け取る</div>
          <p style="font-size:13.5px;line-height:1.9;color:rgba(244,238,225,0.78);margin:0;">紹介経由のご購入で報酬が発生。{{ $confirmDays }}日後に確定し、翌月10日に振り込まれます。</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ================= BRANDS ================= -->
  <section style="background:#f4eee1;">
    <div style="max-width:1120px;margin:0 auto;padding:clamp(64px,9vw,118px) clamp(20px,5vw,56px);">
      <div style="text-align:center;margin-bottom:clamp(44px,6vw,68px);">
        <div style="font-family:'Cormorant Garamond',serif;font-style:italic;font-size:clamp(18px,2vw,22px);letter-spacing:0.04em;color:#c1784c;margin-bottom:8px;">Two brands</div>
        <h2 style="font-family:'Shippori Mincho',serif;font-weight:700;font-size:clamp(1.7rem,3.6vw,2.5rem);line-height:1.4;letter-spacing:0.02em;margin:0;">紹介できる、2つのブランド。</h2>
        <p style="font-size:clamp(13px,1.5vw,15px);line-height:1.9;color:#6f685c;max-width:30em;margin:16px auto 0;">どちらも“馬肉”がテーマ。読者や愛犬に合わせて選べます。</p>
      </div>

      <!-- Brand 1: dogs -->
      <div style="display:flex;flex-wrap:wrap;align-items:center;gap:clamp(28px,4vw,56px);margin-bottom:clamp(48px,6vw,80px);">
        <div style="flex:1 1 320px;min-width:260px;display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div style="grid-column:1 / -1;aspect-ratio:16/10;border-radius:18px;overflow:hidden;box-shadow:0 18px 38px -22px rgba(44,40,32,0.4);">
            {{-- 画像：public/images/landing/inu-main.jpg（横長 16:10 推奨） --}}
            <img src="{{ asset('images/landing/inu-main.jpg') }}" alt="自然派いぬ生活 商品" style="width:100%;height:100%;object-fit:cover;display:block;">
          </div>
          <div style="aspect-ratio:1/1;border-radius:16px;overflow:hidden;box-shadow:0 14px 30px -20px rgba(44,40,32,0.38);">
            {{-- 画像：public/images/landing/inu-1.jpg（正方形 1:1 推奨） --}}
            <img src="{{ asset('images/landing/inu-1.jpg') }}" alt="自然派いぬ生活 サブ1" style="width:100%;height:100%;object-fit:cover;display:block;">
          </div>
          <div style="aspect-ratio:1/1;border-radius:16px;overflow:hidden;box-shadow:0 14px 30px -20px rgba(44,40,32,0.38);">
            {{-- 画像：public/images/landing/inu-2.jpg（正方形 1:1 推奨） --}}
            <img src="{{ asset('images/landing/inu-2.jpg') }}" alt="自然派いぬ生活 サブ2" style="width:100%;height:100%;object-fit:cover;display:block;">
          </div>
        </div>
        <div style="flex:1 1 320px;min-width:260px;">
          <div style="display:inline-flex;align-items:center;gap:10px;margin-bottom:14px;">
            <span style="font-family:'Cormorant Garamond',serif;font-style:italic;font-size:18px;letter-spacing:0.06em;color:#5b6b41;">For Dogs</span>
            <span style="font-size:11px;letter-spacing:0.16em;color:#9a9079;border-left:1px solid rgba(44,40,32,0.18);padding-left:10px;">ペットフード</span>
          </div>
          <h3 style="font-family:'Shippori Mincho',serif;font-weight:700;font-size:clamp(1.5rem,2.8vw,2rem);margin:0 0 14px;">自然派いぬ生活</h3>
          <p style="font-size:14px;line-height:1.95;color:#5a5348;margin:0 0 22px;">愛犬の健康を考えた、自然派の馬肉フードとケア用品。毎日の食といたわりを届けるブランドです。</p>
          <ul>
            <li style="display:flex;gap:12px;align-items:flex-start;margin-bottom:12px;font-size:14px;line-height:1.7;color:#4a443a;"><span style="flex:none;width:7px;height:7px;border-radius:50%;background:#5b6b41;margin-top:7px;"></span>国産・自然派にこだわった犬用の馬肉フード</li>
            <li style="display:flex;gap:12px;align-items:flex-start;margin-bottom:12px;font-size:14px;line-height:1.7;color:#4a443a;"><span style="flex:none;width:7px;height:7px;border-radius:50%;background:#5b6b41;margin-top:7px;"></span>毎日のごはんだから、リピート購入が生まれやすい</li>
            <li style="display:flex;gap:12px;align-items:flex-start;font-size:14px;line-height:1.7;color:#4a443a;"><span style="flex:none;width:7px;height:7px;border-radius:50%;background:#5b6b41;margin-top:7px;"></span>愛犬家の読者・フォロワーと相性◎</li>
          </ul>
        </div>
      </div>

      <!-- Brand 2: people -->
      <div style="display:flex;flex-wrap:wrap-reverse;align-items:center;gap:clamp(28px,4vw,56px);">
        <div style="flex:1 1 320px;min-width:260px;">
          <div style="display:inline-flex;align-items:center;gap:10px;margin-bottom:14px;">
            <span style="font-family:'Cormorant Garamond',serif;font-style:italic;font-size:18px;letter-spacing:0.06em;color:#c1784c;">For People</span>
            <span style="font-size:11px;letter-spacing:0.16em;color:#9a9079;border-left:1px solid rgba(44,40,32,0.18);padding-left:10px;">馬刺し</span>
          </div>
          <h3 style="font-family:'Shippori Mincho',serif;font-weight:700;font-size:clamp(1.5rem,2.8vw,2rem);margin:0 0 14px;">馬肉特急</h3>
          <p style="font-size:14px;line-height:1.95;color:#5a5348;margin:0 0 22px;">人が食べる、新鮮な馬刺しのブランド。鮮度と品質にこだわった、ごちそうとしての馬肉です。</p>
          <ul>
            <li style="display:flex;gap:12px;align-items:flex-start;margin-bottom:12px;font-size:14px;line-height:1.7;color:#4a443a;"><span style="flex:none;width:7px;height:7px;border-radius:50%;background:#c1784c;margin-top:7px;"></span>人用の新鮮な馬刺し。ギフト・お取り寄せ需要も</li>
            <li style="display:flex;gap:12px;align-items:flex-start;margin-bottom:12px;font-size:14px;line-height:1.7;color:#4a443a;"><span style="flex:none;width:7px;height:7px;border-radius:50%;background:#c1784c;margin-top:7px;"></span>グルメ・お取り寄せ好きの読者と相性◎</li>
            <li style="display:flex;gap:12px;align-items:flex-start;font-size:14px;line-height:1.7;color:#4a443a;"><span style="flex:none;width:7px;height:7px;border-radius:50%;background:#c1784c;margin-top:7px;"></span>犬を飼っていない読者にも紹介できる</li>
          </ul>
        </div>
        <div style="flex:1 1 320px;min-width:260px;display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div style="grid-column:1 / -1;aspect-ratio:16/10;border-radius:18px;overflow:hidden;box-shadow:0 18px 38px -22px rgba(44,40,32,0.4);">
            {{-- 画像：public/images/landing/uma-main.jpg（横長 16:10 推奨） --}}
            <img src="{{ asset('images/landing/uma-main.jpg') }}" alt="馬肉特急 商品" style="width:100%;height:100%;object-fit:cover;display:block;">
          </div>
          <div style="aspect-ratio:1/1;border-radius:16px;overflow:hidden;box-shadow:0 14px 30px -20px rgba(44,40,32,0.38);">
            {{-- 画像：public/images/landing/uma-1.jpg（正方形 1:1 推奨） --}}
            <img src="{{ asset('images/landing/uma-1.jpg') }}" alt="馬肉特急 サブ1" style="width:100%;height:100%;object-fit:cover;display:block;">
          </div>
          <div style="aspect-ratio:1/1;border-radius:16px;overflow:hidden;box-shadow:0 14px 30px -20px rgba(44,40,32,0.38);">
            {{-- 画像：public/images/landing/uma-2.jpg（正方形 1:1 推奨） --}}
            <img src="{{ asset('images/landing/uma-2.jpg') }}" alt="馬肉特急 サブ2" style="width:100%;height:100%;object-fit:cover;display:block;">
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ================= REWARD & PAYMENT ================= -->
  <section style="background:#faf6ec;border-top:1px solid rgba(44,40,32,0.06);">
    <div style="max-width:780px;margin:0 auto;padding:clamp(64px,9vw,118px) clamp(20px,5vw,56px);">
      <div style="text-align:center;margin-bottom:clamp(36px,5vw,52px);">
        <div style="font-family:'Cormorant Garamond',serif;font-style:italic;font-size:clamp(18px,2vw,22px);letter-spacing:0.04em;color:#c1784c;margin-bottom:8px;">Reward &amp; Payment</div>
        <h2 style="font-family:'Shippori Mincho',serif;font-weight:700;font-size:clamp(1.7rem,3.6vw,2.5rem);line-height:1.4;letter-spacing:0.02em;margin:0;">報酬とお支払いのこと。</h2>
      </div>
      <div>
        <div style="display:flex;flex-wrap:wrap;gap:6px 28px;padding:22px 0;border-top:1px solid rgba(44,40,32,0.12);">
          <div style="flex:0 0 150px;font-family:'Shippori Mincho',serif;font-weight:600;font-size:16px;color:#2c2820;">報酬料率</div>
          <div style="flex:1 1 320px;font-size:14px;line-height:1.9;color:#5a5348;">自然派いぬ生活 {{ $inuRateLabel }}%／馬肉特急 {{ $umaRateLabel }}%（期間限定の特別料率）。例：自然派いぬ生活で{{ $exampleOrder }}円のご注文 → <strong style="color:#a85f38;">{{ $exampleReward }}円</strong></div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:6px 28px;padding:22px 0;border-top:1px solid rgba(44,40,32,0.12);">
          <div style="flex:0 0 150px;font-family:'Shippori Mincho',serif;font-weight:600;font-size:16px;color:#2c2820;">報酬の確定</div>
          <div style="flex:1 1 320px;font-size:14px;line-height:1.9;color:#5a5348;">発生から{{ $confirmDays }}日後、キャンセル・返品がなければ確定します。</div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:6px 28px;padding:22px 0;border-top:1px solid rgba(44,40,32,0.12);">
          <div style="flex:0 0 150px;font-family:'Shippori Mincho',serif;font-weight:600;font-size:16px;color:#2c2820;">締め／支払い</div>
          <div style="flex:1 1 320px;font-size:14px;line-height:1.9;color:#5a5348;">月末締め・翌月10日に銀行振込。</div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:6px 28px;padding:22px 0;border-top:1px solid rgba(44,40,32,0.12);border-bottom:1px solid rgba(44,40,32,0.12);">
          <div style="flex:0 0 150px;font-family:'Shippori Mincho',serif;font-weight:600;font-size:16px;color:#2c2820;">最低支払額</div>
          <div style="flex:1 1 320px;font-size:14px;line-height:1.9;color:#5a5348;">{{ $minPayoutLabel }}円。満たない分は翌月に繰り越します。</div>
        </div>
      </div>
    </div>
  </section>

  <!-- ================= FAQ ================= -->
  <section style="background:#f4eee1;">
    <div class="lp-faq" style="max-width:780px;margin:0 auto;padding:clamp(60px,8vw,110px) clamp(20px,5vw,56px);">
      <div style="text-align:center;margin-bottom:clamp(34px,5vw,50px);">
        <div style="font-family:'Cormorant Garamond',serif;font-style:italic;font-size:clamp(18px,2vw,22px);letter-spacing:0.04em;color:#c1784c;margin-bottom:8px;">FAQ</div>
        <h2 style="font-family:'Shippori Mincho',serif;font-weight:700;font-size:clamp(1.7rem,3.6vw,2.5rem);line-height:1.4;letter-spacing:0.02em;margin:0;">よくあるご質問。</h2>
      </div>

      <details style="background:#fffdf7;border:1px solid rgba(44,40,32,0.08);border-radius:14px;margin-bottom:12px;overflow:hidden;box-shadow:0 10px 26px -18px rgba(44,40,32,0.2);">
        <summary style="display:flex;align-items:center;justify-content:space-between;gap:16px;padding:20px 24px;font-family:'Zen Kaku Gothic New',sans-serif;font-weight:600;font-size:clamp(14px,1.6vw,16px);color:#2c2820;">費用はかかりますか？<span class="lp-faq-plus">＋</span></summary>
        <div class="lp-faq-a"><div style="padding:0 24px 22px;font-size:14px;line-height:1.95;color:#6f685c;">登録も利用も完全無料です。初期費用や月額費用は一切かかりません。</div></div>
      </details>
      <details style="background:#fffdf7;border:1px solid rgba(44,40,32,0.08);border-radius:14px;margin-bottom:12px;overflow:hidden;box-shadow:0 10px 26px -18px rgba(44,40,32,0.2);">
        <summary style="display:flex;align-items:center;justify-content:space-between;gap:16px;padding:20px 24px;font-family:'Zen Kaku Gothic New',sans-serif;font-weight:600;font-size:clamp(14px,1.6vw,16px);color:#2c2820;">報酬率はどのくらいですか？<span class="lp-faq-plus">＋</span></summary>
        <div class="lp-faq-a"><div style="padding:0 24px 22px;font-size:14px;line-height:1.95;color:#6f685c;">期間限定で、自然派いぬ生活が{{ $inuRateLabel }}%、馬肉特急が{{ $umaRateLabel }}%の特別料率です。</div></div>
      </details>
      <details style="background:#fffdf7;border:1px solid rgba(44,40,32,0.08);border-radius:14px;margin-bottom:12px;overflow:hidden;box-shadow:0 10px 26px -18px rgba(44,40,32,0.2);">
        <summary style="display:flex;align-items:center;justify-content:space-between;gap:16px;padding:20px 24px;font-family:'Zen Kaku Gothic New',sans-serif;font-weight:600;font-size:clamp(14px,1.6vw,16px);color:#2c2820;">審査はありますか？<span class="lp-faq-plus">＋</span></summary>
        <div class="lp-faq-a"><div style="padding:0 24px 22px;font-size:14px;line-height:1.95;color:#6f685c;">簡単な審査があります。フォーム送信後、内容を確認のうえ承認いたします。</div></div>
      </details>
      <details style="background:#fffdf7;border:1px solid rgba(44,40,32,0.08);border-radius:14px;margin-bottom:12px;overflow:hidden;box-shadow:0 10px 26px -18px rgba(44,40,32,0.2);">
        <summary style="display:flex;align-items:center;justify-content:space-between;gap:16px;padding:20px 24px;font-family:'Zen Kaku Gothic New',sans-serif;font-weight:600;font-size:clamp(14px,1.6vw,16px);color:#2c2820;">報酬はいつもらえますか？<span class="lp-faq-plus">＋</span></summary>
        <div class="lp-faq-a"><div style="padding:0 24px 22px;font-size:14px;line-height:1.95;color:#6f685c;">月末締め、翌月10日に銀行口座へお振込みします（最低支払額{{ $minPayoutLabel }}円）。</div></div>
      </details>
      <details style="background:#fffdf7;border:1px solid rgba(44,40,32,0.08);border-radius:14px;overflow:hidden;box-shadow:0 10px 26px -18px rgba(44,40,32,0.2);">
        <summary style="display:flex;align-items:center;justify-content:space-between;gap:16px;padding:20px 24px;font-family:'Zen Kaku Gothic New',sans-serif;font-weight:600;font-size:clamp(14px,1.6vw,16px);color:#2c2820;">犬を飼っていなくても参加できますか？<span class="lp-faq-plus">＋</span></summary>
        <div class="lp-faq-a"><div style="padding:0 24px 22px;font-size:14px;line-height:1.95;color:#6f685c;">はい。人が食べる「馬肉特急」もあるので、犬を飼っていない方も紹介できます。</div></div>
      </details>
    </div>
  </section>

  <!-- ================= FINAL CTA ================= -->
  <section style="position:relative;overflow:hidden;background:#56653f;color:#f7f3e8;">
    <div style="position:absolute;top:-40%;left:50%;transform:translateX(-50%);width:70%;height:160%;background:radial-gradient(closest-side, rgba(193,120,76,0.2), transparent 70%);pointer-events:none;"></div>
    <div style="position:relative;max-width:780px;margin:0 auto;padding:clamp(64px,9vw,118px) clamp(20px,5vw,56px);text-align:center;">
      <div style="font-family:'Cormorant Garamond',serif;font-style:italic;font-size:clamp(18px,2vw,22px);letter-spacing:0.04em;color:#e6b566;margin-bottom:10px;">Start today</div>
      <h2 style="font-family:'Shippori Mincho',serif;font-weight:700;font-size:clamp(1.9rem,4vw,2.9rem);line-height:1.36;letter-spacing:0.02em;margin:0 0 30px;">今日から、紹介をはじめよう。</h2>
      <a href="{{ route('register.create') }}" class="lp-cta" style="display:inline-flex;align-items:center;gap:10px;background:#c1784c;color:#fff;font-weight:700;font-size:clamp(15px,1.6vw,17px);padding:18px 40px;border-radius:999px;text-decoration:none;box-shadow:0 16px 38px -10px rgba(193,120,76,0.65);">無料ではじめる <span style="font-size:18px;">→</span></a>
      <p style="font-size:13px;color:rgba(244,238,225,0.66);margin:22px 0 0;">ご不明な点は <a href="{{ route('inquiry.create') }}" style="color:#e6b566;text-decoration:underline;text-underline-offset:3px;">お問い合わせ</a> からどうぞ。</p>
    </div>
  </section>

  <!-- ================= FOOTER ================= -->
  <footer style="background:#3c4731;color:rgba(244,238,225,0.72);">
    <div style="max-width:1120px;margin:0 auto;padding:36px clamp(20px,5vw,56px);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;">
      <div style="display:flex;align-items:center;gap:11px;">
        <span style="flex:none;width:30px;height:30px;border-radius:50%;border:1px solid rgba(230,181,102,0.5);display:flex;align-items:center;justify-content:center;font-family:'Shippori Mincho',serif;font-weight:700;font-size:14px;color:#e6b566;">馬</span>
        <span style="font-family:'Shippori Mincho',serif;font-size:14px;color:#f4eee1;">馬肉特急 · 自然派いぬ生活 アンバサダー</span>
      </div>
      <div style="display:flex;gap:20px;font-size:12.5px;">
        <a href="#" style="color:rgba(244,238,225,0.7);text-decoration:none;">特定商取引法</a>
        <a href="#" style="color:rgba(244,238,225,0.7);text-decoration:none;">プライバシー</a>
        <a href="{{ route('inquiry.create') }}" style="color:rgba(244,238,225,0.7);text-decoration:none;">お問い合わせ</a>
      </div>
    </div>
    <div style="border-top:1px solid rgba(255,255,255,0.07);padding:16px clamp(20px,5vw,56px);text-align:center;font-size:11.5px;color:rgba(244,238,225,0.45);">© 2026 馬肉特急 / 自然派いぬ生活 Ambassador Program.</div>
  </footer>

</div>

</body>
</html>
