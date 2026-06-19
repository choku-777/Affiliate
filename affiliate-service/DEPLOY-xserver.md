# Xserver デプロイ手順（アフィリエイト管理アプリ / Laravel 11）

対象ドメイン: **affiliate.tairiku-tsusho.co.jp**（`tairiku-tsusho.co.jp` のサブドメイン）
同一 Xserver 上で稼働。ショップ本体（EC-CUBE / shizenha-inu.life）とは独立。

> 表記ルール（自分の環境に置き換えて読む）
> - `{account}` … Xserver のサーバーID（例 `xs123456`）
> - `{server}` … 割当サーバー名（例 `sv12345`）。サーバーパネルの「アカウント」で確認
> - ホームディレクトリ … `/home/{account}/`
> - ドメインフォルダ … `/home/{account}/tairiku-tsusho.co.jp/`

---

## ディレクトリ構成（最終形）

セキュリティのため、**Laravel本体は公開領域(public_html)の外**に置き、`public/` の中身だけを公開フォルダに置きます。

```
/home/{account}/tairiku-tsusho.co.jp/
├── laravel/                      ← Laravel本体（非公開）app/ config/ vendor/ .env など
└── public_html/
    └── affiliate/                ← サブドメインの公開フォルダ（= public/ の中身を配置）
        ├── index.php  (パスを書き換え)
        └── .htaccess
```

---

## STEP 1. サブドメインを作成（サーバーパネル）

1. サーバーパネル → **サブドメイン設定** → `tairiku-tsusho.co.jp` を選択
2. サブドメイン `affiliate` を追加 → `affiliate.tairiku-tsusho.co.jp` が作られる
   - 公開フォルダ: `/home/{account}/tairiku-tsusho.co.jp/public_html/affiliate/`
3. 反映まで数分〜。続けて **SSL**（無料独自SSL / Let's Encrypt）をこのサブドメインで有効化
   - ※Google OAuth と Cookie のため HTTPS は必須

## STEP 2. MySQL データベースを作成（サーバーパネル）

1. **MySQL設定** → MySQL追加：DB名を作成（実名は `{account}_aff` のように接頭辞が付く。文字コードは **utf8mb4**）
2. **MySQLユーザ追加**：ユーザを作成（実名 `{account}_affu` 等）
3. **アクセス権所有ユーザの追加**：作成DBに作成ユーザを割り当て
4. 同ページの **MySQLホスト名** を控える（多くは `localhost`。表示値をそのまま使う）

> 控える4点：DB名 / ユーザ名 / パスワード / ホスト名

## STEP 3. SSH を有効化して接続

1. サーバーパネル → **SSH設定** → 状態を「ON」、公開鍵を登録（または鍵を生成してダウンロード）
2. 接続（ポートは **10022**）:
   ```bash
   ssh -p 10022 {account}@{server}.xserver.jp
   ```
3. PHP CLI が 8.2 以上か確認:
   ```bash
   php -v
   ```
   8.2未満なら Xserver のバージョン付き PHP を使う（例 `php8.3 -v`）。以降のコマンドの `php` を該当バージョンに読み替え。

## STEP 4. Laravel 本体を用意して、本リポジトリのソースを重ねる

このリポジトリの `affiliate-service/` は**アプリ固有ソースのみ**。素のLaravelに重ねて完成させます。

```bash
cd /home/{account}/tairiku-tsusho.co.jp

# Composer を用意（無ければphar取得）
php -r "copy('https://getcomposer.org/installer','composer-setup.php');"
php composer-setup.php && rm composer-setup.php   # → composer.phar が生成

# 素のLaravelを作成（フォルダ名 laravel）
php composer.phar create-project laravel/laravel laravel

# 本リポジトリを取得して affiliate-service/ を laravel/ に上書き
git clone https://github.com/choku-777/affiliate.git src_repo
cp -r src_repo/affiliate-service/. laravel/
rm -rf src_repo

cd laravel

# 追加パッケージ（Google認証）
php ../composer.phar require laravel/socialite
```

> `install:api` は不要です（本リポジトリの `bootstrap/app.php` が既に `routes/api.php` を読み込む構成のため）。

## STEP 5. .env を設定

```bash
cd /home/{account}/tairiku-tsusho.co.jp/laravel
cp .env.example .env   # 既にある場合は不足キーを追記
php artisan key:generate
nano .env
```

