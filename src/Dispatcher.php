<?php

namespace Rapture\Adr;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rapture\Http\Exception\HttpMethodNotAllowedException;
use Rapture\Http\Exception\HttpNotFoundException;
use Rapture\Http\Exception\HttpNotImplementedException;
use Rapture\Http\Response;
use Rapture\Router\Definition\RouterInterface;

/**
 * Class Dispatcher
 *
 * @package Rapture\Adr
 * @author  Iulian N. <rapture@iuliann.ro>
 * @license LICENSE MIT
 */
class Dispatcher
{
    /** @var string */
    protected $appName = 'Demo';

    /** @var RouterInterface */
    protected $router;

    /**
     * Dispatcher constructor.
     *
     * @param string          $app    App name
     * @param RouterInterface $router Router collector
     */
    public function __construct(string $app, RouterInterface $router)
    {
        $this->app = $app;
        $this->router = $router;
    }

    /**
     * @param ServerRequestInterface $request  Request object
     * @param ResponseInterface      $response Response object
     *
     * @throws HttpMethodNotAllowedException
     * @throws HttpNotFoundException
     * @throws HttpNotImplementedException
     *
     * @return void
     */
    public function dispatch(ServerRequestInterface $request, ResponseInterface $response)
    {
        list($statusCode, $handler, $params) = $this->router->route($request->getMethod(), $request->getUri()->getPath());

        if ($statusCode === Response::STATUS_NOT_FOUND) {
            throw new HttpNotFoundException();
        } elseif ($statusCode === Response::STATUS_METHOD_NOT_ALLOWED) {
            throw new HttpMethodNotAllowedException();
        }

        foreach ((array)$params as $name => $value) {
            $request->withAttribute($name, $value);
        }

        if ($handler instanceof \Closure) {
            $handler($request, $response);

            return;
        }

        $actionName = $handler[0] == '\\'
            ? $handler
            : $this->app . '\\Action\\' . $handler;

        if (class_exists($actionName)) {
            /** @var Action $action */
            $action = new $actionName($request, $response);
        } else {
            throw new HttpNotImplementedException();
        }

        self::dispatchAction($action);
    }

    /**
     * @param Action $action Action object
     *
     * @return void
     */
    public static function dispatchAction(Action $action)
    {
        $action->preInvoke();
        $data = $action();
        $action->postInvoke($data);

        /** @var Responder $responder */
        $responder = $action->getResponder($data);
        if (!$responder) {
            return;
        }

        if ($responder instanceof Responder) {
            $responder->preInvoke($data);
            $responder($data);
            $responder->postInvoke($data);
        }
    }
}
