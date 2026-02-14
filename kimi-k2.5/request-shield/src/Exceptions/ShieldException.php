<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Exceptions;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Throwable;

final class ShieldException extends Exception
{
    private ?Request $request;

    public function __construct(
        string $message = 'Access denied.',
        int $code = Response::HTTP_FORBIDDEN,
        ?Throwable $previous = null,
        ?Request $request = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->request = $request;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function render(): Response
    {
        $content = $this->message;

        return response($content, $this->code);
    }
}