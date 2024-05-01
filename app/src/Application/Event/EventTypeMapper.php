<?php

declare(strict_types=1);

namespace App\Application\Event;

use Spiral\Core\Attribute\Singleton;

#[Singleton]
final class EventTypeMapper implements EventTypeMapperInterface, EventTypeRegistryInterface
{
    /** @var array<string, EventTypeMapperInterface> */
    private array $mappers = [];

    public function toPreview(string $type, array|\JsonSerializable $payload): array|\JsonSerializable
    {
        if (!isset($this->mappers[$type])) {
            return $payload;
        }

        return $this->mappers[$type]->toPreview($type, $payload);
    }

    public function toFull(string $type, array|\JsonSerializable $payload): array|\JsonSerializable
    {
        if (!isset($this->mappers[$type])) {
            return $payload;
        }

        return $this->mappers[$type]->toFull($type, $payload);
    }

    public function register(string $type, EventTypeMapperInterface $mapper): void
    {
        if (isset($this->mappers[$type])) {
            throw new \RuntimeException("Mapper for type [$type] already registered");
        }

        $this->mappers[$type] = $mapper;
    }
}
