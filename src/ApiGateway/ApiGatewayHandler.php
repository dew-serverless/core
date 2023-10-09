<?php

namespace Dew\Core\ApiGateway;

use Dew\Core\Contracts\EventHandler;
use Dew\Core\Contracts\ServesFastCgiRequest;
use Dew\Core\FpmHandler;
use Dew\Core\Server;
use Psr\Http\Message\ResponseInterface;

class ApiGatewayHandler implements EventHandler
{
    public function __construct(
        public Server $server,
        public ?ServesFastCgiRequest $fpm = null,
        public ?FastCgiRequestFactory $factory = null
    ) {
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
}
