# アフィリエイト計測・管理システム — プロジェクト引き継ぎ書

> このドキュメントは、本プロジェクトを**初見のAI／開発者がゼロから把握**できるようにまとめた
> 包括的な引き継ぎ書です。背景・設計・現状・残作業・環境情報・既知の落とし穴まで網羅しています。

最終更新: 2026-06-11
リポジトリ: `choku-777/affiliate`（GitHub）
作業ブランチ: **`claude/happy-cannon-up94x8`**

---

## 1. これは何か（目的）

EC-CUBE で運営するペット用品ショップ **自然派ぬ生活（https://shizenha-inu.life）** に、
**アフィリエイト（紹介報酬）機能**を導入するプロジェクト。

- 紹介者（アフィリエイター）が自分専用URLで集客 → 購入が発生したら報酬を付与
- アフィリエイターの登録・承認・成果集計・報酬の確定/支払い・各種設定を管理する

## 2. 全体アーキテクチャ（最重要）

**「計測」と「管理」を2つのアプリに分離**している。これが本プロジェクトの根幹方針。

```
┌─────────────────────────────────────┐        ┌──────────────────────────────────────────┐
│ ① EC-CUBE プラグイン（計測専用の薄い層）  │        │ ② Laravel 管理アプリ（管理機能ぜんぶ）        │
│   サイト: shizenha-inu.life            │        │   サイト: affiliate.tairiku-tsusho.co.jp    │
│   パス: app/Plugin/Affiliate           │        │   パス: affiliate-service/                  │
│                                       │        │                                            │
│ ・?affiliate=CODE でクッキー付与         │ HTTP   │ ・アフィリエイター登録／承認                   │
│ ・注文完了→成果データをPOST  ───────────┼──────▶│ ・成果の集計・確定・取消・支払い管理            │
│ ・注文ステータス変更→POST               │ POST   │ ・料率などの設定                             │
│ ・独自DBを持たない／管理UIも持たない       │        │ ・管理画面（Googleログイン・2ロール）          │
│                                       │        │ ・専用MySQL DBにデータ保存                    │
└─────────────────────────────────────┘        └──────────────────────────────────────────┘
        EC-CUBE 4.3.0 / PHP 8.3                          Laravel 11 / PHP 8.3 / MariaDB 10.5
        （両方とも同一 Xserver 上で稼働）
```

**分離した理由**: EC-CUBE のバージョンアップやデータ構造に管理機能が密結合すると壊れやすい。
計測（EC-CUBE側）だけを最小限のプラグインにし、管理は独立Laravelアプリに寄せて疎結合にした。

## 3. 確定済みの設計判断（ユーザーと合意済み）

| 項目 | 決定 |
|---|---|
| 管理アプリの技術 | **Laravel 11**（PHP 8.2+） |
| ホスティング | 同一 Xserver の**サブドメイン** `affiliate.tairiku-tsusho.co.jp` |
| 管理アプリのDB | Xserver MySQL（**EC-CUBEとは別DB**） |
| 管理画面の認証 | **Google OAuth**（Laravel Socialite）＋**許可メールのホワイトリスト** |
| 管理画面のロール | **2ロール**。`manager`=全権 / `operator`=設定変更以外（承認・成果・支払い操作は可）。ログインしたGoogleアカウントのメールでロール判定 |
| アフィリエイターとEC-CUBE会員 | **別管理**（メール突合なし）。成果紐付けはコード（クッキー）→注文番号のみ |
| アフィリエイター登録項目 | 姓名・フリガナ・電話・生年月日・性別・住所（郵便番号→住所自動入力 zipcloud API）・**ログイン用メール＆パスワード**・振込先 |
| アフィリエイター本人マイページ | **メール＋パスワードのログイン方式**（`/affiliate/login` → `/affiliate/mypage`）。承認済みのみログイン可。※当初トークンURL案だったがログイン方式に変更済み |
| 報酬計算 | 料率方式（既定5%・設定可）。`floor(注文金額 × 料率 / 100)` |
| 成果の確定 | 発生から N 日（既定30日）経過で自動確定（日次バッチ） |
| キャンセル/返品 | EC-CUBE管理の注文編集時にプラグインがステータスをPOST → Laravel側で該当成果を取消 |
| 報酬の支払い | アフィリエイター単位の**一括支払い(Payout)**。確定済み(confirmed)合計が最低支払額以上の人をまとめて`paid`化し、支払い履歴(payouts)を残す |

