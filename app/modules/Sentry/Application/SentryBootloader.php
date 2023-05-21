<?php

declare(strict_types=1);

namespace Modules\Sentry\Application;

use App\Application\Service\HttpHandler\HandlerRegistryInterface;
use Modules\Sentry\EventHandler;
use Modules\Sentry\Interfaces\Http\Handler\EventHandler as HttpEventHandler;
use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\Bootloader;

final class SentryBootloader extends Bootloader
{
    protected const SINGLETONS = [
        EventHandlerInterface::class => [self::class, 'eventHandler'],
    ];

    public function boot(
        HandlerRegistryInterface $registry,
        HttpEventHandler $handler
    ): void {
        $registry->register($handler);
    }

    public function eventHandler(ContainerInterface $container): EventHandlerInterface
    {
        return new EventHandler($container, []);
    }
}
