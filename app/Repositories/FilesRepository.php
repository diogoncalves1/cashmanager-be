<?php
namespace App\Repositories;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

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

    public function uploadImagesFromHtml($html, $path, $filename): string
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $images = $dom->getElementsByTagName('img');

        foreach ($images as $img) {
            $src = $img->getAttribute('src');

            if (preg_match('/^data:image\/(\w+);base64,/', $src, $type)) {
                $data = substr($src, strpos($src, ',') + 1);
                $data = base64_decode($data);

                $extension = $type[1];
                $filename  = $path . $filename . '.' . $extension;

                Storage::put($filename, $data);

                $img->setAttribute('src', Storage::url($filename));
            }
        }

        return $dom->saveHTML();
    }
}
