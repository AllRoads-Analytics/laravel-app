<?php

return [
    'stripe_mode' => env('STRIPE_MODE', 'test'), // test or live
    'stripe_public_key' => env('STRIPE_KEY'),

    'plans' => [
        'free' => [
            'id' => 'free',
            'label' => 'Free',
            'monthly_price' => 0,
            'limit_funnels' => 1,
            'limit_data_view_days' => 7,
            'limit_users' => 1,
            'limit_pageviews_per_month' => 10000,
            'test_monthly_price_stripe_id' => null,
            'live_monthly_price_stripe_id' => null,
        ],
        'basic' => [
            'id' => 'basic',
            'label' => 'Basic',
            'monthly_price' => 7,
            'limit_funnels' => 3,
            'limit_data_view_days' => 30,
            'limit_users' => 3,
            'limit_pageviews_per_month' => 50000,
            'test_monthly_price_stripe_id' => 'price_1KHEeuAOLKXPgWftpOcOoP24',
            'live_monthly_price_stripe_id' => 'price_1KHECiAOLKXPgWftjlEG1gAp',
        ],
        'plus' => [
            'id' => 'plus',
            'label' => 'Plus',
            'monthly_price' => 15,
            'limit_funnels' => 10,
            'limit_data_view_days' => 90,
            'limit_users' => 5,
            'limit_pageviews_per_month' => 100000,
            'test_monthly_price_stripe_id' => 'price_1KHEfbAOLKXPgWft9Vrzi5nz',
            'live_monthly_price_stripe_id' => 'price_1KHECoAOLKXPgWftSiHupACn',
        ],
        'pro' => [
            'id' => 'pro',
            'label' => 'Pro',
            'monthly_price' => 30,
            'limit_funnels' => 20,
            'limit_data_view_days' => 365,
            'limit_users' => 10,
            'limit_pageviews_per_month' => 500000,
            'test_monthly_price_stripe_id' => 'price_1KHEgpAOLKXPgWftmwbOmhPq',
            'live_monthly_price_stripe_id' => 'price_1KHECuAOLKXPgWft4FXbxpYG',
        ],
        'enterprise' => [
            'id' => 'enterprise',
            'label' => 'Enterprise',
            'monthly_price' => 50,
            'limit_funnels' => null,
            'limit_data_view_days' => null,
            'limit_users' => null,
            'limit_pageviews_per_month' => 1000000,
            'test_monthly_price_stripe_id' => 'price_1KDbvlAOLKXPgWft3hgmRrYM',
            'live_monthly_price_stripe_id' => 'price_1KHECyAOLKXPgWft4pyy3PM9',
        ],
    ],
];
