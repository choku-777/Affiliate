# SNS投稿の申告・承認・掲載 仕様書

サンプルを受け取ったアンバサダーにSNS投稿をしてもらい、投稿URLを申告 → 承認 → うましっぽ公式サイトに掲載する仕組み。

- 対象: `affiliate-service`（Laravel）＋ うましっぽEC-CUBE（掲載ページのみ）
- 作成日: 2026-09-12
- ステータス: **段階1 実装・本番反映済み（2026-09-12）／動作確認済み**。段階2（公開API・掲載ページ）は未着手

---

## 1. 背景・目的

- サンプル提供の**交換条件としてSNS投稿を必須**にする（申込時に同意を取る）
- 投稿を公式サイトで紹介することで、アンバサダーの投稿価値を高め、サイトの信頼性も上げる
- ハッシュタグでの自動収集は3社とも制約が大きいため、**URL申告方式**で集める

## 2. 決定事項

| 項目 | 内容 |
|---|---|
| 申告できる人 | **アンバサダーのみ**（マイページから。本人確認・発送履歴と自動で紐付く） |
| 掲載先 | **うましっぽ公式サイト（umashippo.jp）のみ** |
| 投稿の位置づけ | **サンプルの交換条件**（申込時に「投稿する」「公式サイトでの引用に同意」をチェック必須） |
| 特典 | なし（承認＝公式サイトで紹介、が特典） |
| ハッシュタグ | **`#うましっぽ`** 1本＋ **`#PR`** |
| 公式アカウント | **未作成のため後回し**。設定画面に入力欄を用意し、空の間は案内に表示しない |
| 投稿期限 | **発送から2週間（14日）以内**。期限日をマイページに明示。日数は設定で変更可 |
| Instagram | メンション＋#PR＋ブランドタグのみ。共同投稿は使わない。**ストーリーズ不可** |
| X | 先頭に@を置かない。ハッシュタグは2〜3個 |
| TikTok | 「コンテンツの開示」ON必須。公開範囲「全員」。18歳以上 |

### 法務上の必須事項
- 無料サンプルと引き換えの投稿は**景品表示法上の「事業者の表示」**にあたるため、**#PR を必ず入れてもらう**
- **#PR が無い投稿は承認しない**（管理画面で確認チェックを必須にする）
- 埋め込み表示は各社規約上OK。画像を保存して再掲載するのは別途許諾が必要なため**行わない**

---

## 3. サンプル申込フォームの変更（既存機能）

申込時に以下の**同意チェック（必須）**を追加する。

```
□ サンプル到着後、X・Instagram・TikTok のいずれかにご感想を投稿します
□ 投稿を「うましっぽ」公式サイト・公式SNSで紹介することに同意します
```

- 同意日時を `sample_requests.sns_consent_at` に保存
- フォーム内に「投稿のお願い」（ハッシュタグ・メンション・#PR・公開設定）を表示

---

## 4. 機能① マイページ：投稿URLの申告

### 表示条件
サンプルの申込がある（未発送でも可）アンバサダー。発送済みで**まだ申告がない**場合は「投稿のお願い」を目立たせる。

### 申告フォーム `/affiliate/sns-posts`
| 項目 | 内容 |
|---|---|
| 投稿URL | 必須。X / Instagram / TikTok のURLのみ受け付ける |
| ひとこと | 任意 |

### サーバー側の処理
1. URLから**プラットフォームを自動判定**（x.com, twitter.com / instagram.com / tiktok.com）
2. **TikTokの短縮URL**（vt.tiktok.com）はサーバーがリダイレクト先を解決して本来のURLに変換
3. URLを**正規化**（クエリ除去・末尾スラッシュ統一）し、**投稿IDを抽出**
4. 同じ投稿の**重複申告は拒否**
5. 状態 `pending`（確認中）で保存 → Discord通知

### 申告一覧（マイページ）
自分の申告と状態を表示：確認中／掲載中／非掲載（却下理由は表示しない）

---

## 5. 機能② 管理画面：投稿の承認 `/admin/sns-posts`

| 機能 | 内容 |
|---|---|
| 一覧 | 初期表示は「確認中」。絞り込み：確認中／掲載中／却下／非表示／すべて |
| プレビュー | 行を開くと**実際の投稿を埋め込み表示**（承認前に実物を確認できる） |
| #PR確認 | チェック欄。**チェックしないと承認ボタンが押せない** |
| 確認済みにする | 状態 → **確認済み（掲載は保留）**。「見てOKだったがまだ載せない」を記録する。#PR確認が条件 |
| 掲載する | 状態 → 掲載中。承認者・日時を記録。#PR確認が条件 |
| 却下 | 理由を入力（#PRなし／非公開／内容不適切 など）。アンバサダーには理由を見せない |
| 掲載停止 | 掲載中 → 非表示（削除された投稿・取り下げ依頼に対応） |
| 表示順 | 数字で指定。未指定は承認日の新しい順 |

### 未投稿者の把握
**サンプル発送画面**の一覧に「**投稿**」列を追加し、発送済みなのに申告がない人をひと目で分かるようにする。
（例：「未申告」「確認中」「掲載中」のバッジ）
- 発送日＋設定日数（初期14日）を**投稿期限**とし、期限を過ぎて未申告の人は**赤いバッジ「期限切れ」**で表示
- マイページ側にも「投稿期限：YYYY年M月D日」を表示する

---

## 6. 機能③ 公開API ＋ EC-CUBE「みんなの声」ページ

