<?php

use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Context\Context;
use OpenTelemetry\API\Globals;
use OpenTelemetry\SemConv\TraceAttributes;
use OpenTelemetry\SDK\Sdk;

require 'vendor/autoload.php';

class DemoClass
{
    public function run(): void
    {
        echo 'Connecting to Redis...<br>';

        $redis = new Redis();
        $redis->connect('redis');

        echo 'Successfully connected to Redis<br>';
        echo 'Calling redis->set<br>';

        $redis->set('foo', 'bar');
        echo 'Successfully called redis->set<br>';

        echo 'Calling redis->get<br>';
        $value = $redis->get('foo');
        echo "Value is $value <br>";
    }
}

/* The auto-instrumentation code */
/*
OpenTelemetry\Instrumentation\hook(
    class: DemoClass::class,
    function: 'run',
    pre: static function (DemoClass $demo, array $params, string $class, string $function, ?string $filename, ?int $lineno) {
        static $instrumentation;
        $instrumentation ??= new CachedInstrumentation('example');
        $span = $instrumentation->tracer()->spanBuilder('democlass-run')->startSpan();
        Context::storage()->attach($span->storeInContext(Context::getCurrent()));
    },
    post: static function (DemoClass $demo, array $params, $returnValue, ?Throwable $exception) {
        $scope = Context::storage()->scope();
        $scope->detach();
        $span = Span::fromContext($scope->context());
        if ($exception) {
            $span->recordException($exception);
            $span->setStatus(StatusCode::STATUS_ERROR);
        }
        $span->end();
    }
);
*/

/* Run the instrumented code, which will generate a trace */
$demo = new DemoClass();
$demo->run();
