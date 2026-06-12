# 馬肉特急 アフィリエイト計測 導入手順書

馬肉特急（https://www.829109.jp/ ）のEC-CUBEに、アフィリエイト計測プラグインを導入する手順です。
自然派いぬ生活と**同じ管理システム**（https://affiliate.tairiku-tsusho.co.jp ）に成果を集約し、
管理画面・アフィリエイター登録・支払いはすべて兼用します。サイトの区別は「サイトコード = `baniku`」で行います。

> **用語メモ**
> - **プラグイン**：EC-CUBEに機能を追加する部品。今回は「どのアフィリエイター経由で売れたか」を記録して管理システムへ送る計測部品です。
> - **.env（ドットエンブ）**：サーバーごとの設定を書くファイル。ここの`AFFILIATE_SITE_CODE=baniku`が「このサイトは馬肉特急」という目印になります。
> - **サイトコード**：サイトを区別する短い識別子。自然派いぬ生活=`shizenha-inu`／馬肉特急=`baniku`。

---

## 0. 前提の確認

- 馬肉特急のEC-CUBEが **4.x系**であること（自然派と同じ4.3系なら、プラグインはそのまま動きます）
  - 確認方法：EC-CUBE管理画面の最下部、または `composer.json` 内の `ec-cube/ec-cube` のバージョン表記
- **PHP 8.2.31**（馬肉特急のシステム情報で確認済み）。プラグインはPHP 8.0相当の書き方しか使っておらず（8.3固有の構文なし）、`composer.json`にもPHPバージョン制約がないため、**無変更で8.2でも問題なく動作します**
  - **コマンドの注意**：この手順書のコマンドは `php8.2` と表記しています（＝PHP 8.2系を呼ぶコマンドの意味）。馬肉特急のサーバー（さくら）では、まず `php -v` を実行して **8.2.x** と表示されるコマンドを使ってください（多くの場合 `php` で大丈夫です）
- 管理システム（affiliate.tairiku-tsusho.co.jp）側の準備は**完了済み**です
  - `sites`テーブルに馬肉特急（コード=`baniku`、料率10%）を登録済み。あとは馬肉特急サーバー側の作業だけです。

---

## 1. プラグイン本体を配置する

プラグインの中身は、自然派いぬ生活とまったく同一です。入手方法は以下のいずれか。

### 方法A：自然派サーバーからコピー（推奨・確実）
自然派サーバーの以下のフォルダを丸ごとコピーして、馬肉特急のEC-CUBEの同じ場所に置きます。

```
コピー元（自然派）: ~/shizenha-inu.life/public_html/app/Plugin/Affiliate/
配置先（馬肉特急）: （馬肉特急のEC-CUBEルート）/app/Plugin/Affiliate/
```

### 方法B：GitHubリポジトリから取得
リポジトリ `choku-777/Affiliate` の `app/Plugin/Affiliate/` 一式が最新版です。
これを馬肉特急のEC-CUBEの `app/Plugin/Affiliate/` に配置します。

> どちらの方法でも中身は同じです。`app/Plugin/Affiliate/` の下に
> `PluginManager.php` / `Service/` / `EventListener/` / `Resource/` などが入っていればOK。

---

## 2. .env にプラグイン設定を追記する

馬肉特急のEC-CUBEルートにある `.env` ファイルの末尾に、以下5行を追記します。
**`AFFILIATE_SITE_CODE=baniku` が最重要**（これが無いと自然派として記録されてしまいます）。

```dotenv
AFFILIATE_API_URL=https://affiliate.tairiku-tsusho.co.jp
AFFILIATE_API_KEY=（自然派の .env と同じ値をコピー）
AFFILIATE_COOKIE_DAYS=30
AFFILIATE_TRACK_CLICKS=true
AFFILIATE_SITE_CODE=baniku
```

- **AFFILIATE_API_KEY**：自然派サーバーの `~/shizenha-inu.life/public_html/.env` にある
  `AFFILIATE_API_KEY=...` の値を**そのままコピー**してください（2サイト共通のキーです）。
  ※ セキュリティのため、この手順書には実値を載せていません。
- **AFFILIATE_COOKIE_DAYS=30**：紹介リンクをクリックしてから30日以内の購入を成果とする、の意味。
- **AFFILIATE_TRACK_CLICKS=true**：クリック数も記録する設定。

> 馬肉特急のEC-CUBEが本番モード（`APP_ENV=prod`）で `.env.local.php` というファイルを使っている場合は、
> 追記後に `php8.2 composer dump-env prod` を実行して設定を反映してください。
> （自然派サーバーには `.env.local.php` は無く、`.env` を直接読む構成でした。同じ構成なら不要です）

---

## 3. プラグインを有効化する

EC-CUBEルートで、以下のコマンドを順に実行します（`php8.2` は前述のとおりPHP 8.2系を呼ぶコマンド＝多くは `php`）。

```bash
# プラグインをEC-CUBEに登録
php8.2 bin/console eccube:plugin:install --code=Affiliate

# 有効化
php8.2 bin/console eccube:plugin:enable --code=Affiliate
```

> 自然派サーバーではこの方法で有効化しています。もし `eccube:plugin:install` でエラーが出る場合は、
> 自然派の導入時と同じ対応が必要になることがあります（不明点はご連絡ください）。

---

## 4. キャッシュを再生成する

設定とプラグインを反映するため、キャッシュを作り直します。

```bash
php8.2 bin/console cache:clear
```

`[OK] Cache for the "prod" environment ... was successfully cleared.` と出れば成功です。
（`PHP Deprecated: ... Serializable ...` という警告はEC-CUBE標準のもので、無視してOKです）

---

## 5. 管理画面で馬肉特急の料率を確認・設定する

1. 管理システム（https://affiliate.tairiku-tsusho.co.jp ）に管理者でログイン
2. 「設定」画面の **「サイト別の報酬料率（%）」** に「馬肉特急」が表示されます
3. 初期値は **10%**。変更したい場合はここで馬肉特急の料率だけ変更して保存
   - 確定日数・最低支払額・Cookie期間は全サイト共通です（馬肉特急だけ変えることはしません）

---

## 6. 動作確認（E2E）

1. アフィリエイター（承認済み）のマイページを開くと、紹介用URLが**サイトごとに**表示されます
   - 馬肉特急の紹介URL例：`https://www.829109.jp/?affiliate=（アフィリエイトコード）`
2. その馬肉特急の紹介URLにアクセス → 馬肉特急で何か購入
3. 管理システムの **「報酬一覧」** を開き、**「サイト」列が「馬肉特急」**になっている成果が記録されていればOK
   - 「サイト」フィルタで「馬肉特急」に絞り込んで確認できます

---

## まとめ（チェックリスト）

- [ ] EC-CUBE 4.3.0・PHP 8.2.31 を確認（`php -v` で8.2系のコマンドを確認）
- [ ] `app/Plugin/Affiliate/` を配置
- [ ] `.env` に5行追記（特に `AFFILIATE_SITE_CODE=baniku`）
- [ ] `eccube:plugin:install` → `eccube:plugin:enable`
- [ ] `cache:clear`
- [ ] 管理画面で馬肉特急の料率を確認
- [ ] 紹介URL→購入→「報酬一覧」でサイト=馬肉特急を確認

不明点や、`eccube:plugin:install` でエラーが出た場合はご連絡ください。
