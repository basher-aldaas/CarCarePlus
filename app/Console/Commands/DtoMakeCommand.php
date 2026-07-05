<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class DtoMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:dto {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crate a new DTO class';

    /**
     * نوع الملف الذي يظهر في الـ Terminal عند النجاح
     */
    protected $type = 'DTO';

    /**
     * Execute the console command.
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/dto.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\DTOs';
    }

}
