<?php

declare(strict_types=1);
/**
 * This file is part of DTM-PHP.
 *
 * @license  https://github.com/dtm-php/dtm-sample/blob/master/LICENSE
 */
namespace App\Middleware;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ShowRequestMiddleware implements MiddlewareInterface
{
    #[Inject]
    protected StdoutLoggerInterface $logger;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $this->logger->info(sprintf('[%d] %s %s', $response->getStatusCode(), $request->getMethod(), $request->getUri()->getPath()));
        return $response;
    }
}
