<?php

declare(strict_types=1);

use yii\localeurls\UrlLanguageManager;

return [
    'yii2.urlManager.class' => UrlLanguageManager::class,
    'yii2.localeurls.languages' => ['en-*', 'es-*', 'fr-*', 'pt-*', 'ru-*'],
];
