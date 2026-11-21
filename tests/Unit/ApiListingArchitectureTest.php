<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ApiListingArchitectureTest extends TestCase
{
    public function test_api_controllers_do_not_depend_on_datatables(): void
    {
        foreach (glob(__DIR__.'/../../Modules/*/Http/Controllers/Api') as $directory) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }
                $this->assertDoesNotMatchRegularExpression('/DataTable|datatables\s*\(/', file_get_contents($file->getPathname()), $file->getPathname());
            }
        }
    }
}
