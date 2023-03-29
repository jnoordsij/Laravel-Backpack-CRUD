<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Uploads\Uploaders;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SingleFile extends Uploader
{
    public function uploadRepeatableFile(Model $entry, $values = null)
    {
        $orderedFiles = $this->getFileOrderFromRequest();

        $previousFiles = $this->getPreviousRepeatableValues($entry);

        foreach ($values as $row => $file) {
            if ($file && is_file($file) && $file->isValid()) {
                $fileName = $this->getFileName($file);

                $file->storeAs($this->getPath(), $fileName, $this->getDisk());
                $orderedFiles[$row] = $this->getPath().$fileName;

                continue;
            }
        }

        foreach ($previousFiles as $row => $file) {
            if ($file && ! isset($orderedFiles[$row])) {
                $orderedFiles[$row] = null;
                Storage::disk($this->getDisk())->delete($file);
            }
        }

        return $orderedFiles;
    }

    public function uploadFile(Model $entry, $value = null)
    {
        $value = $value ?? CrudPanelFacade::getRequest()->file($this->getName());

        $previousFile = $entry->getOriginal($this->getName());

        if ($value && is_file($value) && $value->isValid()) {
            if ($previousFile) {
                Storage::disk($this->getDisk())->delete($previousFile);
            }
            $fileName = $this->getFileName($value);

            $value->storeAs($this->getPath(), $fileName, $this->getDisk());

            return $this->getPath().$fileName;
        }

        if (! $value && CrudPanelFacade::getRequest()->has($this->getName()) && $previousFile) {
            Storage::disk($this->getDisk())->delete($previousFile);

            return null;
        }

        return $previousFile;
    }
}
