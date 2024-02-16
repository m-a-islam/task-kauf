<?php

namespace helpers;

class CommandHelper
{
    public int $exitCode;
    public string $message;

    public function __construct(int $exitCode, string $message)
    {
        $this->exitCode = $exitCode;
        $this->message = $message;
    }
}