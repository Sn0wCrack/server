<?php

declare(strict_types=1);

namespace Modules\Ray\Application\Handlers;

use App\Application\Commands\FindEventByUuid;
use App\Application\Domain\ValueObjects\Uuid;
use App\Application\Exception\EntityNotFoundException;
use Modules\Sentry\Application\EventHandlerInterface;
use Spiral\Cqrs\QueryBusInterface;

final class MergeEventsHandler implements EventHandlerInterface
{
    public function __construct(
        private readonly QueryBusInterface $bus,
    ) {
    }

    public function handle(array $event): array
    {
        try {
            /** @var \Modules\Events\Domain\Event $storedEvent */
            $storedEvent = $this->bus->ask(
                new FindEventByUuid(
                    Uuid::fromString($event['uuid'])
                )
            );
            $event['payloads'] = [
                ...($storedEvent->getPayload()->jsonSerialize()['payloads'] ?? []),
                ...($event['payloads'] ?? [])
            ];


        } catch (EntityNotFoundException $e) {
        }

        return $event;
    }
}
