<?php


namespace Beta\Composer;


use Beta\Composer\Interfaces\ResultOperationInterface;

class ResultOperation implements ResultOperationInterface
{
    /**
     * @var bool
     */
    private $success;
    /**
     * @var string
     */
    private $message;

    public function __construct(bool $success, string $message = '')
    {
        $this->success = $success;
        $this->message = $message;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getErrorMessage(): string
    {
        return $this->message;
    }

    public function throw()
    {
        throw new \Exception($this->message);
    }
}
