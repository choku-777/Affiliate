# 自社アフィリエイトシステム

Laravel製の管理アプリ ＋ EC-CUBE計測プラグインの2本立て。**本番稼働中**。

- 公開URL: https://affiliate.tairiku-tsusho.co.jp
- GitHub: `choku-777/Affiliate`（作業ブランチ `claude/happy-cannon-up94x8`）
- 管理アプリ: `affiliate-service/`（Laravel）— 素のLaravelに重ねる構成（`composer create-project laravel/laravel` 後に rsync で上書き）
- EC-CUBEプラグイン: `app/Plugin/Affiliate`（計測専用・postback。独自テーブル無し）
- 手順書: `affiliate-service/DEPLOY-xserver.md` / `HANDOFF.md` / `SETUP-baniku.md`

## 必ず守ること

### 本番DBは実データ。全件操作を絶対にしない
- 実際のアフィリエイター登録があり、報酬・支払い履歴が入っている
- テストデータを消す場合は**メールアドレス等で対象を特定して限定削除**する。`DELETE FROM ... ;`（無条件）は禁止
- スキーマ変更・マイグレーションは事前に確認とバックアップを取る

### PHPは php8.3 を明示指定する
- Xserver のデフォルト `php` は 8.0 で Laravel が動かない
- cron は `/usr/bin/php8.3` のフルパス。Composer は `~/tairiku-tsusho.co.jp/composer.phar`

### 秘密情報をコミットしない
- `id_ecdsa.pem`（さくら用SSH秘密鍵・パスフレーズ付き）がリポジトリ直下にある。`.gitignore` の `*.pem` で保護されているが、**除外設定を緩めない**
- `affiliate-service/.env` も .gitignore 済み。rsync は必ず `--exclude=.env`
- 鍵のパスフレーズ・DBパスワード・APIキーをコード、ドキュメント、ログに書かない

### ローカルEC-CUBEの送信先を本番に向けない
- プラグインの `AFFILIATE_API_URL` が本番Laravelを指していると、**ローカルのテスト注文が本番成果に混入する**

## デプロイ（Xserver）
- SSH: `ssh -i ~/.ssh/afferissh.key -p 10022 xs812447@sv16737.xserver.jp`
- 本体 `~/tairiku-tsusho.co.jp/laravel/`、公開 `~/tairiku-tsusho.co.jp/public_html/affiliate/`（index.php は `../../laravel/` を参照）
- DB: MariaDB、`xs812447_afferi`@localhost（パスワードに記号を含むため .env はダブルクオート）
- 更新手順: ローカル編集 → rsync（`--exclude=.env`）→ `php8.3 artisan migrate --force` → `config/route/view:cache` 再生成
- ローカルMacに php/composer が無いため、動作確認は本番で行う（＝変更は慎重に）

## マルチサイト
| site_code | サイト | サーバ | 料率 |
|---|---|---|---|
| `umashippo` | うましっぽ | Xserver | 20% |
| `baniku` | 馬肉特急 | さくら | 10% |

- EC-CUBE側 `.env` の `AFFILIATE_SITE_CODE` と管理側 `sites.code` を一致させる。変更時は**DBと.envを同時に**
- 馬肉特急のプラグインは1つ前の版のまま（防御強化版 commit 476a284 が未反映）。揃える場合はさくらの鍵パスフレーズを確認してから rsync ＋ `php bin/console cache:clear`（さくらは `php` が 8.2 系でOK）

## 成果フロー（仕様として固定されている挙動）
- クリック → 購入で pending 計上（報酬 = 注文額 × 料率% の切り捨て）
- `confirm_after_days` 経過で日次バッチ `affiliate:confirm-rewards`（04:00）が confirmed に変更
- 管理画面「支払い」(`/admin/payouts`) でアフィリエイター単位に集計し、最低支払額以上なら一括 paid ＋ `payouts` に履歴記録（閾値未満は繰り越し）
- **料率は成果発生時点で `rewards.rate_applied` に固定**される。後から設定を変えても過去成果は変わらない
- キャンセル連携は EC-CUBE の**注文詳細画面でのステータス変更**でのみ発火する（一覧の一括操作では発火しない）。paid 後はキャンセルでも自動取消されない＝手動対応

## 認証
- アフィリエイター: メール＋パスワード（`/affiliate/login`、承認済みのみ）。マイページ `/affiliate/mypage`
- 管理画面: Google OAuth（`/admin/login`）。テストモードのため、Google Cloud Console（プロジェクト `tairiku-Affiliate`）のテストユーザーに登録された人だけログイン可
- 認証・権限まわりの変更は高リスク扱い（グローバル CLAUDE.md の High-Risk 手順に従う）

## 注意（未確定）
- トップLPは調整中で**未公開**。`web.php` は landing 化済みだが route:cache は旧設定のまま。勝手に公開しない
