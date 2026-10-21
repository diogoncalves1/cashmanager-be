<?php

namespace Tests\Unit;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class TranslationCatalogueTest extends TestCase
{
    private function catalogue(string $locale): array
    {
        $messages = [];
        foreach (glob(base_path("Modules/*/Resources/lang/{$locale}/*.php")) as $file) {
            $module = strtolower(basename(dirname($file, 4)));
            $group = basename($file, '.php');
            $messages += $this->flatten(require $file, "{$module}::{$group}.");
        }

        return $messages;
    }

    private function flatten(array $messages, string $prefix): array
    {
        $result = [];
        foreach ($messages as $key => $message) {
            if (is_array($message)) {
                $result += $this->flatten($message, $prefix.$key.'.');
            } else {
                $result[$prefix.$key] = $message;
            }
        }

        return $result;
    }

    public function test_module_catalogues_have_matching_keys_and_arguments(): void
    {
        $portuguese = $this->catalogue('pt');
        $english = $this->catalogue('en');
        $ptKeys = array_keys($portuguese);
        $enKeys = array_keys($english);
        sort($ptKeys);
        sort($enKeys);
        $this->assertSame($ptKeys, $enKeys);

        foreach ($portuguese as $key => $message) {
            $this->assertNotSame('', trim($message), $key);
            $this->assertNotSame('', trim($english[$key]), $key);
            preg_match_all('/:([a-zA-Z_][a-zA-Z0-9_]*)/', $message, $ptArguments);
            preg_match_all('/:([a-zA-Z_][a-zA-Z0-9_]*)/', $english[$key], $enArguments);
            $ptNames = array_unique($ptArguments[1]);
            $enNames = array_unique($enArguments[1]);
            sort($ptNames);
            sort($enNames);
            $this->assertSame($ptNames, $enNames, $key);
        }
    }

    public function test_literal_module_references_and_dynamic_prefixes_exist(): void
    {
        $catalogue = $this->catalogue('pt');
        foreach (['app', 'Modules'] as $directory) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path($directory)));
            foreach ($files as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php' || str_contains($file->getPathname(), '/Resources/lang/')) {
                    continue;
                }
                preg_match_all('/(?:__|trans|Lang::get)\(\s*([\'"])([a-z]+::[a-zA-Z0-9_.-]+)\1\s*([,).])/', file_get_contents($file->getPathname()), $references, PREG_SET_ORDER);
                foreach ($references as $reference) {
                    $key = $reference[2];
                    if ($reference[3] === '.') {
                        $this->assertNotEmpty(array_filter(array_keys($catalogue), fn ($candidate) => str_starts_with($candidate, $key)), "Missing prefix {$key}: {$file->getPathname()}");
                    } else {
                        $this->assertArrayHasKey($key, $catalogue, $file->getPathname());
                    }
                }
            }
        }
    }

    public function test_module_catalogues_are_available_in_the_runtime_translator(): void
    {
        $translator = app('translator');
        foreach (['pt', 'en'] as $locale) {
            foreach ($this->catalogue($locale) as $key => $message) {
                $this->assertTrue($translator->hasForLocale($key, $locale), "{$locale}: {$key}");
            }
        }
    }
}
