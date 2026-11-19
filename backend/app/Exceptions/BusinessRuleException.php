<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A business rule refused the action. Rendered as `{ message }` with the
 * status (422 by default) in bootstrap/app.php — the message is user-facing.
 *
 *   throw new BusinessRuleException('You cannot delete your own account.');
 */
class BusinessRuleException extends RuntimeException
{
    public function __construct(string $message, private int $status = 422)
    {
        parent::__construct($message);
    }

    public function status(): int
    {
        return $this->status;
    }
}
