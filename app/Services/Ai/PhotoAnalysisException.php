<?php

namespace App\Services\Ai;

use Exception;

class PhotoAnalysisException extends Exception
{
    public function __construct(string $message, public readonly ?array $rawResponse = null)
    {
        parent::__construct($message);
    }
}
