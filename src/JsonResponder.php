<?php

namespace Rapture\Adr;

use Rapture\Http\Stream;

/**
 * JSON responder for ADR pattern
 *
 * @package Rapture\Adr
 * @author  Iulian N. <rapture@iuliann.ro>
 * @license LICENSE MIT
 */
class JsonResponder extends Responder
{
    /**
     * __invoke
     *
     * @param array $data Action data
     *
     * @return void
     */
    public function __invoke(array $data)
    {
        $stream = new Stream(fopen('php://memory', 'r+'));
        $stream->write(json_encode($data));

        $this->response
            ->withHeader('Content-type', 'application/json')
            ->withBody($stream)
            ->send();
    }
}
