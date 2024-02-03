<?php

namespace Dew\Core\Support;

use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;

class Signature
{
    /**
     * Create a new ACS siganture instance.
     */
    public function __construct(
        public string $key
    ) {
        //
    }

    /**
     * The signature algorithmn.
     */
    public function name(): string
    {
        return 'ACS3-HMAC-SHA256';
    }

    /**
     * The hashing algorithmn.
     */
    public function algorithmn(): string
    {
        return 'sha256';
    }

    /**
     * The canonical request headers.
     *
     * @return string[]
     */
    public function headers(RequestInterface $request): array
    {
        return collect($request->getHeaders())
            ->keys()
            ->map(fn (string $header) => strtolower($header))
            ->sort()
            ->all();
    }

    /**
     * Calculate the signature for the request.
     */
    public function sign(RequestInterface $request): string
    {
        $data = implode("\n", [
            $this->name(),
            hash($this->algorithmn(), $this->canonical($request)),
        ]);

        return hash_hmac($this->algorithmn(), $data, $this->key);
    }

    /**
     * The canonical request.
     */
    public function canonical(RequestInterface $request): string
    {
        $headers = $this->headers($request);

        return implode("\n", [
            $request->getMethod(),
            $request->getUri()->getPath(),
            $this->query($request),
            collect($headers)->map(fn (string $name) => sprintf("%s:%s\n",
                $name, $request->getHeaderLine($name)
            ))->join(''),
            implode(';', $headers),
            $this->hash($request),
        ]);
    }

    /**
     * The canonical request query string.
     */
    public function query(RequestInterface $request): string
    {
        return collect(Psr7\Query::parse($request->getUri()->getQuery()))
            ->sortKeys()
            ->map(fn (string $value, string $key) => sprintf('%s=%s',
                rawurlencode($key), rawurlencode($value)
            ))
            ->values()
            ->join('&');
    }

    /**
     * The request body digest.
     */
    public function hash(RequestInterface $request): string
    {
        return hash($this->algorithmn(), (string) $request->getBody());
    }
}
