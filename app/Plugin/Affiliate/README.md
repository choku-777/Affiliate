# Affiliate プラグイン（EC-CUBE 4.2 用）

自社運用型のアフィリエイト（紹介報酬）機能を EC-CUBE 4.2 に追加するプラグインです。
外部ASPに依存せず、登録・成果計測・集計・支払い管理までを自社で完結できます。

## 機能概要

- **アフィリエイター登録（承認制）**: フロントの登録フォームから申請 → 管理者が承認するとURL発行。
- **成果計測**: 発行URL（`?affiliate=CODE`）でアクセスするとクッキー（既定30日）を付与。
  注文完了時にクッキーを参照して成果を記録（ラストクリック）。
- **報酬計算**: 全商品一律の料率（既定5%）× 注文合計（送料・税込）。端数は切り捨て。
- **成果の確定/取消**: 注文発生から猶予日数（既定30日）経過＆注文が正常なら確定。
  キャンセル・返品された注文は取消。日次バッチで処理。
- **支払い管理**: 管理画面で成果を集計表示。確定済みを手動で「支払済」に更新（実振込は手動・銀行振込）。

## ディレクトリ

```
app/Plugin/Affiliate/
├── Command/AffiliateConfirmCommand.php   成果確定/取消バッチ
├── Common/AffiliateNav.php               管理メニュー
├── Controller/Front, Controller/Admin    登録・承認・成果・設定
├── Entity/                               Affiliate, AffiliateReward, AffiliateClick, AffiliatePayment, AffiliateConfig
├── EventListener/                        クッキー付与・注文完了フック
├── Form/Type/                            登録・設定・検索フォーム
├── Repository/
├── Service/RewardCalculator.php          報酬計算ロジック（単体テスト対象）
└── Resource/                             テンプレート・設定
```

## インストール

```bash
# app/Plugin/Affiliate に配置した上で
bin/console eccube:plugin:install --code=Affiliate
bin/console eccube:plugin:enable --code=Affiliate
```

有効化時に初期設定（料率5% / クッキー30日 / 確定30日 / 最低支払額5000円）が登録されます。
設定は管理画面「アフィリエイト > 設定」から変更できます。

## 成果確定バッチ（cron）

```bash
bin/console affiliate:confirm-rewards
```

日次での実行を推奨します（例: 毎日 04:00）。

## アフィリエイトURL

承認後、管理画面の詳細画面に発行URLが表示されます。形式は次の通りです。

```
https://{ショップドメイン}/?affiliate={コード}
```

商品ページ等、任意のページのURLに `?affiliate={コード}` を付与しても計測されます。

## データベース（Xserver MySQL 等）について

本プラグインは Doctrine ORM を使用しており、EC-CUBE が接続する DB をそのまま利用します。
EC-CUBE 4.2 は MySQL 5.7/8.0・MariaDB 10.x に対応しているため、Xserver の MySQL でも動作します。
DB接続は EC-CUBE 本体の `.env`（`DATABASE_URL`）で設定してください。プラグイン側の追加設定は不要です。

## 管理画面の Google アカウント認証について

EC-CUBE 標準の管理画面ログインは ID/パスワードです。Google アカウントによる認証（SSO）は
本プラグインの対象外で、EC-CUBE 本体のセキュリティ設定（Symfony Security + OAuth）側で対応します。
別途、専用の対応を行う想定です。

## テスト

```bash
# EC-CUBE のルートで
vendor/bin/phpunit app/Plugin/Affiliate/Tests
```