### 公開API（Laravel）
`GET /api/sns-posts` — 認証不要・読み取りのみ

```json
[
  {"platform": "instagram", "url": "https://www.instagram.com/p/XXXX/", "post_id": "XXXX", "approved_at": "2026-09-20"},
  {"platform": "x",         "url": "https://x.com/user/status/123",     "post_id": "123",  "approved_at": "2026-09-19"},
  {"platform": "tiktok",    "url": "https://www.tiktok.com/@user/video/456", "post_id": "456", "approved_at": "2026-09-18"}
]
```
- 掲載中のみ・表示順どおり
- サーバー側で5分キャッシュ（負荷対策）

### EC-CUBE側（umashippo.jp）
- ユーザー定義ページ `user_data/voices`（仮）
- JavaScriptでAPIを取得し、プラットフォームごとに公式の埋め込みコードを生成して並べる
- 投稿者が削除しても空になるだけでページは壊れない

### 埋め込みコード（3社とも認証不要）

| SNS | 形式 |
|---|---|
| X | `<blockquote class="twitter-tweet"><a href="URL"></a></blockquote>` ＋ `platform.twitter.com/widgets.js` |
| Instagram | `<blockquote class="instagram-media" data-instgrm-captioned data-instgrm-permalink="URL" data-instgrm-version="14"></blockquote>` ＋ `instagram.com/embed.js`<br>※ `data-instgrm-captioned` が無いと本文（キャプション）が出ず #PR を確認できない |
| TikTok | `<blockquote class="tiktok-embed" cite="URL" data-video-id="ID"></blockquote>` ＋ `tiktok.com/embed.js` |

---

## 7. データ設計

### `sns_posts`（新規）

| カラム | 型 | 説明 |
|---|---|---|
| id | bigint | |
| affiliate_id | FK(affiliates) | 申告者 |
| sample_request_id | FK(sample_requests) null | 対応するサンプル申込 |
| platform | varchar(16) | `x` / `instagram` / `tiktok` |
| post_url | varchar(500) | 申告されたURL（原文） |
| normalized_url | varchar(500) unique | 正規化後URL（重複判定用） |
| post_id | varchar(64) | 投稿ID（埋め込み用） |
| status | varchar(16) | `pending`（確認中）/ `checked`（確認済み・掲載保留）/ `approved`（掲載中）/ `rejected` / `hidden` |
| note | text null | アンバサダーのひとこと |
| pr_checked_at / pr_checked_by | | #PR確認の記録 |
| approved_at / approved_by | | 承認の記録 |
| reject_reason | varchar(255) null | 却下理由 |
| sort_order | integer | 表示順 |
| created_at / updated_at | | |

### `sample_requests`（既存に追加）
| カラム | 型 | 説明 |
|---|---|---|
| sns_consent_at | timestamp null | 投稿・引用への同意日時 |

### `settings`（既存に追加）
| カラム | 型 | 説明 |
|---|---|---|
| sns_hashtag | varchar(64) | ブランドハッシュタグ（初期値 `#うましっぽ`） |
| sns_account_x | varchar(64) null | X の公式アカウント（空なら案内に出さない） |
| sns_account_instagram | varchar(64) null | Instagram の公式アカウント |
| sns_account_tiktok | varchar(64) null | TikTok の公式アカウント |
| sns_post_deadline_days | integer | 投稿期限の日数（初期値 14） |

---

## 8. ルート一覧（追加分）

```
# アンバサダー側（要ログイン）
GET   /affiliate/sns-posts            申告フォーム＋自分の申告一覧
POST  /affiliate/sns-posts            申告

# 管理側
GET   /admin/sns-posts                一覧
POST  /admin/sns-posts/{id}/pr-check  #PR確認
POST  /admin/sns-posts/{id}/approve   承認
POST  /admin/sns-posts/{id}/reject    却下
POST  /admin/sns-posts/{id}/hide      掲載停止
POST  /admin/sns-posts/{id}/sort      表示順

# 公開API（認証なし）
GET   /api/sns-posts                  掲載中の投稿一覧（JSON）
```

## 9. 権限
承認・却下：運用担当（パート）＋管理者の両方

## 10. 通知
申告があったとき、**サンプル用のDiscord Webhook**へ通知（アンバサダー名／SNS／URL）

---

## 11. 進め方

| 段階 | 内容 |
|---|---|
| **1** | 申込フォームの同意追加 → 申告フォーム → 管理画面の承認 → 発送画面の「投稿」列 |
| **2** | 公開API → EC-CUBE「みんなの声」ページ |
| **3**（任意） | 削除された投稿の自動チェック／未投稿者へのリマインドメール |

段階1だけでも「誰が投稿したか・#PRが入っているか」を一元管理でき、手動での掲載運用が始められる。

---

## 12. 未確定事項（実装前に決めるもの）

| # | 内容 | 状態 |
|---|---|---|
| 1 | ブランドハッシュタグ | **確定**（`#うましっぽ` のみ） |
| 2 | 公式アカウント名 | **後回し**（作成後に設定画面から入力） |
| 3 | 投稿期限 | **確定**（発送から14日以内） |
| 4 | 「みんなの声」ページのURL・デザイン | 段階2で決める |

## 13. 注意点
- `sns_posts` は本番DBへのテーブル追加。マイグレーション前にバックアップ
- 公開APIは掲載中の投稿URLだけを返す（氏名・住所などは一切含めない）
- 埋め込み表示は各SNSのスクリプトに依存するため、SNS側の仕様変更で表示が変わる可能性がある
