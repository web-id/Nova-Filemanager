<?php

namespace WebId\Filemanager\App;

use WebId\Filemanager\App\Models\Media;
use WebId\Filemanager\Http\Services\FileManagerService;

class MediaFromFiles
{
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
     * Exist in BDD ?
     *
     * @param $name
     * @param $extension
     * @return bool
     */
    public function existInDDB($name, $extension)
    {
        $media = Media::where('name', $name)->where('extension', $extension)->first();
        return !!$media;
    }

    /**
     *  Exist in Storage ?
     *
     * @param $pathFile
     * @return bool
     */
    public function existInStorage($pathFile)
    {
        return $this->service->exists($pathFile);
    }

    /**
     * get a filepath for file
     *
     * @param $name
     * @param $path
     * @param $extension
     * @return string
     */
    public function getFullPathFile($name, $path, $extension)
    {
        $fullpath = '';
        if ($path !== '/') {
            $fullpath .= $path.'/';
        }
        $fullpath .= $name.'.'.$extension;

        return $fullpath;
    }

    /**
     * Return random string with x char
     * @param int $number
     * @return string
     */
    public function getRandomStr($number = 7)
    {
        return strtolower(str_random($number));
    }

    /**
     * Convert existing file and BDD for using str_slug()
     */
    public function forceStrSlug()
    {
        $medias = Media::all();
        $medias->each(function ($media) {
            $name_slugged = str_slug($media->name);
            if ($media->name != $name_slugged) { //Need to rename file
                if ($this->existInStorage($this->getFullPathFile($name_slugged, $media->path, $media->extension))) { //File already named like this in path
                    $name_slugged = $name_slugged . '_' . $this->getRandomStr(7);
                    $media->name = $name_slugged;
                    $media->save();
                    $this->service->renameFile($media->fullpath, $name_slugged, $media->extension);
                } else {
                    $media->name = $name_slugged;
                    $media->save();
                    $this->service->renameFile($media->fullpath, $name_slugged, $media->extension);
                }
            }
        });
    }

    /**
     * Convert a file storage to Media
     * @param $file
     * @return \WebId\Filemanager\App\Models\Media
     */
    public function getMediaFromFileStorage($file)
    {
        $media = new Media;
        $media->name = FileManagerService::getFileNameWithoutExtension($file->path) ?? '';
        $media->extension = FileManagerService::getFileExtension($file->path) ?? '';
        $media->path = FileManagerService::getFilePathWithoutName($file->path) ?? '';
        return $media;
    }

    /**
     * Populate BDD
     */
    public function populate()
    {
        $racineFiles = $this->service->getFiles('/', 'mime');
        $files = $this->service->loopDirsForPopulateCollectOfFiles('/', $racineFiles);
        $files->each(function ($file) {
            $media = $this->getMediaFromFileStorage($file);
            $name_slugged = str_slug($media->name);
            $fullpath = $media->fullpath; //the real because changing name, change fullpath here
            dd();
            if(!$this->existInDDB($media->name, $media->extension) || $name_slugged != $media->name) {
                $media->name = $name_slugged . '_' . $this->getRandomStr(7);
                $this->service->renameFile($fullpath, $media->name, $media->extension);
                $media->save();
            }
        });
    }
}