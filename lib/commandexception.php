<?php


namespace Package\Manager;

use Exception;
use Throwable;

class CommandException extends Exception
{
    /**
     * @var string|null
     */
    private $command;

    public function __construct(string $message = "", string $command = "", $code = 0, Throwable $previous = null)
    {
        $this->command = $command;
        parent::__construct($message, $code, $previous);
    }

    public function getCommand(): string
    {
        return $this->command;
    }
}
