<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CommandHelper
{
    /** @var Command */
    private $command;

    public function setCommand(Command $command)
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @return InputArgument[]
     */
    public function getInputArguments()
    {
        return $this->command->getDefinition()->getArguments();
    }

    /**
     * @return InputOption[]
     */
    public function getOptions()
    {
        return $this->command->getDefinition()->getOptions();
    }

    /**
     * @return string[]
     */
    public function getInputArgumentNames()
    {
        return array_values(array_map(function(InputArgument $input) {
            return $input->getName();
        }, $this->getInputArguments()));
    }
}
