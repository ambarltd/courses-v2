<?php

declare(strict_types=1);

use Galeas\Api\Kernel;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/../vendor/autoload.php';

$environment = getenv('API_ENVIRONMENT_TYPE');

if (!$environment) {
    throw new \InvalidArgumentException('Undefined environment type');
}

$phpDebug = (
    'environment_staging_debug' === $environment ||
    'environment_test' === $environment
);

if ($phpDebug) {
    umask(0000);

    Debug::enable();
}
// Request::setTrustedProxies(['0.0.0.0/0'], Request::HEADER_FORWARDED);

$kernel = new Kernel($environment, false);

# cache has to be manually reset
# trying to change this per environment adds a lot of complexity
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
