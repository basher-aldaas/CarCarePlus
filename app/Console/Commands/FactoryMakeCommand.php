<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FactoryMakeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:factory {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Factory class';

    /**
     * Execute the console command.
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/factory.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\Database\\Factories\\';
    }

    public function handle()
    {
        $this->info('Factory created');
    }
}
