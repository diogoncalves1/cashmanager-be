<?php
namespace App\Repositories;

use Illuminate\Support\Facades\File;

abstract class FilesRepository extends SuperRepository
{
    public function imageUpload($path, $file, $filename)
    {
        if (! File::exists($path)) {
            File::makeDirectory($path, 0777, true);
        }

        $filename = $filename . '.' . $file->getClientOriginalExtension();

        $file->move(public_path($path), $filename);

        return $filename;
    }

    public function imageDelete($path)
    {
        $fullPath = public_path($path);

        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}
