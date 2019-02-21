<?php

namespace WebId\Filemanager\App;

use WebId\Filemanager\App\Models\Media;
use WebId\Filemanager\Http\Services\FileManagerService;

class MediaFromFiles {
    /**
     * @var string
     */
    protected $table = "medias";

    /**
     * @var \WebId\Filemanager\Http\Services\FileManagerService
     */
    protected $service;

    /**
     * MediaFromFiles constructor.
     *
     */
    public function __construct()
    {
        $this->service = new FileManagerService();
    }

    /**
     * Populate BDD
     */
    public function populate() {
        $racineFiles = $this->service->getFiles('/', 'mime');
        $files = $this->service->loopDirsForPopulateCollectOfFiles('/', $racineFiles);
        $files->each(function($file) {
            $media = new Media;
            $media->name = FileManagerService::getFileNameWithoutExtension($file->path) ?? '';
            $media->extension = FileManagerService::getFileExtension($file->path) ?? '';
            $media->path = FileManagerService::getFilePathWithoutName($file->path) ?? '';
            $existe = Media::where('name', $media->name)->where('extension', $media->extension)->first();
            if($existe) {
                $random = str_random(7);
                $media->name = $media->name . $random;
                if($this->service->renameFile($file->path, $media->name, $media->extension)) {
                    $media->save();
                }
            } else {
                return $media->save();
            }
        });
    }

}