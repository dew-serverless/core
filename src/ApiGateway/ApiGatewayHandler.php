<?php

namespace Dew\Core\ApiGateway;

use Dew\Core\Contracts\ServesFastCgiRequest;
use Dew\Core\EventHandler;
use Dew\Core\FpmHandler;
use Dew\Core\Server;
use Psr\Http\Message\ResponseInterface;

class ApiGatewayHandler extends EventHandler
{
    public function __construct(
        Server $server,
        protected ?ServesFastCgiRequest $fpm = null,
        protected ?FastCgiRequestFactory $factory = null
    ) {
        parent::__construct($server);

        $this->fpm = $this->fpm ?: new FpmHandler;
        $this->fpm->start();

        $this->factory = $this->factory ?: new FastCgiRequestFactory(
            'handler.php', $this->server->context()->codePath()
        );
    }

    /**
     * Handle API Gateway event.
     *
     * @param  \Dew\Core\ApiGateway\ApiGatewayEvent  $event
     */
    public function handle($event): ResponseInterface
    {
        $request = $this->factory->make($event);
        $response = new Response($this->fpm->handle($request));

        return $response->toApiGatewayFormat();
    }

    /**
     * The underlying FPM.
     */
    public function fpm(): ServesFastCgiRequest
    {
        return $this->fpm;
    }

    /**
     * The underlying FastCGI request factory.
     */
    public function fastcgi(): FastCgiRequestFactory
    {
        $this->factory;
    }
}
