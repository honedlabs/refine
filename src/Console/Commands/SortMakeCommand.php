<?php

declare(strict_types=1);

namespace Honed\Refine\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:sort')]
class SortMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:sort';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $description = 'Create a new sort class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Sort';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/honed.sort.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(\trim($stub, '/')))
            ? $customPath
            : __DIR__.'/../../..'.$stub;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Refiners\Sorts';
    }

    /**
     * @return array<int,array<int,mixed>>
     */
    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the sort already exists'],
        ];
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array<string,mixed>
     */
    protected function promptForMissingArgumentsUsing()
    {
        return [
            'name' => [
                'What should the '.strtolower($this->type).' be named?',
                'E.g. DateSort',
            ],
        ];
    }
}
