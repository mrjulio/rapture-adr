<?php

namespace Rapture\Adr;

use Rapture\Container\Container;
use Rapture\Http\Definition\HttpExceptionInterface;
use Rapture\Http\Response;
use Rapture\Http\Stream;

/**
 * Class JsonHandler
 *
 * @package Rapture\Adr
 * @author  Iulian N. <rapture@iuliann.ro>
 * @license LICENSE MIT
 */
class JsonHandler
{
    /**
     * @param int    $errNo      Error number
     * @param string $errStr     Error string
     * @param string $errFile    Error file
     * @param int    $errLine    Error line
     * @param array  $errContext Error context
     *
     * @return void
     */
    public static function error(int $errNo, string $errStr, string $errFile = '', int $errLine = 0, array $errContext = [])
    {
        error_log(sprintf("#%d: %s(%d) %s", $errNo, $errFile, $errLine, $errStr));
    }

    /**
     * @param \Exception $exception Exception thrown
     *
     * @return void
     */
    public static function exception($exception)
    {
        if ($exception instanceof HttpExceptionInterface) {
            /** @var Response $response */
            $response = Container::instance()['response'];
            $response->withStatus($exception->getCode());

            $message = $exception->getMessage();

            $stream = new Stream(fopen('php://memory', 'r+'));

            $json = json_decode($message, true);
            $stream->write(
                json_encode(
                    json_last_error() === JSON_ERROR_NONE ? $json + ['message' => ''] : ['message' => $message]
                )
            );

            $response->withHeader('Content-type', 'application/json')->withBody($stream)->send();
        }

        error_log($exception);
    }
}
