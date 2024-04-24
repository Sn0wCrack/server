<?php

declare(strict_types=1);

namespace Modules\Webhooks\Application;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Modules\Webhooks\Domain\DeliveryFactoryInterface;
use Modules\Webhooks\Domain\DeliveryRepositoryInterface;
use Modules\Webhooks\Domain\WebhookFactoryInterface;
use Modules\Webhooks\Domain\WebhookLocatorInterface;
use Modules\Webhooks\Domain\WebhookRegistryInterface;
use Modules\Webhooks\Domain\WebhookRepositoryInterface;
use Modules\Webhooks\Domain\WebhookServiceInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Cache\CacheStorageProviderInterface;
use Spiral\Core\FactoryInterface;

final class WebhooksBootloader extends Bootloader
{
    private const CACHE_ALIAS = 'webhooks';

    public function defineSingletons(): array
    {
        return [
            ClientInterface::class => static fn(
                EnvironmentInterface $env,
            ): ClientInterface => new Client([
                'timeout' => 5,
                'connect_timeout' => 5,
                'headers' => [
                    'User-Agent' => $env->get('WEBHOOK_USER_AGENT', 'Buggregator\Webhooks'),
                    'Content-Type' => 'application/json',
                ],
            ]),

            WebhookFactoryInterface::class => WebhookFactory::class,

            WebhookServiceInterface::class => WebhookService::class,

            InMemoryWebhookRepository::class => static fn(
                FactoryInterface $factory,
                CacheStorageProviderInterface $storageProvider,
            ): InMemoryWebhookRepository => $factory->make(
                InMemoryWebhookRepository::class,
                [
                    'cache' => $storageProvider->storage(self::CACHE_ALIAS),
                ],
            ),
            WebhookRepositoryInterface::class => InMemoryWebhookRepository::class,
            WebhookRegistryInterface::class => InMemoryWebhookRepository::class,

            YamlFileWebhookLocator::class => static fn(
                FactoryInterface $factory,
                DirectoriesInterface $dirs,
            ): YamlFileWebhookLocator => $factory->make(
                YamlFileWebhookLocator::class,
                [
                    'directory' => $dirs->get('runtime') . '/webhooks',
                ],
            ),

            WebhookLocatorInterface::class => static function (
                YamlFileWebhookLocator $locator,
            ): WebhookLocatorInterface {
                return new CompositeWebhookLocator([
                    $locator,
                ]);
            },

            DeliveryRepositoryInterface::class => static fn(
                FactoryInterface $factory,
                CacheStorageProviderInterface $storageProvider,
            ): DeliveryRepositoryInterface => $factory->make(
                InMemoryDeliveryRepository::class,
                [
                    'cache' => $storageProvider->storage(self::CACHE_ALIAS),
                ],
            ),

            DeliveryFactoryInterface::class => DeliveryFactory::class,
        ];
    }

    public function boot(
        WebhookRegistryInterface $registry,
        WebhookLocatorInterface $locator,
    ): void {
        foreach ($locator->findAll() as $webhook) {
            $registry->register($webhook);
        }
    }
}
