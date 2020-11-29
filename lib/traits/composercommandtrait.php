<?php


namespace Package\Manager\Traits;


use Package\Manager\CommandException;

trait ComposerCommandTrait
{
    abstract protected function getComposerPath(): string;
    abstract protected function getWorkDir(): string;

    /**
     * @param string $command
     * @param string|null $errorMessage
     * @throws CommandException
     */
    private function runCommand(string $command, string $errorMessage = null)
    {
        $output = null;
        $result = null;
        exec($command, $output, $result);
        if ($result > 0) {
            $errorMessage = !empty($output) ? $output : ($errorMessage ?? "Error exec: {$command}");
            if (is_array($errorMessage)) {
                $errorMessage = implode(', ', $errorMessage);
            }
            throw new CommandException($errorMessage, $command);
        }
    }

    /**
     * @param string $package
     * @throws CommandException
     */
    protected function requirePackage(string $package)
    {
        $composerPath = $this->getComposerPath();
        $workDir = $this->getWorkDir();
        $this->runCommand(
            "{$composerPath} require -d {$workDir} {$package}",
            "Не удалось установить пакет: {$package}"
        );
    }

    /**
     * @param string $package
     * @throws CommandException
     */
    protected function removePackage(string $package)
    {
        $composerPath = $this->getComposerPath();
        $workDir = $this->getWorkDir();
        $this->runCommand(
            "{$composerPath} remove -d {$workDir} {$package}",
            "Не удалось удалить пакет: {$package}"
        );
    }

    /**
     * @throws CommandException
     */
    protected function dumpAutoload()
    {
        $composerPath = $this->getComposerPath();
        $workDir = $this->getWorkDir();
        $this->runCommand(
            "{$composerPath} dumpautoload -d {$workDir}",
            "Не удалось обновить автозагрузчик"
        );
    }
}
