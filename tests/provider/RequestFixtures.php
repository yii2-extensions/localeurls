<?php

declare(strict_types=1);

namespace yii2\extensions\localeurls\tests\provider;

/**
 * Builds the session, cookie, request and server fixtures of a redirect provider row.
 *
 * @phpstan-type Fixtures array{
 *   cookie: array<string, string>,
 *   request: array<string, mixed>,
 *   server: array<string, string>,
 *   session: array<string, string>,
 * }
 */
trait RequestFixtures
{
    /**
     * Builds the request fixtures of a row.
     *
     * @param string|null $session Language stored in the session, or `null` for none.
     * @param string|null $cookie Language stored in the language cookie, or `null` for none.
     * @param array $accept Acceptable languages of the request, in order of preference.
     * @param string|null $geo Country code of the GeoIP server variable, or `null` for none.
     *
     * @phpstan-param list<string> $accept
     * @phpstan-return Fixtures
     */
    private static function fixtures(
        string|null $session = null,
        string|null $cookie = null,
        array $accept = [],
        string|null $geo = null,
    ): array {
        return [
            'cookie' => $cookie === null ? [] : ['_language' => $cookie],
            'request' => $accept === [] ? [] : ['acceptableLanguages' => $accept],
            'server' => $geo === null ? [] : ['HTTP_X_GEO_COUNTRY' => $geo],
            'session' => $session === null ? [] : ['_language' => $session],
        ];
    }
}