## 4. データの流れ（エンドツーエンド）

1. 訪問者が `https://shizenha-inu.life/?affiliate=CODE` にアクセス
   → プラグインが `affiliate_code` クッキーを付与（既定30日・httponly・SameSite=Lax）
2. その訪問者が商品を購入し注文完了
   → プラグインがクッキーを読み、成果(`conversion`)を管理アプリへPOST
3. 管理アプリ: コードで**承認済みアフィリエイター**を検索 → `order_no` で重複排除 → 報酬額算出 → `status=pending` で記録
4. EC-CUBE管理で注文ステータス変更（キャンセル=3／返品=9 等）
   → プラグインが `order_status` をPOST → 管理アプリが該当成果を `cancelled` に
5. 日次バッチ `affiliate:confirm-rewards`: `pending` かつ発生から確定日数経過かつ未キャンセル → `confirmed`
6. 管理者が「支払い」画面でアフィリエイター単位に**一括支払い** → 対象の確定報酬を `paid` 化し `payouts` 履歴を作成
7. アフィリエイター登録は管理アプリの公開フォームから（メール＋パスワードも登録）。管理者がGoogleログインで承認すると、
   承認通知メールで「紹介用URL」と「ログインURL」が送られる。本人はメール＋パスワードで `/affiliate/login` からマイページにログイン

## 5. コンポーネント① EC-CUBE プラグイン（`app/Plugin/Affiliate`）

**役割**: 計測（postback）専用。管理UI・ルーティング・独自Entity・独自テーブルを持たない
（→ `sensio/framework-extra-bundle` が廃止された EC-CUBE 4.3=Symfony 6.4 でも安全）。

### ファイル構成
```
app/Plugin/Affiliate/
├── composer.json                         # type: eccube-plugin, extra.code: Affiliate, require: {}
├── PluginManager.php                     # enable()でvar/affiliate_outbox作成
├── Resource/config/services.yaml         # autowire/autoconfigure + projectDirバインドのみ
├── Service/
│   ├── Config.php                        # .envを$_SERVER/$_ENV/getenvで直読み
│   └── PostbackClient.php                # cURLでPOST＋ファイルoutbox＋再送
├── EventListener/
│   ├── AffiliateCookieListener.php       # KernelEvents::RESPONSE → ?affiliate=CODEでクッキー付与
│   ├── ShoppingCompleteListener.php      # FRONT_SHOPPING_COMPLETE_INITIALIZE → conversion送信
│   └── AdminOrderStatusListener.php      # ADMIN_ORDER_EDIT_INDEX_COMPLETE → order_status送信
├── Command/
│   └── AffiliateResendCommand.php        # affiliate:resend（outbox再送、#[AsCommand]）
└── README.md
```

### 設定（EC-CUBE の `.env` に追記して使う）
```dotenv
AFFILIATE_API_URL=https://affiliate.tairiku-tsusho.co.jp   # 送信先（管理アプリ）
AFFILIATE_API_KEY=（管理アプリと同じ共有キー）
AFFILIATE_COOKIE_DAYS=30                                   # 任意（既定30）
AFFILIATE_TRACK_CLICKS=false                               # 任意（クリックも送るか）
```

### 信頼性設計
- 送信は「先に `var/affiliate_outbox/*.json` へ書き出し → 送信成功で削除」。失敗分は残る
- `php bin/console affiliate:resend` で再送（cronで数分おき推奨）
- 送信先 `{AFFILIATE_API_URL}/api/affiliate/event`、ヘッダ `X-Api-Key`、JSONボディ `{type, payload, created_at}`
  - `type` は `conversion` / `order_status` / `click`

