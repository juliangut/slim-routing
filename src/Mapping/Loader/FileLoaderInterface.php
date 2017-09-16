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

namespace Jgut\Slim\Routing\Mapping\Loader;

/**
 * File mapping loader interface.
 */
interface FileLoaderInterface extends LoaderInterface
{
    /**
     * Get routing data.
     *
     * @param string[] $loadingPaths
     *
     * @return array
     */
    public function getMappingData(array $loadingPaths): array;
}
