<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests\provider;

use Generator;

/**
 * Data provider for {@see \yii2\extensions\localeurls\tests\UrlLanguageRegressionTest} host-rule test cases.
 */
final class UrlLanguageRegressionProvider
{
    /**
     * @phpstan-return Generator<string, array{
     *   '//www.example.com'|'https://www.example.com',
     *   ''|'/base',
     *   bool,
     * }>
     */
    public static function hostRuleLayouts(): Generator
    {
        yield 'protocol-relative host, root, script name hidden' => ['//www.example.com', '', false];
        yield 'protocol-relative host, base URL, script name hidden' => ['//www.example.com', '/base', false];
        yield 'protocol-relative host, root, script name shown' => ['//www.example.com', '', true];
        yield 'protocol-relative host, base URL, script name shown' => ['//www.example.com', '/base', true];
        yield 'HTTPS host, root, script name hidden' => ['https://www.example.com', '', false];
        yield 'HTTPS host, base URL, script name hidden' => ['https://www.example.com', '/base', false];
        yield 'HTTPS host, root, script name shown' => ['https://www.example.com', '', true];
        yield 'HTTPS host, base URL, script name shown' => ['https://www.example.com', '/base', true];
    }
}
