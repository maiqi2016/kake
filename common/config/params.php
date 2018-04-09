<?php
$secondSite = 'www';
$secondBackend = 'backend';
$secondSource = 'source';
$secondPic = 'pic';
$secondPassport = 'passport';
$domain = DOMAIN;

return [
    'app_name' => 'KAKE',
    'api_token_backend' => '#maiqi$kk',
    'api_token_frontend' => '#maiqi$kk',
    'use_cache' => true,

    'app_title' => 'KAKE旅行',
    'app_description' => 'KAKE旅行',
    'app_keywords' => 'KAKE旅行',

    'frontend_url' => "//{$secondSite}.{$domain}",
    'frontend_source' => "//{$secondSource}.{$domain}/kake/frontend",

    'backend_url' => "http://{$secondBackend}.{$domain}",
    'backend_source' => "//{$secondSource}.{$domain}/kake/backend",

    'tmp_path' => '/tmp/kake',
    'upload_url' => "//{$secondPic}.{$domain}",
    'passport_url' => "//{$secondPassport}.{$domain}",

    'wechat_callback' => "//{$secondSite}.${domain}/",
    'alipay_callback' => "//{$secondSite}.${domain}/",

    'thrift_ip' => '172.19.222.65',
    'thrift_port' => '8888',

    'site_search_ad_keyword' => null,
    'site_search_ad_url' => null,

    'site_focus_limit' => 6,
    'site_sale_limit' => 6,
    'site_ad_banner_limit' => 3,
    'site_ad_focus_limit' => 3,
    'site_product_limit' => 10,

    'product_page_size' => 8,
    'order_page_size' => 8,
    'order_notice_user_ids' => '',

    'upgrade' => false,
    'upgrade_title' => 'System upgrade',
    'upgrade_minute' => 15,
    'upgrade_message' => '系统版本升级中，本次升级约需 %d 分钟，尽请期待',

    'order_pay_timeout' => 30,

    'distribution_limit' => 5,
    'distribution_items_limit' => 7,
    'distribution_ad_focus_limit' => 5,
    'distribution_ad_banner_limit' => 3,

    'commission_min_price' => 99,
    'withdraw_min' => 100,

    'activity_producer_share_title' => '我要带你去开房~',
    'activity_producer_share_description' => '快来领取今日福利，活动天天有，惊喜无上限~~',
];