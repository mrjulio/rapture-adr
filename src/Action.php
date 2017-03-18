<?php

namespace Rapture\Adr;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rapture\Acl\Definition\RequesterInterface;
use Rapture\Auth\Authentication;
use Rapture\Auth\Definition\UserInterface;
use Rapture\Cache\Definition\CacheInterface;
use Rapture\Container\Container;
use Rapture\Http\Request;
use Rapture\Http\Response;
use Rapture\Router\Router;
use Rapture\Session\Adapter\Php;

/**
 * Class Action
 *
 * @package Rapture\Adr
 * @author  Iulian N. <rapture@iuliann.ro>
 * @license LICENSE MIT
 */
class Action
{
    const HTML_RESPONDER = 'Rapture\Adr\Responder';
    const JSON_RESPONDER = 'Rapture\Adr\JsonResponder';
    const NULL_RESPONDER = '';

    /** @var Request */
    protected $request;

    /** @var Response */
    protected $response;

    /** @var Container */
    protected $container;

    /**
     * @param ServerRequestInterface $request  Request object
     * @param ResponseInterface      $response Response object
     */
    public function __construct(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->request  = $request;
        $this->response = $response;
    }

    /**
     * Hook triggered before action
     *
     * @return void
     */
    public function preInvoke()
    {
        // pre-invoke hook
    }

    /**
     * __invoke
     *
     * @return array
     */
    public function __invoke():array
    {
        return [];
    }

    /**
     * Hook triggered after invoking the action
     *
     * @param array $data Data to be send to responder
     *
     * @return void
     */
    public function postInvoke(array &$data)
    {
        // post-invoke hook
    }

    /**
     * Responder
     *
     * @param array $data Data to be send to responder
     *
     * @return Responder
     */
    public function getResponder(array $data)
    {
        $responderClassName = $this->getResponderClass($data);

        return $responderClassName
            ? new $responderClassName($this->response, $this)
            : null;
    }

    /**
     * Get responder name
     *
     * @param array $data Response data from __invoke method
     *
     * @return string
     */
    public function getResponderClass(array $data):string
    {
        return str_replace('\\Action\\', '\\Responder\\', get_class($this));
    }

    /**
     * redirect
     *
     * @param string $uri        URI to redirect to
     * @param int    $statusCode Status code
     * @param string $prefix     Route prefix
     *
     * @return void
     */
    public function redirect($uri, $statusCode = 302, $prefix = '/')
    {
        $url = $prefix . trim($uri, $prefix);
        header("Location: {$url}", true, $statusCode);
        exit;
    }

    /*
     * Services
     */

    /**
     * Request object
     *
     * @return Request
     */
    public function request():Request
    {
        return $this->request;
    }

    /**
     * Response object
     *
     * @return Response
     */
    public function response():Response
    {
        return $this->response;
    }

    /**
     * service
     *
     * @param string $name Service class name or alias
     *
     * @return object
     */
    public function service($name)
    {
        if (!$this->container) {
            $this->container = Container::instance();
        }

        return $this->container[$name];
    }

    /**
     * @return \ArrayObject
     */
    public function config()
    {
        return $this->service('config');
    }

    /**
     * getRouter
     *
     * @return Router
     */
    public function router()
    {
        return $this->service('router');
    }

    /**
     * getSession
     *
     * @return Php
     */
    public function session()
    {
        return $this->service('session');
    }

    /**
     * getAuth
     *
     * @return Authentication
     */
    public function auth()
    {
        return $this->service('auth');
    }

    /**
     * getAuth
     *
     * @return \Rapture\Acl\Adapter\Php
     */
    public function acl()
    {
        return $this->service('acl');
    }

    /**
     * getCache
     *
     * @return CacheInterface
     */
    public function cache()
    {
        return $this->service('cache');
    }

    /**
     * getUser
     *
     * @return UserInterface|RequesterInterface
     */
    public function user()
    {
        return $this->service('auth')->user();
    }
}
