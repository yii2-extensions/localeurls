<?php

declare(strict_types=1);

use yii2\extensions\localeurls\UrlLanguageManager;

return [
    'components' => [
        'urlManager' => [
            'class' => UrlLanguageManager::class,
        ],
    ],
];
