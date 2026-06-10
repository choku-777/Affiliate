# アフィリエイト管理サービス（Laravel）

EC-CUBE とは独立した、アフィリエイト管理用の Web アプリです。
`affiliate.shizenha-inu.life`（Xserver サブドメイン）での稼働を想定しています。

- **アフィリエイター登録**（公開）と **本人マイページ**（トークンURL）
- **管理画面**（Googleアカウント認証・許可メールのホワイトリスト）
  - 承認 / 成果集計 / 支払い管理 / 設定
- **API**：EC-CUBE プラグインからの postback 受信（成果・注文ステータス・クリック）
- **バッチ**：成果の確定・取消（日次）

EC-CUBE 側は別リポジトリの `app/Plugin/Affiliate`（postback専用の薄い計測層）が担当します。

## このディレクトリの位置づけ

ここには **アプリ固有のソースのみ** を収めています。実際に動かすには、まず素の Laravel を
作成し、その上にこれらのファイルを重ねます（標準パス準拠なのでそのまま上書きできます）。

## セットアップ

```bash
# 1. 素のLaravelを作成（PHP 8.3 / Laravel 11）
composer create-project laravel/laravel affiliate-service
cd affiliate-service

# 2. 本ディレクトリの内容を上書きコピー（app/ config/ routes/ database/ resources/ bootstrap/ .env.example）

# 3. API ルートを有効化（routes/api.php を使うため）
php artisan install:api   # 既に api.php を同梱しているので、質問にはNoでも可

# 4. Socialite を導入
composer require laravel/socialite

# 5. .env を設定（.env.example を参照。DB/APIキー/許可メール/Google/メール）
cp .env.example .env   # 既存 .env がある場合は必要キーを追記
php artisan key:generate

# 6. マイグレーション
php artisan migrate

# 7. 動作確認
php artisan serve
```

> `install:api` を実行すると Laravel が `routes/api.php` を生成しようとします。本リポジトリの
> `api.php`・`bootstrap/app.php` を優先してください（上書きコピーで対応）。

## Google OAuth の準備

1. Google Cloud Console で OAuth クライアント（ウェブ）を作成
2. 承認済みリダイレクトURIに
   `https://affiliate.shizenha-inu.life/admin/auth/google/callback` を登録
3. クライアントID/シークレットを `.env`（`GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET`）に設定
4. `ADMIN_ALLOWED_EMAILS` にログインを許可するメールを列挙（カンマ区切り）

## EC-CUBE プラグインとの連携

- `AFFILIATE_API_KEY` を **EC-CUBE 側（.env）と本アプリ（.env）で同じ値**にする
- EC-CUBE 側 `.env`：
  ```
  AFFILIATE_API_URL=https://affiliate.shizenha-inu.life
  AFFILIATE_API_KEY=（同じ値）
  ```
- 受信エンドポイント：`POST /api/affiliate/event`（ヘッダ `X-Api-Key`）

## Xserver へのデプロイ（概要）

1. サブドメイン `affiliate.shizenha-inu.life` を作成
2. ドキュメントルートを **`public/`** に向ける（Laravel標準）
3. PHP 8.3 を選択、`composer install --no-dev`、`.env` 設定、`php artisan migrate`
4. cron に確定バッチを登録：
   ```
   * * * * * cd /path/to/affiliate-service && php artisan schedule:run >> /dev/null 2>&1
   ```
   （`routes/console.php` で 04:00 に `affiliate:confirm-rewards` を実行）

## 主要なディレクトリ

```
app/Http/Controllers/Api/EventController.php   postback受信（conversion/order_status/click）
app/Http/Controllers/RegistrationController.php 登録（公開）
app/Http/Controllers/MyPageController.php       本人マイページ
app/Http/Controllers/Admin/*                    認証・ダッシュボード・承認・成果・設定
app/Http/Middleware/VerifyApiKey.php            APIキー認証
app/Http/Middleware/EnsureAdmin.php             管理画面の認可
app/Models/*                                    Affiliate / Reward / Setting / Click
app/Services/RewardCalculator.php               報酬計算
app/Console/Commands/ConfirmRewards.php         確定/取消バッチ
database/migrations/*                           テーブル定義
resources/views/*                               画面
```
