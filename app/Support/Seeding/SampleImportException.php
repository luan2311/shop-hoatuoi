<?php

namespace App\Support\Seeding;

use RuntimeException;

final class SampleImportException extends RuntimeException
{
    public function __construct(string $message, public readonly array $summary)
    {
        // Không gắn exception MongoDB gốc: thông điệp kết nối có thể chứa credentials.
        parent::__construct($message);
    }
}
