<?php

namespace Dew\Core;

use Closure;
use Dew\Core\Contracts\ValidatesEventBridge;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class EventBridgeValidation implements ValidatesEventBridge
{
    /**
     * The custom URL resolution handler.
     *
     * @var \Closure(\Psr\Http\Message\ServerRequestInterface): string
     */
    protected ?Closure $resolvesUrl = null;

    /**
     * The resolved certificates.
     *
     * @var string[]
     */
    protected array $resolved = [];

    /**
     * Create a new Event Bridge validation instance.
     */
    public function __construct(
        protected ?ClientInterface $client = null
    ) {
        $this->client = $this->client ?? new Client([
            'timeout' => 3.0,
        ]);
    }

    /**
     * Determine if the request contains valid Event Bridge invocation payload.
     */
    public function valid(ServerRequestInterface $request): bool
    {
        if (! $this->hasHeaders($request)) {
            return false;
        }

        if ($this->expired($request)) {
            return false;
        }

        if (! $this->trustedCertificateUrl($request)) {
            return false;
        }

        if (! $this->matchedSignature($request)) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the request contains all necessary headers.
     */
    public function hasHeaders(ServerRequestInterface $request): bool
    {
        return $request->hasHeader('x-eventbridge-signature')
            && $request->hasHeader('x-eventbridge-signature-method')
            && $request->hasHeader('x-eventbridge-signature-version')
            && $request->hasHeader('x-eventbridge-signature-timestamp')
            && $request->hasHeader('x-eventbridge-signature-token')
            && $request->hasHeader('x-eventbridge-signature-secret')
            && $request->hasHeader('x-eventbridge-signature-url');
    }

    /**
     * Determine if the request has expired.
     */
    public function expired(ServerRequestInterface $request): bool
    {
        $now = time();
        $sent = (int) $request->getHeaderLine('x-eventbridge-signature-timestamp');

        return $now - $sent >= 60;
    }

    /**
     * Determine if the certificate URL can be trusted.
     */
    public function trustedCertificateUrl(ServerRequestInterface $request): bool
    {
        $parsed = parse_url($request->getHeaderLine('x-eventbridge-signature-url'));

        // Requires encrypted communications.
        if ($parsed['scheme'] !== 'https') {
            return false;
        }

        // Requires a specific URL format.
        // @link https://www.alibabacloud.com/help/en/eventbridge/user-guide/signature-algorithm
        if (! str_ends_with($parsed['host'], '-eventbridge.oss-accelerate.aliyuncs.com')) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the request signature matches the local calculation.
     */
    public function matchedSignature(ServerRequestInterface $request): bool
    {
        return $this->signature($request) === $request->getHeaderLine('x-eventbridge-signature');
    }

    /**
     * Set custom URL resolution algorithm.
     *
     * @param  callable(\Psr\Http\Message\ServerRequestInterface): string  $callback
     */
    public function urlUsing(callable $callback): self
    {
        $this->resolvesUrl = $callback;

        return $this;
    }

    /**
     * Calculate the signature for the request.
     */
    public function signature(ServerRequestInterface $request): string
    {
        // The order of the headers is fixed.
        $headers = collect([
            'x-eventbridge-signature-timestamp',
            'x-eventbridge-signature-method',
            'x-eventbridge-signature-version',
            'x-eventbridge-signature-url',
            'x-eventbridge-signature-token',
        ])->map(fn ($name) => sprintf('%s: %s',
            $name, $request->getHeaderLine($name)
        ));

        $data = collect([
            $this->resolvesUrl
                ? call_user_func_array($this->resolvesUrl, [$request])
                : (string) $request->getUri(),
            $headers->join("\n"),
            (string) $request->getBody(),
        ])->join("\n");

        return base64_encode(
            hash_hmac('sha1', $data."\n", $this->getSecret($request), binary: true)
        );
    }

    /**
     * Decrypt the key used to encrypt the signature.
     */
    public function getSecret(ServerRequestInterface $request): string
    {
        $data = base64_decode($request->getHeaderLine('x-eventbridge-signature-secret'));

        if (! openssl_public_decrypt($data, $decrypted, $this->getCertificate($request))) {
            throw new RuntimeException('Failed to decrypt the secret.');
        }

        return $decrypted;
    }

    /**
     * Get the public key to decrypt the secret.
     */
    public function getCertificate(ServerRequestInterface $request): string
    {
        $url = $request->getHeaderLine('x-eventbridge-signature-url');

        // Each time we retrieve the certificate, we cache the content to
        // speed up the process. If the same URL has been resolved, we
        // can return it quickly without having to send the request.
        if (isset($this->resolved[$url])) {
            return $this->resolved[$url];
        }

        $certificate = (string) $this->client
            ->sendRequest(new Request('GET', $url))
            ->getBody();

        return $this->resolved[$url] = $certificate;
    }
}
