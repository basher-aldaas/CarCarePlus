<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class ServiceMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Service class';

    /**
     * نوع الملف الذي يظهر في الـ Terminal عند النجاح
     */
    protected $type = 'Service';

    /**
     * Execute the console command.
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/service.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\Services';
    }
}
