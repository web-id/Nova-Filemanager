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
     * @param $path
     * @return bool
     */
    public function existInDB($name, $extension, $path = null)
    {
        $query = Media::where('name', 'LIKE', $name)->where('extension', $extension);
        if($path) {
            $query->where('path', $path);
        }
        $media = $query->first();
        return !!$media;
    }

    /**
     * Exist in BDD with another path ?
     *
     * @param $name
     * @param $extension
     * @param $path
     * @return bool
     */
    public function existInDBWithAnotherPath($name, $extension, $path)
    {
        $query = Media::where('name', $name)->where('extension', $extension)->where('path', 'NOT LIKE', $path);
        $media = $query->first();
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
        return file_exists($pathFile);
        //return $this->service->exists($pathFile);
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
            $realPath = $media->fullpath;
            if ($media->name != $name_slugged) { //Need to rename file
                if ($this->existInStorage($this->getFullPathFile($name_slugged, $media->path, $media->extension))) { //File already named like this in path
                    $name_slugged = $name_slugged . '-' . $this->getRandomStr(7);
                    $media->name = $name_slugged;
                    $media->save();
                    $this->service->renameFile($realPath, $name_slugged, $media->extension);
                } else {
                    $media->name = $name_slugged;
                    $media->save();
                    $this->service->renameFile($realPath, $name_slugged, $media->extension);
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

        $fullpath = $media->fullpath; //fullpath is dynamic by the name so let's save the real one

        if($media->name != str_slug($media->name)) {
            $media->name = str_slug($media->name);
            if($this->existInDB(str_slug($media->name), $media->extension) || $this->service->exists($media->fullpath)) {
                $media->name = $media->name . '-' . $this->getRandomStr(7);
            }
            $this->service->renameFile($fullpath, $media->name, $media->extension);
        }

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
            $fullpath = $media->fullpath; //the real because changing name, change fullpath here

            if(!$this->existInDB($media->name, $media->extension)) { //New file
                $media->save();
            } else { //Exist
                if($this->existInDBWithAnotherPath($media->name, $media->extension, $media->path)) { //Check if same file in another path
                    $media->name = $media->name . '-' . $this->getRandomStr(7);
                    $this->service->renameFile($fullpath, $media->name, $media->extension);
                    $media->save();
                }
            }
        });
    }
}