### 状態
**実機（EC-CUBE 4.3.0）にインストール済み・有効化済み・動作確認済み**（下記の3バグ修正後）。

### インストール時に発生し修正した3つの不具合（重要な教訓）
1. **型不一致のFatal error**: `PluginManager` が `Symfony\Component\DependencyInjection\ContainerInterface`
   をimportしていた。4.2/4.3の `AbstractPluginManager::enable()` は `Psr\Container\ContainerInterface`
   を使うため非互換。→ import を `Psr\Container\ContainerInterface` に変更。
2. **`getParameter()` 未定義で500**: `enable()` に渡る `$container` はフルコンテナでなく **ServiceLocator**
   で `get()/has()` しか持たない。→ `dirname(__DIR__, 3)` でプロジェクトルートを算出するよう変更。
3. **アップロード時「ディレクトリ作成に失敗」**: `app/Plugin/Affiliate` の残骸が既存だと新規mkdirが失敗。
   → 再アップロード前に残骸を削除（コード修正不要・運用）。配布は **zip（composer.jsonをトップ階層、
   `__MACOSX`/`.DS_Store`除外）** が安全。tar.gzはGNU形式だとPharData展開で `upload_failure` になることがある。
- 付随: 有効化中にEC-CUBEが `.maintenance` を作り、残ると503になることがある → 削除で解除（プラグインの問題ではない）。

## 6. コンポーネント② Laravel 管理アプリ（`affiliate-service/`）

**重要**: このディレクトリには**アプリ固有のソースのみ**が入っている。素のLaravel 11に**重ねて**完成させる。

### ファイル構成
```
affiliate-service/
├── app/
│   ├── Models/                Affiliate, Reward, Setting, Click, Payout
│   ├── Services/RewardCalculator.php           # floor(total*rate/100)
│   ├── Console/Commands/ConfirmRewards.php      # affiliate:confirm-rewards（確定/取消）
│   ├── Mail/AffiliateApproved.php               # 承認通知メール（紹介URL＋ログインURL）
│   └── Http/
│       ├── Middleware/VerifyApiKey.php          # X-Api-Key検証
│       ├── Middleware/EnsureAdmin.php           # 管理画面ログイン必須
│       ├── Middleware/EnsureManager.php         # managerロール限定（設定）
│       ├── Middleware/EnsureAffiliateLoggedIn.php # アフィリエイター本人ログイン必須（alias: affiliate）
│       └── Controllers/
│           ├── Api/EventController.php           # POST /api/affiliate/event 受信
│           ├── RegistrationController.php        # 公開：登録（拡張項目＋パスワード）
│           ├── AffiliateAuthController.php       # 公開：本人ログイン/ログアウト（メール＋パスワード）
│           ├── MyPageController.php              # 本人：マイページ（session affiliate_id で特定）
│           └── Admin/AuthController.php, DashboardController.php, AffiliateController.php,
│                     RewardController.php, PayoutController.php, SettingController.php
├── config/affiliate.php       # api_key, roles(email=>role), admin_emails, shop_url
├── config/services.php        # google（Socialite）ブロック追加済み
├── bootstrap/app.php          # ミドルウェアalias(admin/manager/apikey)・ルーティング
├── routes/web.php, api.php, console.php
├── database/migrations/       # settings, affiliates, rewards, clicks, +profile/auth拡張, payouts
├── resources/views/           # register, mypage, admin/*, emails（Bootstrap 5 CDN）
├── .env.example
├── README.md                  # 構築手順の概要
└── DEPLOY-xserver.md          # ★Xserver向けの詳細デプロイ手順（これに従う）
```

