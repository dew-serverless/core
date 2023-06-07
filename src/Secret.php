<?php

namespace Dew\Core;

use AlibabaCloud\SDK\Oos\V20190601\Models\GetParametersByPathRequest;
use AlibabaCloud\SDK\Oos\V20190601\Oos;

class Secret
{
    public static function loadFromOos(?Oos $client = null): void
    {
        $fc = FunctionCompute::createFromEnvironment();
        $client = $client ?: static::createOosFrom($fc);

        $prefix = sprintf('%s/', $fc->serviceName());
        $offset = strlen($prefix);

        $response = $client->getParametersByPath(new GetParametersByPathRequest([
            'path' => $prefix,
        ]));

        foreach ($response->body->parameters as $parameter) {
            $name = substr($parameter->name, $offset);

            static::setEnv($name, $parameter->value);
        }
    }

    protected static function setEnv(string $name, string $value): void
    {
        putenv(sprintf('%s=%s', $name, $value));

        $_ENV[$name] = $_SERVER[$name] = $value;
    }

    protected static function createOosFrom(FunctionCompute $fc): Oos
    {
        $config = $fc->newConfig();
        $config->endpoint = sprintf('oos.%s.aliyuncs.com', $fc->region());

        return new Oos($config);
    }
}