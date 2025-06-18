<?php

declare(strict_types=1);

if (version_compare(PHP_VERSION, '8.2.0', '>=')) {
    passthru('composer require --dev phpstan/mutant-killer-infection-runner --no-scripts');
}
