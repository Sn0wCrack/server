<?php

declare(strict_types=1);

namespace App\Application\Service\HttpHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HandlerInterface
{
    public function priority(): int;
    /**
     * @param \Closure<ServerRequestInterface> $next
     */
    public function handle(ServerRequestInterface $request, \Closure $next): ResponseInterface;
}
