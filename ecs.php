<?php

/*
 * slim-routing (https://github.com/juliangut/slim-routing).
 * Slim framework routing.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

declare(strict_types=1);

use Jgut\ECS\Config\ConfigSet74;
use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\NoSilencedErrorsSniff;
use PhpCsFixerCustomFixers\Fixer\NoNullableBooleanTypeFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

$header = <<<'HEADER'
slim-routing (https://github.com/juliangut/slim-routing).
Slim framework routing.

@license BSD-3-Clause
@link https://github.com/juliangut/slim-routing
@author Julián Gutiérrez <juliangut@gmail.com>
HEADER;

return static function (ECSConfig $ecsConfig) use ($header): void {
    $ecsConfig->paths([
        __FILE__,
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $skips = [
        NoSilencedErrorsSniff::class . '.Discouraged' => [
            __DIR__ . '/src/Configuration.php',
            __DIR__ . '/src/Route/RouteResolver.php',
        ],
        NoNullableBooleanTypeFixer::class => [
            __DIR__ . '/src/Mapping/Attribute/Route.php',
        ],
    ];

    if (\PHP_VERSION_ID < 80_000) {
        $skips = array_merge(
            $skips,
            [
                __DIR__ . '/tests/Routing/Mapping/Files/Classes/Invalid/Attribute',
                __DIR__ . '/tests/Routing/Mapping/Files/Classes/Valid/Attribute',
            ],
        );
    }

    (new ConfigSet74())
        ->setHeader($header)
        ->enablePhpUnitRules()
        ->setAdditionalSkips($skips)
        ->configure($ecsConfig);
};
