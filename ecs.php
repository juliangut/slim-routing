<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

use Jgut\ECS\Config\ConfigSet80;
use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\NoSilencedErrorsSniff;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer;
use PhpCsFixerCustomFixers\Fixer\NoNullableBooleanTypeFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $header = <<<'HEADER'
    (c) 2017-{{year}} Julián Gutiérrez <juliangut@gmail.com>

    @license BSD-3-Clause
    @link https://github.com/juliangut/slim-routing
    HEADER;

    $ecsConfig->paths([
        __FILE__,
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);
    $ecsConfig->cacheDirectory('.ecs.cache');

    $skips = [
        NoSilencedErrorsSniff::class . '.Forbidden' => [
            __DIR__ . '/src/Configuration.php',
            __DIR__ . '/src/Route/RouteResolver.php',
            __DIR__ . '/src/Mapping/Annotation/Router.php',
        ],
        NoNullableBooleanTypeFixer::class => __DIR__ . '/src/Mapping/Attribute/Route.php',
    ];

    if (\PHP_VERSION_ID < 80_100) {
        $skips[PhpdocAlignFixer::class] = [
            __DIR__ . '/src/Route/Route.php',
            __DIR__ . '/src/RouteCollector.php',
        ];
    }

    (new ConfigSet80())
        ->setHeader($header)
        ->enablePhpUnitRules()
        ->setAdditionalSkips($skips)
        ->configure($ecsConfig);
};
