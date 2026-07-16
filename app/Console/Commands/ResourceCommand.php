<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class ResourceCommand extends GeneratorCommand
{
    /**
     * The console command name.
     */
    protected $name = 'make:resource {name}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new Resource class';

    /**
     * The type of class being generated.
     */
    protected $type = 'Resource';

    protected function getStub()
    {
        return base_path('Stubs/resource.stub');
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Resources';
    }
}
