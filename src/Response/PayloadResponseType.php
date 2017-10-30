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

/**
 * Generic json response type.
 */
class PayloadResponseType extends AbstractResponseType
{
    /**
     * Payload.
     *
     * @var mixed
     */
    protected $payload;

    /**
     * Get payload.
     *
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Set Payload.
     *
     * @param mixed $payload
     *
     * @return self
     */
    public function setPayload($payload): PayloadResponseType
    {
        $this->payload = $payload;

        return $this;
    }
}
