<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class RepositoryMakeCommand extends GeneratorCommand
{
    /**
     * اسم الأمر الذي ستكتبه في السطر البرمجي
     */
    protected $signature = 'make:repo {name}';

    /**
     * وصف الأمر
     */
    protected $description = 'Create a new Repository class';

    /**
     * نوع الملف (يظهر في السطر البرمجي عند النجاح)
     */
    protected $type = 'Repository';

    /**
     * تحديد مكان قالب الـ Stub
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/repository.stub';
    }

    /**
     * تحديد المجلد الافتراضي الذي ستوضع فيه المستودعات
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Repositories';
    }
}