### データモデル
- **settings**（単一行 id=1）: `commission_rate`, `confirm_after_days`, `min_payout_amount`, `cookie_lifetime_days`
- **affiliates**: `name`(姓名連結・互換用), `last_name`/`first_name`/`last_name_kana`/`first_name_kana`,
  `email`(unique・ログインID), `password`(hashedキャスト), `phone`, `birth_date`, `gender`,
  住所(`postal_code`/`prefecture`/`city`/`address1`/`address2`), `affiliate_code`(unique),
  `mypage_token`(unique・現状はログイン方式のため未使用), 銀行情報,
  `status`(pending/approved/rejected/suspended), `commission_rate`(nullable=個別料率上書き), `approved_at`
- **rewards**: `affiliate_id`, `order_no`(unique), `order_total`, `rate_applied`, `reward_amount`,
  `status`(pending/confirmed/cancelled/paid), `order_status_id`, `converted_at`/`confirmed_at`/`paid_at`, `payout_id`(nullable)
- **payouts**: `affiliate_id`, `amount`(支払合計), `reward_count`(まとめた件数), `paid_at`。1回の一括支払い＝1レコード
- **clicks**: `affiliate_id`(nullable), `affiliate_code`, `ip`, `referer`, `landing_url`, `clicked_at`

### ルート概要
- 公開: `GET /register`, `POST /register`, `GET /register/thanks`
- アフィリエイター本人: `GET|POST /affiliate/login`, （middleware `affiliate`）`GET /affiliate/mypage`, `POST /affiliate/logout`
- API: `POST /api/affiliate/event`（middleware `apikey`）
- 管理: `GET /admin/login`, Google認証(`/admin/auth/google/redirect|callback`), `POST /admin/logout`
  - 認可領域(middleware `admin`): `/admin`（ダッシュボード）, `/admin/affiliates`（一覧/詳細/approve/reject/suspend）,
    `/admin/rewards`（一覧）, `/admin/payouts`（一括支払い一覧／`POST /admin/payouts/{affiliate}` で支払い実行）,
    **`/admin/settings`（middleware `manager` でmanager限定）**

### `.env` の要点
```dotenv
APP_URL=https://affiliate.tairiku-tsusho.co.jp
DB_CONNECTION=mysql / DB_HOST=（Xserverのホスト名）/ DB_DATABASE / DB_USERNAME / DB_PASSWORD
AFFILIATE_API_KEY=（EC-CUBE側と同じ値にする）
ADMIN_MANAGER_EMAILS=ec@tairiku-tsusho.co.jp        # 全権
ADMIN_OPERATOR_EMAILS=（運用担当のGoogleアカウント）  # 設定以外
SHOP_BASE_URL=https://shizenha-inu.life             # 発行URL生成用（変更しない）
GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET / GOOGLE_REDIRECT_URI=.../admin/auth/google/callback
MAIL_*（承認通知メール用）
```

### 状態
**ソースは完成・リポジトリにpush済み。まだデプロイ（起動）していない。** ← 残作業の主対象。

## 7. 稼働環境（Xserver・確定情報）

| 項目 | 値 |
|---|---|
| サーバー番号 | `sv16737`（ホスト名 `sv16737.xserver.jp` / IP `85.131.213.118`） |
| サーバーID（SSHユーザー） | `xs812447`（ホーム `/home/xs812447`） |
| SSH接続 | `ssh -i ~/.ssh/afferissh.key -p 10022 xs812447@sv16737.xserver.jp`（ポート10022） |
| PHP CLI | 既定は8.0.30だが、**`~/bin/php` → `/usr/bin/php8.3`（8.3.30）に設定済み**。`~/.bash_profile`で`PATH`に`~/bin`を前置済み |
| 利用可能PHP | 8.5/8.4/8.3/8.2/8.1/8.0（`/usr/bin/php8.3` 等） |
| DB | MariaDB 10.5.x |
| Composer | `~/composer.phar` に取得予定（`php ~/composer.phar`） |
| EC-CUBE | 4.3.0（同一サーバー・管理ルートは `kanri`）。プラグイン導入済み |
| 管理アプリのサブドメイン | `affiliate.tairiku-tsusho.co.jp`（**作成済み**） |
| 管理アプリの公開フォルダ | `/home/xs812447/tairiku-tsusho.co.jp/public_html/affiliate/` |
| 管理アプリのMySQL | **作成済み**（DB名/ユーザ/パス/ホスト名は `.env` に設定する。ホスト名は多くは `localhost`） |

