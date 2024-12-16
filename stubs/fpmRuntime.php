<?php

use Dew\Core\EventBridgeValidation;
use Dew\Core\EventHandlerFactory;
use Dew\Core\Fpm\Fpm;
use Dew\Core\Log;
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

Log::debug('Create storage directories.');

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

Log::debug('Cache Laravel configurations.');

$app->make(Kernel::class)->call('config:cache');

/*
|--------------------------------------------------------------------------
| Boot Up the FPM
|--------------------------------------------------------------------------
|
| We rely on FPM to handle the PHP requests under the hood, which is the
| traditional and reliable way. Once the main FPM process has started
| successfully, we are ready to process the next incoming requests.
|
*/

Log::debug('Boot up the FPM.');

$fpm = Fpm::boot();

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

Log::debug('Start listening to the requests.');

$eventBridge = tap(new EventBridgeValidation)
    ->urlUsing(fn (ServerRequestInterface $request): string => $request
        ->getUri()->withScheme('https')
    );

$factory = new EventHandlerFactory(__DIR__.'/handler.php', $eventBridge);

$server = RoadRunner::createFromGlobal(
    (int) ($_SERVER['MAX_REQUESTS'] ?? 250)
);

$server->serve(function (ServerRequestInterface $request, callable $send) use ($fpm, $factory) {
    $response = $factory->make($request)->handle($request);

    $send($response);

    $fpm->ensureRunning();
});
