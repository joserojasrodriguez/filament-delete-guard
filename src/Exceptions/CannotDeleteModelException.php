<?php

declare(strict_types=1);

namespace Joserojasrodriguez\FilamentDeleteGuard\Exceptions;

use RuntimeException;

final class CannotDeleteModelException extends RuntimeException
{
    public static function because(string $message): self
    {
        return new self($message);
    }
}