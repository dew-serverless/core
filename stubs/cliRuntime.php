<?php

use Dew\Core\CliHandlerFactory;
use Dew\Core\EventBridgeValidation;
use Dew\Core\RoadRunner;
use Dew\Core\StorageDirectories;
use Illuminate\Contracts\Console\Kernel;
use Psr\Http\Message\ServerRequestInterface;

$app = require __DIR__.'/bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Create Storage Directories
|--------------------------------------------------------------------------
|
| It is not recommended to store critical files on the ephemeral Function
| Compute container. Creating temporary storage directories allows the
| framework to internally cache data, enhancing overall performance.
|
*/

fwrite(STDERR, 'Create storage directories.'.PHP_EOL);

StorageDirectories::create();

$app->useStoragePath(StorageDirectories::PATH);

/*
|--------------------------------------------------------------------------
| Cache Laravel Configurations
|--------------------------------------------------------------------------
|
| There are some dynamic environment variables on the Function Compute
| runtime, so we couldn't cache them when we were in the deployment
| stage; since the storage path has been set, it's time to do so.
|
*/

fwrite(STDERR, 'Cache Laravel configurations.'.PHP_EOL);

$app->make(Kernel::class)->call('config:cache');

/*
|--------------------------------------------------------------------------
| Handle Incoming Requests
|--------------------------------------------------------------------------
|
| At this stage we start the server and listen for incoming requests. And
| to mitigate the risk of memory leaks, we respawn the worker after it
| has fulfilled the maximum number of requests, so we're good to go.
|
*/

fwrite(STDERR, 'Start listening to the requests.'.PHP_EOL);

$eventBridge = tap(new EventBridgeValidation)
    ->urlUsing(fn (ServerRequestInterface $request): string => $request
        ->getUri()->withScheme('https')
    );

$factory = new CliHandlerFactory($eventBridge);

$server = RoadRunner::createFromGlobal(
    (int) ($_ENV['MAX_REQUESTS'] ?? 250)
);

$server->serve(function (ServerRequestInterface $request, callable $send) use ($factory) {
    $response = $factory->make($request)->handle($request);

    $send($response);
});