### デプロイ時のディレクトリ構成（目標）
```
/home/xs812447/tairiku-tsusho.co.jp/
├── laravel/                         ← Laravel本体（非公開）
└── public_html/affiliate/           ← public/ の中身を配置し index.php のパスを ../../laravel/ に書換
```

## 8. 残作業（次にやること）

**管理アプリ② を Xserver にデプロイする**（手順は `affiliate-service/DEPLOY-xserver.md` のSTEP4以降）。
要点:
1. `~/composer.phar` 取得 → `php ~/composer.phar create-project laravel/laravel laravel`
2. 本リポジトリの `affiliate-service/.` を `laravel/` に上書きコピー
3. `php ~/composer.phar require laravel/socialite`
4. `.env` 設定（DB/APIキー/許可メール/Google/メール）→ `php artisan key:generate`
5. `php artisan migrate --force`
6. `laravel/public/.` を `public_html/affiliate/` にコピー → `index.php` のパスを `../../laravel/` に書換
7. `chmod -R 775 storage bootstrap/cache`、（任意）`config:cache`/`route:cache`
8. cron登録: `* * * * * cd .../laravel && php artisan schedule:run >/dev/null 2>&1`
9. **EC-CUBE側 `.env`** に `AFFILIATE_API_URL` / `AFFILIATE_API_KEY` を設定し `cache:clear`
10. **Google Cloud Console** でOAuthクライアント作成（リダイレクトURI=`https://affiliate.tairiku-tsusho.co.jp/admin/auth/google/callback`）
11. 動作確認（/register, /admin/login, APIへのcurl疎通）

> 注意: `install:api` は不要（本リポの `bootstrap/app.php` が `routes/api.php` を読み込む構成のため）。
> 注意: サブドメインの**無料独自SSL**を有効化しておくこと（Google OAuthとセキュアCookieに必須）。

## 9. 整合性で必ず守ること

- **`AFFILIATE_API_KEY` は EC-CUBE側 `.env` と Laravel側 `.env` で同一値**にする
- `SHOP_BASE_URL=https://shizenha-inu.life`（発行URL `https://shizenha-inu.life/?affiliate=コード` を生成）
- API エンドポイントは `{AFFILIATE_API_URL}/api/affiliate/event`
- EC-CUBE の OrderStatus: キャンセル=3 / 返品=9（取消判定に使用）

## 10. リポジトリ／作業ルール

- GitHub: `choku-777/affiliate`、作業ブランチ **`claude/happy-cannon-up94x8`**
- 変更はこのブランチにコミット＆push
- ※ PR は明示依頼があるまで作らない運用

## 11. 主要コミット履歴（参考）

- アフィリエイトプラグイン初版 → postback専用の薄い層に作り替え（4.3対応）
- 独立Laravel管理アプリ追加 → 2ロール(管理/運用)追加
- services.yamlのenvプロセッサ撤廃（Configサービス化で堅牢化）
- PluginManagerの2バグ修正（Psr ContainerInterface / dirname算出）
- ドメインを affiliate.tairiku-tsusho.co.jp に変更
- Xserverデプロイ手順書(DEPLOY-xserver.md)追加

---

### このドキュメントの使い方（引き継ぎ先AIへ）

1. まず本書で全体像を把握する
2. `affiliate-service/DEPLOY-xserver.md` に従い、Xserverへ管理アプリをデプロイする
3. SSHは利用者のローカル端末から行う（鍵 `~/.ssh/afferissh.key`）。クラウド実行環境からはサーバーに到達できない
4. コードの修正が必要なら、ブランチ `claude/happy-cannon-up94x8` で編集→push
