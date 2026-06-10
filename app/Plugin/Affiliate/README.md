# Affiliate プラグイン（EC-CUBE 4.2 / 4.3 用・postback専用の薄い計測層）

アフィリエイトの**計測だけ**を担当する軽量プラグインです。アフィリエイター登録・承認・
報酬集計・支払い管理などの管理機能は、別の独立アプリ（Laravel製「アフィリエイト管理サービス」）
側にあります。本プラグインは EC-CUBE 上の出来事を管理サービスへ **postback（HTTP POST）** します。

> 管理UI・ルーティング・独自DBテーブルを持たないため、`sensio/framework-extra-bundle` が
> 廃止された **EC-CUBE 4.3（Symfony 6.4）でも安全**に動作します。

## 役割

- **クッキー付与**: 任意ページの `?affiliate=CODE` を検知し、計測クッキー（既定30日）を付与。
- **成果送信**: 注文完了時にクッキーを参照し、`conversion`（注文番号・金額・日時・コード）を送信。
- **ステータス送信**: 管理画面で注文編集（キャンセル/返品等）時に `order_status` を送信。
- **取りこぼし防止**: 送信は「ファイルへ書き出し→成功で削除」方式。失敗分は `affiliate:resend` で再送。

報酬計算・重複排除・承認・確定/取消などの判断は、すべて管理サービス側で行います。

## 送信仕様

`POST {AFFILIATE_API_URL}/api/affiliate/event`
ヘッダ: `X-Api-Key: {AFFILIATE_API_KEY}` / `Content-Type: application/json`
ボディ:

```json
{
  "type": "conversion",
  "payload": {
    "affiliate_code": "xxxxxxxx",
    "order_no": "1000001",
    "order_total": "5400",
    "order_date": "2026-06-10T12:34:56+09:00"
  },
  "created_at": "2026-06-10T12:34:57+09:00"
}
```

`type` は `conversion` / `order_status` / `click` のいずれか。

## 設定（EC-CUBE の .env に追記）

```dotenv
# 管理サービスのベースURL
AFFILIATE_API_URL=https://affiliate.shizenha-inu.life
# 管理サービスと共有するAPIキー
AFFILIATE_API_KEY=（管理サービスで発行した値）
# クッキー有効期間（日）。未指定は30
AFFILIATE_COOKIE_DAYS=30
# クリックも送信する場合は true（既定 false）
AFFILIATE_TRACK_CLICKS=false
```

## インストール（EC-CUBE 4.3 / Xserver）

管理画面アップロード（SSH不要）か、SSH(CLI)で導入します。

```bash
php bin/console eccube:plugin:install --code=Affiliate
php bin/console eccube:plugin:enable --code=Affiliate
php bin/console cache:clear --no-warmup
```

cron（再送。数分おき推奨）:

```
*/5 * * * * cd /path/to/eccube && php bin/console affiliate:resend
```

## アフィリエイトURL

管理サービスで発行されたコードを、ショップの任意URLに付与して使います。

```
https://shizenha-inu.life/?affiliate={コード}
```
