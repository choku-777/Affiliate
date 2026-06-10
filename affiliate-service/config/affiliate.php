<?php

return [
    // EC-CUBE プラグインと共有するAPIキー（postback認証用）
    'api_key' => env('AFFILIATE_API_KEY'),

    // 管理画面にGoogleログインを許可するメールアドレス（カンマ区切り）
    'admin_emails' => array_values(array_filter(array_map(
        fn ($e) => strtolower(trim($e)),
        explode(',', (string) env('ADMIN_ALLOWED_EMAILS'))
    ))),

    // ショップのベースURL（発行URL組み立て用）
    'shop_url' => rtrim((string) env('SHOP_BASE_URL', 'https://shizenha-inu.life'), '/'),
];
