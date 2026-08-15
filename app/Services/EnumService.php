<?php

namespace App\Services;

use Illuminate\Support\Str;
use ReflectionEnum;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class EnumService
{
    /**
     * Directory that holds every application enum.
     */
    protected string $enumPath;

    /**
     * Root namespace mapped to {@see $enumPath}.
     */
    protected string $enumNamespace = 'App\\Enums';

    public function __construct()
    {
        $this->enumPath = app_path('Enums');
    }

    /**
     * Build a map of every backed enum keyed by its snake_case name.
     *
     * Each entry is a list of { value, label } pairs so the frontend can
     * populate dropdowns without hard-coding option lists.
     *
     * @param string|null $locale Locale used for the human-readable labels.
     * @return array<string, array<int, array{value: int|string, label: string}>>
     */
    public function all(?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $enums = [];

        foreach ($this->enumClasses() as $class) {
            $reflection = new ReflectionEnum($class);

            // Only backed enums expose a usable value for the frontend.
            if (! $reflection->isBacked()) {
                continue;
            }

            $key = Str::snake($reflection->getShortName());

            $enums[$key] = array_map(
                fn ($case) => [
                    'value' => $case->value,
                    'label' => method_exists($case, 'label')
                        ? $case->label($locale)
                        : $case->name,
                ],
                $class::cases()
            );
        }

        ksort($enums);

        return $enums;
    }

    /**
     * Resolve every enum class name under the Enums directory.
     *
     * @return array<int, class-string>
     */
    protected function enumClasses(): array
    {
        $classes = [];

        $finder = (new Finder())
            ->files()
            ->in($this->enumPath)
            ->name('*.php');

        foreach ($finder as $file) {
            $class = $this->classFromFile($file);

            if (enum_exists($class)) {
                $classes[] = $class;
            }
        }

        return $classes;
    }

    /**
     * Map a file inside the Enums directory to its fully-qualified class name.
     */
    protected function classFromFile(SplFileInfo $file): string
    {
        $relative = Str::of($file->getRealPath())
            ->after($this->enumPath . DIRECTORY_SEPARATOR)
            ->replace(['/', '\\'], '\\')
            ->replaceLast('.php', '');

        return $this->enumNamespace . '\\' . $relative;
    }
}