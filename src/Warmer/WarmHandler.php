<?php

namespace Dew\Core\Warmer;

use DateTime;
use DateTimeInterface;
use Dew\Core\Contracts\HandlesEvent;
use Dew\Core\Support\Middleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class WarmHandler implements HandlesEvent
{
    /**
     * Handle the given event request.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = (string) $request->getBody();
        $decoded = json_decode($data, associative: true);

        $count = $decoded['warm'] ?? 1;
        $time = new DateTime($decoded['time'] ?? 'now');

        fwrite(STDERR, sprintf("Warming up %s HTTP function container(s).\n", $count));

        Promise\Utils::settle($this->requests($count, $time))->wait();

        return new Response;
    }

    /**
     * Create a Function Compute client.
     */
    protected function client(string $action, string $version): Client
    {
        return new Client([
            'base_uri' => sprintf('https://%s.%s-internal.fc.aliyuncs.com',
                $_SERVER['FC_ACCOUNT_ID'], $_SERVER['FC_REGION']
            ),
            'handler' => $this->createStack($action, $version),
        ]);
    }

    /**
     * Create a handler stack for function invocation.
     */
    protected function createStack(string $action, string $version): HandlerStack
    {
        $stack = HandlerStack::create();
        $stack->push(Middleware::metadata($action, $version));
        $stack->push(Middleware::acs(
            $_SERVER['ALIBABA_CLOUD_ACCESS_KEY_ID'],
            $_SERVER['ALIBABA_CLOUD_ACCESS_KEY_SECRET'],
            $_SERVER['ALIBABA_CLOUD_SECURITY_TOKEN']
        ));

        return $stack;
    }

    /**
     * Create a list of requests for container warm up.
     *
     * @param  positive-int  $count
     * @return \GuzzleHttp\Promise\PromiseInterface[]
     */
    protected function requests(int $count, DateTimeInterface $time): array
    {
        $promises = [];

        $client = $this->client('InvokeFunction', '2023-03-30');
        $endpoint = sprintf('/2023-03-30/functions/%s/invocations',
            $_SERVER['FC_FUNCTION_NAME']
        );

        for ($i = 0; $i < $count; $i++) {
            $promises[] = $client->postAsync($endpoint, [
                'query' => ['qualifier' => 'main'],
                'body' => json_encode([
                    'dewhandler' => 'ping',
                    'time' => $time->getTimestamp(),
                    'index' => $i,
                    'total' => $count,
                ]),
                'headers' => ['x-fc-invocation-type' => 'Async'],
            ]);
        }

        return $promises;
    }
}
