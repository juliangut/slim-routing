<?php

/*
 * (c) 2017-2025 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

use Jgut\ECS\Config\ConfigSet80;
use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\NoSilencedErrorsSniff;
use PhpCsFixer\Fixer\FunctionNotation\MethodArgumentSpaceFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer;
use PhpCsFixerCustomFixers\Fixer\NoNullableBooleanTypeFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

$skips = [
    NoSilencedErrorsSniff::class . '.Forbidden' => [
        __DIR__ . '/src/Configuration.php',
        __DIR__ . '/src/Route/RouteResolver.php',
        __DIR__ . '/src/Mapping/Annotation/Router.php',
    ],
    NoNullableBooleanTypeFixer::class => __DIR__ . '/src/Mapping/Attribute/Route.php',
];
if (\PHP_VERSION_ID < 80_100) {
    $skips = array_merge(
        $skips,
        [
            PhpdocAlignFixer::class => [
                __DIR__ . '/src/Route/Route.php',
                __DIR__ . '/src/RouteCollector.php',
            ],
            MethodArgumentSpaceFixer::class => [
                __DIR__ . '/src/Mapping/Attribute/Group.php',
                __DIR__ . '/src/Mapping/Attribute/Route.php',
            ],
        ],
    );
}

$configSet = (new ConfigSet80())
    ->setHeader(<<<'HEADER'
    (c) 2017-{{year}} Julián Gutiérrez <juliangut@gmail.com>

    @license BSD-3-Clause
    @link https://github.com/juliangut/slim-routing
    HEADER)
    ->enablePhpUnitRules()
    ->setAdditionalSkips($skips);
$paths = [
    __FILE__,
    __DIR__ . '/src',
    __DIR__ . '/tests',
];

if (!method_exists(ECSConfig::class, 'configure')) {
    return static function (ECSConfig $ecsConfig) use ($configSet, $paths): void {
        $ecsConfig->paths($paths);
        $ecsConfig->cacheDirectory('.ecs.cache');

        $configSet->configure($ecsConfig);
    };
}

return $configSet
    ->configureBuilder()
    ->withCache('.ecs.cache')
    ->withPaths($paths);