`.env` の要点（`.env.example` 準拠）:
```dotenv
APP_NAME="Affiliate"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://affiliate.tairiku-tsusho.co.jp

DB_CONNECTION=mysql
DB_HOST=localhost            # ← STEP2で控えたMySQLホスト名
DB_PORT=3306
DB_DATABASE={account}_aff    # ← 実DB名
DB_USERNAME={account}_affu   # ← 実ユーザ名
DB_PASSWORD=********

# EC-CUBEプラグインと同じ値にする
AFFILIATE_API_KEY=（openssl rand -hex 32 で生成した値）

# 管理画面ログイン（ロール別・カンマ区切り）
ADMIN_MANAGER_EMAILS=ec@tairiku-tsusho.co.jp
ADMIN_OPERATOR_EMAILS=（運用担当のGoogleアカウント）

# 発行URLに使うショップ本体（変更しない）
SHOP_BASE_URL=https://shizenha-inu.life

# Google OAuth
GOOGLE_CLIENT_ID=（Google Cloud Consoleで発行）
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=https://affiliate.tairiku-tsusho.co.jp/admin/auth/google/callback

# メール送信（承認通知）
MAIL_MAILER=smtp
MAIL_HOST=（Xserverのメール or 外部SMTP）
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS="no-reply@tairiku-tsusho.co.jp"
```

## STEP 6. マイグレーション（テーブル作成）

```bash
php artisan migrate --force
```

## STEP 7. 公開フォルダに public/ を配置し index.php を書き換え

```bash
cd /home/{account}/tairiku-tsusho.co.jp

# サブドメインの初期ファイルを退避・除去
rm -f public_html/affiliate/index.html public_html/affiliate/default_page.png 2>/dev/null

# public/ の中身（隠しファイル .htaccess 含む）を公開フォルダへコピー
cp -r laravel/public/. public_html/affiliate/
```

`public_html/affiliate/index.php` を編集し、本体への相対パスを `../../laravel/` に直す:
```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// 本体は ../../laravel/ にある
if (file_exists($maintenance = __DIR__.'/../../laravel/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../../laravel/vendor/autoload.php';

(require_once __DIR__.'/../../laravel/bootstrap/app.php')
    ->handleRequest(Request::capture());
```

> `.htaccess` は Laravel の `public/.htaccess` をそのまま使えばOK（書き換え不要）。

## STEP 8. パーミッションと最適化

```bash
cd /home/{account}/tairiku-tsusho.co.jp/laravel
chmod -R 775 storage bootstrap/cache

# 本番最適化（任意・推奨）
php artisan config:cache
php artisan route:cache
```

## STEP 9. 動作確認

- ブラウザで `https://affiliate.tairiku-tsusho.co.jp/register` → 登録フォームが出る
- `https://affiliate.tairiku-tsusho.co.jp/admin/login` → Googleログイン → 許可メールで入れる
- API疎通（任意・SSH等から）:
  ```bash
  curl -i -X POST https://affiliate.tairiku-tsusho.co.jp/api/affiliate/event \
    -H "Content-Type: application/json" -H "X-Api-Key: 設定したAFFILIATE_API_KEY" \
    -d '{"type":"conversion","payload":{"affiliate_code":"dummy","order_no":"TEST1","order_total":"1000"}}'
  # → 200。未承認コードなので {"status":"ignored",...} が返れば疎通OK
  ```

## STEP 10. cron（成果の確定/取消バッチ）

サーバーパネル → **Cron設定** で毎分実行を登録（Laravelスケジューラ）:
```
* * * * * cd /home/{account}/tairiku-tsusho.co.jp/laravel && php artisan schedule:run >> /dev/null 2>&1
```
（`routes/console.php` が 04:00 に `affiliate:confirm-rewards` を実行）

---

## STEP 11. EC-CUBE 側を本ドメインに向ける

EC-CUBE の `.env` に追記（プラグインが送信先を知るため）:
```dotenv
AFFILIATE_API_URL=https://affiliate.tairiku-tsusho.co.jp
AFFILIATE_API_KEY=（STEP5と同じ値）
```
反映:
```bash
php bin/console cache:clear --no-warmup
```

## STEP 12. Google OAuth クライアント

Google Cloud Console → 認証情報 → OAuthクライアント（ウェブ）:
- 承認済みリダイレクトURI: `https://affiliate.tairiku-tsusho.co.jp/admin/auth/google/callback`
- 発行した client_id / client_secret を STEP5 の `.env` に設定

