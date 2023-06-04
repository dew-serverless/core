<?php

namespace Dew\Core;

use Dew\Core\Exceptions\RequestHandlerException;
use Dew\Core\Handlers\CliHandler;
use Psr\Http\Message\ServerRequestInterface;

class RequestHandler
{
    public static function make(ServerRequestInterface $request)
    {
        if (! static::isValidRequest($request)) {
            throw new RequestHandlerException(sprintf('Caught unexpected request [%s] %s.',
                $request->getMethod(),
                $request->getUri()->getPath()
            ));
        }

        $body = $request->getBody()->getContents();
        $payload = json_decode($body, associative: true);

        if (CliHandler::canHandle($payload)) {
            return new CliHandler($payload);
        }

        throw new RequestHandlerException(sprintf(
            'Failed to handle the given payload [%s].', $body
        ));
    }

    /**
     * Determine whether the request is from Function Compute.
     *
     * @see https://help.aliyun.com/document_detail/191342.html
     * @param  ServerRequestInterface  $request
     * @return bool
     */
    public static function isValidRequest(ServerRequestInterface $request): bool
    {
        return $request->getUri()->getPath() === '/invoke'
            && $request->getMethod() === 'POST';
    }
}