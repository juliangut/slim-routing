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

namespace Jgut\Slim\Routing\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Abstract response type.
 */
abstract class AbstractResponseType implements ResponseTypeInterface
{
    /**
     * PSR-7 response.
     *
     * @var ResponseInterface
     */
    protected $response;

    /**
     * Set PSR-7 response.
     *
     * @param ResponseInterface $response
     *
     * @return static
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponse()
    {
        return $this->response;
    }
}
