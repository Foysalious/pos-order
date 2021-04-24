<?php


namespace App\Services\FileManagers;


use Symfony\Component\HttpFoundation\File\UploadedFile;

trait FileManager
{
    protected function uniqueFileName($file, $name, $ext = null)
    {
        if (empty($name)) {
            $name = "TIWNN";
        }
        $name = strtolower(str_replace(' ', '_', $name));
        return time() . "_" . $name . "." . ($ext ?: $this->getExtension($file));
    }

    private function getExtension($file)
    {
        if ($file instanceof UploadedFile) return $file->getClientOriginalExtension();
        return getBase64FileExtension($file);
    }
}
