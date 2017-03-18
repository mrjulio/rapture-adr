<?php

namespace Rapture\Adr;

use Psr\Http\Message\ResponseInterface;
use Rapture\Http\Response;
use Rapture\Http\Stream;
use Rapture\Template\Template;

/**
 * Responder from ADR pattern
 *
 * @package Rapture\Adr
 * @author  Iulian N. <rapture@iuliann.ro>
 * @license LICENSE MIT
 */
class Responder
{
    /** @var Template */
    protected $template;

    /** @var Response */
    protected $response;

    /** @var Action */
    protected $action;

    /**
     * __construct
     *
     *
     * @param ResponseInterface $response Response object
     * @param Action            $action   Action object
     */
    public function __construct(ResponseInterface $response, Action $action = null)
    {
        $this->response = $response;
        $this->action   = $action;
    }

    /**
     * getResponse
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Hook triggered before rendering
     *
     * @param array $data Data sent by action
     *
     * @return void
     */
    public function preInvoke(array $data)
    {
        $this->template = new Template($this->getTemplateName(), $data);
    }

    /**
     * __invoke
     *
     * @param array $data Data sent by action
     *
     * @return void
     */
    public function __invoke(array $data)
    {
        $stream = new Stream(fopen('php://memory', 'r+'));
        $stream->write($this->template->render());

        $this->response->withBody($stream)->send();
    }

    /**
     * Hook triggered after rendering
     *
     * @param array $data Data sent by action
     *
     * @return void
     */
    public function postInvoke(array $data)
    {
        // postInvoke hook
    }

    /**
     * templateName
     *
     * Example:
     * \Demo\Responder\User\Save => user/save
     *
     * @return string
     */
    public function getTemplateName()
    {
        return strtolower(implode('/', array_slice(explode('\\', get_class($this)), 2)));
    }
}
