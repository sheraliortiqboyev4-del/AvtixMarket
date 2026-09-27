<?php
/**
 * Products API
 * Returns available products for purchase
 */

require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Hardcoded products (same as bot)
$products = [
    'stars' => [
        [
            'id' => 'stars_500',
            'name' => '500 Stars',
            'quantity' => 500,
            'price' => 25000,
            'type' => 'stars'
        ],
        [
            'id' => 'stars_1000',
            'name' => '1000 Stars',
            'quantity' => 1000,
            'price' => 50000,
            'type' => 'stars'
        ],
        [
            'id' => 'stars_2500',
            'name' => '2500 Stars',
            'quantity' => 2500,
            'price' => 100000,
            'type' => 'stars'
        ],
        [
            'id' => 'stars_5000',
            'name' => '5000 Stars',
            'quantity' => 5000,
            'price' => 200000,
            'type' => 'stars'
        ]
    ],
    'premium' => [
        [
            'id' => 'premium_1m',
            'name' => '1 OY Premium',
            'months' => 1,
            'price' => 50000,
            'type' => 'premium'
        ],
        [
            'id' => 'premium_3m',
            'name' => '3 OY Premium',
            'months' => 3,
            'price' => 130000,
            'type' => 'premium'
        ],
        [
            'id' => 'premium_6m',
            'name' => '6 OY Premium',
            'months' => 6,
            'price' => 250000,
            'type' => 'premium'
        ],
        [
            'id' => 'premium_12m',
            'name' => '12 OY Premium',
            'months' => 12,
            'price' => 450000,
            'type' => 'premium'
        ]
    ],
    'ton' => [
        [
            'id' => 'ton_10',
            'name' => '10 TON',
            'amount' => 10,
            'price' => 100000,
            'type' => 'ton'
        ],
        [
            'id' => 'ton_50',
            'name' => '50 TON',
            'amount' => 50,
            'price' => 450000,
            'type' => 'ton'
        ],
        [
            'id' => 'ton_100',
            'name' => '100 TON',
            'amount' => 100,
            'price' => 850000,
            'type' => 'ton'
        ],
        [
            'id' => 'ton_500',
            'name' => '500 TON',
            'amount' => 500,
            'price' => 4000000,
            'type' => 'ton'
        ]
    ],
    'gift' => [
        [
            'id' => 'gift_30',
            'name' => '1 OY Gift Card',
            'months' => 1,
            'price' => 50000,
            'type' => 'gift'
        ],
        [
            'id' => 'gift_90',
            'name' => '3 OY Gift Card',
            'months' => 3,
            'price' => 130000,
            'type' => 'gift'
        ],
        [
            'id' => 'gift_180',
            'name' => '6 OY Gift Card',
            'months' => 6,
            'price' => 250000,
            'type' => 'gift'
        ]
    ]
];

sendResponse(true, 'Products loaded', ['data' => $products]);

?>