---

## 更新デプロイ（2回目以降）＝ 手動アップロード方式

> ⚠️ **重要**：サーバーの `laravel/` は **git管理ではない**（STEP4で `cp` で重ねたため）。
> したがって「`git pull`」は使えない。**ローカルから変更ファイルを rsync で上げる**のが正しい。
> 現行の実値: account=`xs812447` / server=`sv16737` / 鍵=`~/.ssh/afferissh.key` / ポート=`10022`
> Laravel本体=`/home/{account}/tairiku-tsusho.co.jp/laravel/` / 公開=`.../public_html/affiliate/`

> ⚠️ **`rsync --delete` を公開フォルダに使わないこと。** 公開フォルダの `index.php` は STEP7 で
> `../../laravel/` を指すよう書き換えた**独自版**。`--delete` で同期すると上書き／削除され、500になる。

### 手順（すべてローカル端末から実行）

```bash
# 0) GitHubにも反映（任意・コード履歴用。本番はgit連動ではない）
git add -A && git commit -m "..." && git push origin <branch>

# 1) 本番の現行ファイルをバックアップ（戻せるように）
ssh -i ~/.ssh/afferissh.key -p 10022 xs812447@sv16737.xserver.jp \
  'cd ~/tairiku-tsusho.co.jp/laravel && for f in routes/web.php resources/views/landing.blade.php app/Http/Controllers/LandingController.php; do [ -f "$f" ] && cp -a "$f" "$f.bak-lp"; done'

# 2) 変更ファイルをアップロード（-R で階層維持・--delete は付けない）
cd affiliate-service
rsync -avzR -e "ssh -i ~/.ssh/afferissh.key -p 10022" \
  app/Http/Controllers/LandingController.php \
  resources/views/landing.blade.php \
  routes/web.php \
  public/images/landing/ \
  xs812447@sv16737.xserver.jp:/home/xs812447/tairiku-tsusho.co.jp/laravel/

# 3) サーバー側：キャッシュ更新 ＋ 画像を公開フォルダへコピー
ssh -i ~/.ssh/afferissh.key -p 10022 xs812447@sv16737.xserver.jp \
  'cd ~/tairiku-tsusho.co.jp/laravel && \
   ~/bin/php artisan view:clear && ~/bin/php artisan route:clear && ~/bin/php artisan config:clear && \
   mkdir -p ../public_html/affiliate/images/landing && \
   cp -f public/images/landing/*.jpg ../public_html/affiliate/images/landing/'
# ※ DB変更を含む場合のみ: ~/bin/php artisan migrate --force
# ※ 本番最適化を使う場合は最後に config:cache / route:cache（クロージャrouteが無いこと）

# 4) 確認
curl -s -o /dev/null -w "%{http_code}\n" https://affiliate.tairiku-tsusho.co.jp/
```

> メモ: サーバーの PHP CLI は `~/bin/php`（8.3）。画像はWeb用に最適化してから上げる
> （例: `sips -s format jpeg -s formatOptions 80 --resampleHeightWidthMax 1600 in.jpg --out out.jpg`）。

### ロールバック
```bash
ssh -i ~/.ssh/afferissh.key -p 10022 xs812447@sv16737.xserver.jp \
  'cd ~/tairiku-tsusho.co.jp/laravel && for f in routes/web.php resources/views/landing.blade.php app/Http/Controllers/LandingController.php; do [ -f "$f.bak-lp" ] && cp -a "$f.bak-lp" "$f"; done && ~/bin/php artisan view:clear'
```

### 将来 git pull 方式にしたい場合（任意）
`laravel/` を git 管理にするには、別途リポジトリ構成の見直しが必要
（本リポジトリは `affiliate-service/` がサブ階層のため、そのままでは `laravel/` 直下に pull できない）。

## トラブル時

- 500 が出る → 一時的に `.env` の `APP_DEBUG=true` にして詳細表示、または `laravel/storage/logs/laravel.log` を確認
- DB接続エラー → `DB_HOST`（Xserverのホスト名）/ DB名・ユーザ名の接頭辞を再確認
- Googleログインで弾かれる → リダイレクトURI完全一致 / `ADMIN_MANAGER_EMAILS` にメールが入っているか
- 画面は出るがCSS無し → これはBootstrap CDN利用なので通常問題なし（社内ネットワーク制限時のみ要確認）
