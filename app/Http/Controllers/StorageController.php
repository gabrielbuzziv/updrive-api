<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class StorageController extends Controller
{

    protected $path = 'app';

    /**
     * Check if file exist in the storage and
     * load it.
     *
     * @param $directory
     * @param $file
     * @return mixed
     */
    public function load($directory, $file)
    {
        return $file;

        if ($directory == 'updrive')
            abort(404);

        if ($path = $this->fileExists($directory, $file)) {
            $file = File::get($path);
            $type = File::mimeType($path);

            return Response::make($file, 200)->header('Content-Type', $type);
        }
    }

    /**
     * Check if file exist, if not abort 404,
     * if exist just return the path of file.
     *
     * @param $directory
     * @param $file
     * @return string
     */
    private function fileExists($directory, $file)
    {
        $path = storage_path("{$this->path}/{$directory}/{$file}");

        if (! File::exists($path)) abort(404);

        return $path;
    }
}
