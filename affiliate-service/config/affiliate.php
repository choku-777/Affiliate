<?php

$parse = fn ($value) => array_values(array_filter(array_map(
    fn ($e) => strtolower(trim($e)),
    explode(',', (string) $value)
)));

// 管理（全権）／運用（設定変更以外）のメールを読み込む。
// 旧 ADMIN_ALLOWED_EMAILS は後方互換として「管理」に含める。
$managers = array_values(array_unique(array_merge(
    $parse(env('ADMIN_MANAGER_EMAILS')),
    $parse(env('ADMIN_ALLOWED_EMAILS'))
)));
$operators = $parse(env('ADMIN_OPERATOR_EMAILS'));

// email => role の対応表（両方に登録された場合は manager を優先）
$roles = [];
foreach ($operators as $email) {
    $roles[$email] = 'operator';
}
foreach ($managers as $email) {
    $roles[$email] = 'manager';
}

return [
    // EC-CUBE プラグインと共有するAPIキー（postback認証用）
    'api_key' => env('AFFILIATE_API_KEY'),

    // email => 'manager'|'operator'
    'roles' => $roles,

    // ログインを許可する全メール
    'admin_emails' => array_keys($roles),

    // ショップのベースURL（発行URL組み立て用）
    'shop_url' => rtrim((string) env('SHOP_BASE_URL', 'https://umashippo.jp'), '/'),
];
