<?php

namespace Dew\Core\Support;

use GuzzleHttp\Middleware as GuzzleMiddleware;
use Psr\Http\Message\RequestInterface;

class Middleware
{
    /**
     * Configure ACS request metadata.
     */
    public static function metadata(string $action, string $version): callable
    {
        return GuzzleMiddleware::mapRequest(
            fn (RequestInterface $request) => $request
                ->withHeader('x-acs-date', date('Y-m-d\TH:i:s\Z'))
                ->withHeader('x-acs-signature-nonce', bin2hex(random_bytes(16)))
                ->withHeader('x-acs-action', $action)
                ->withHeader('x-acs-version', $version)
        );
    }

    /**
     * Sign the ACS request.
     */
    public static function acs(string $key, string $secret, ?string $token = null): callable
    {
        return GuzzleMiddleware::mapRequest(
            function (RequestInterface $request) use ($key, $secret, $token): RequestInterface {
                $signature = new Signature($secret);

                if (is_string($token)) {
                    $request = $request
                        ->withHeader('x-acs-accesskey-id', $key)
                        ->withHeader('x-acs-security-token', $token);
                }

                $request = $request->withHeader(
                    'x-acs-content-sha256', $signature->hash($request)
                );

                return $request->withHeader('Authorization', sprintf(
                    '%s Credential=%s,SignedHeaders=%s,Signature=%s',
                    $signature->name(), $key,
                    implode(';', $signature->headers($request)),
                    $signature->sign($request)
                ));
            }
        );
    }
}
