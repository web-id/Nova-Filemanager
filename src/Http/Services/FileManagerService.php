<?php

namespace WebId\Filemanager\Http\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use WebId\Filemanager\App\Models\Media;
use Illuminate\Http\File;

class FileManagerService
{
    use GetFiles;

    /**
     * @var mixed
     */
    protected $storage;

    /**
     * @var mixed
     */
    protected $disk;

    /**
     * @var mixed
     */
    protected $currentPath;

    /**
     * @var mixed
     */
    protected $exceptFiles;

    /**
     * @var mixed
     */
    protected $exceptFolders;

    /**
     * @var mixed
     */
    protected $exceptExtensions;

    /**
     * @param Storage $storage
     */
    public function __construct()
    {
        $this->disk = env('FILEMANAGER_DISK', 'public');
        $this->storage = Storage::disk($this->disk);
        $this->exceptFiles = collect([]);
        $this->exceptFolders = collect([]);
        $this->exceptExtensions = collect([]);
        $this->globalFilter = null;
    }

    /**
     * Get ajax request to load files and folders.
     *
     * @param Request $request
     *
     * @return json
     */
    public function ajaxGetFilesAndFolders(Request $request)
    {
        $folder = $this->cleanSlashes($request->get('folder'));

        if (! $this->storage->exists($folder)) {
            $folder = '/';
        }

        //Set relative Path
        $this->setRelativePath($folder);

        $order = $request->get('sort');
        if (! $order) {
            $order = 'mime';
        }
        $filter = $request->get('filter');
        if (! $filter) {
            $filter = false;
        }
        $files = $this->getFiles($folder, $order, $filter);

        return response()->json(['files' => $files, 'path' => $this->getPaths($folder)]);
    }

    /**
     * Get ajax request to load files and folders from search.
     *
     * @param $search
     * @return mixed
     */
    public function ajaxSearchFromAllFilesAndFolders($search)
    {
        $racineFiles = $this->getFiles('/', 'mime');
        $files = $this->loopDirsForPopulateCollectOfFiles('/', $racineFiles, $search);

        return response()->json(['files' => $files, 'path' => []]);
    }

    public function ajaxMoveFileOnFolder($filePath, $folderPath)
    {
        try {
            $fileNameExploded = explode('/', $filePath);
            $fileName = $fileNameExploded[count($fileNameExploded) - 1];
            $this->storage->move($filePath, $folderPath.'/'.$fileName);
        } catch (\Exception $exception) {
            dump($exception);
            return response()->json([
                'message'   => 'Error server !'
            ], 500);
        }
        return response()->json([]);
    }

    public function loopDirsForPopulateCollectOfFolders($baseUrl, $racineFiles)
    {
        $folders = collect();
        $racineFiles->each(function($file) use (&$folders, $baseUrl) {
            if($file->type === "dir") {
                $pos = strpos($file->path, $baseUrl);
                if ($baseUrl !== '/' && $pos === false) {
                    $file->path = $baseUrl === '/' ? $file->path : $baseUrl . '/' . $file->path;
                }
                $folders->push($file);
                $folders = $folders->merge(
                    $this->loopDirsForPopulateCollectOfFolders($file->path, $this->getFiles($file->path, 'mime'))
                );
            }
        });
        return $folders;
    }

    /**
     * Loop on $baseUrl and return all files (include files on dir).
     *
     * @param $baseUrl
     * @param $racineFiles
     * @param bool $search
     * @return mixed
     */
    public function loopDirsForPopulateCollectOfFiles($baseUrl, $racineFiles, $search = false)
    {
        $files = collect();
        $racineFiles->each(function($file) use (&$files, $baseUrl, $search) {
            if($file->type === "dir") {
                $files = $files->merge(
                    $this->loopDirsForPopulateCollectOfFiles($file->path, $this->getFiles($file->path, 'mime'), $search)
                );
            } else {
                if($search) {
                    if(strpos($file->name, $search) !== false) {
                        $pos = strpos($file->path, $baseUrl);
                        if ($baseUrl !== '/' && $pos === false) {
                            $file->path = $baseUrl === '/' ? $file->path : $baseUrl . '/' . $file->path;
                        }
                        $files->push($file);
                    }
                } else {
                    $pos = strpos($file->path, $baseUrl);
                    if ($baseUrl !== '/' && $pos === false) {
                        $file->path = $baseUrl === '/' ? $file->path : $baseUrl . '/' . $file->path;
                    }
                    $files->push($file);
                }
            }
        });
        return $files;
    }

    /**
     *  Create a folder on current path.
     *
     * @param $folder
     * @param $path
     *
     * @return  json
     */
    public function createFolderOnPath($folder, $currentFolder)
    {
        $folder = $this->fixDirname($this->fixFilename($folder));

        $path = $currentFolder.'/'.$folder;

        if ($this->storage->has($path)) {
            return response()->json(['error' => __('The folder exist in current path')]);
        }

        if ($this->storage->makeDirectory($path)) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }

    /**
     * Removes a directory.
     *
     * @param $currentFolder
     *
     * @return  json
     */
    public function deleteDirectory($currentFolder)
    {
        if ($this->storage->deleteDirectory($currentFolder)) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }

    /**
     * Upload a file on current folder.
     *
     * @param $filePath
     * @param $currentFolder
     * @param $fileName
     * @param $extension
     *
     * @return  mixed
     */
    public function uploadFile($filePath, $currentFolder, $fileName, $extension)
    {
        $file = new File($filePath);
        if ($this->storage->putFileAs($currentFolder, $file, $fileName . '.' . $extension)) {
            $this->setVisibility($currentFolder, $fileName . '.' . $extension);
            return $fileName;
        } else {
            return false;
        }
    }

    /**
     * Get info of file normalized.
     *
     * @param $file
     *
     * @return  array
     */
    public function getFileInfo($file)
    {
        $fullPath = $this->storage->path($file);

        $info = new NormalizeFile($this->storage, $fullPath, $file);


        return $info->toArray();
    }

    /**
     * Get info of file as Array.
     *
     * @param $file
     *
     * @return  json
     */
    public function getFileInfoAsArray($file)
    {
        if (! $this->storage->exists($file)) {
            return [];
        }

        $fullPath = $this->storage->path($file);

        $info = new NormalizeFile($this->storage, $fullPath, $file);

        return $info->toArray();
    }

    /**
     * Remove a file from storage.
     *
     * @param $file
     *
     * @return  json
     */
    public function removeFile($files)
    {
        if ($this->storage->delete($files)) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }

    /**
     * @param $filePath
     * @param $forceSlug
     */
    private function checkFileExists($currentFolder, $file, $forceSlug = false)
    {
        if ($this->storage->has($currentFolder.'/'.$file->getClientOriginalName()) || $forceSlug) {
            $random = str_random(7);
            $newName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME).'_'.mb_strtolower($random);

            return $newName.'.'.$file->getClientOriginalExtension();
        }

        return $file->getClientOriginalName();
    }

    /**
     * Set visibility to public.
     *
     * @param $folder
     * @param $file
     */
    private function setVisibility($folder, $file)
    {
        if ($folder != '/') {
            $folder .= '/';
        }
        $this->storage->setVisibility($folder.$file, 'public');
    }

    public function renameFile($path, $name, $extension)
    {
        $pathWithoutName = $this::getFilePathWithoutName($path);
        $newPath = $pathWithoutName;
        $newPath .= $pathWithoutName === '/' ? $name : '/' . $name;
        $newPath .= '.' . $extension;
        try {
            $this->storage->move($path, $newPath);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function renameFolder($path, $name)
    {
        try {
            //HACK
            $pathWithoutName = $this::getFilePathWithoutName($path);
            $newPath = $pathWithoutName;
            $newPath .= $pathWithoutName === '/' ? $name . 'hackfix' : '/' . $name . 'hackfix';
            $this->storage->move($path, $newPath);

            $hackPath = $newPath;
            $newPath = $pathWithoutName;
            $newPath .= $pathWithoutName === '/' ? $name : '/' . $name;
            $this->storage->move($hackPath, $newPath);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function exists($file)
    {
        return $this->storage->exists($file);
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
     * Get filename without extension
     * @param $fileName
     * @return string
     */
    static public function getFileNameWithoutExtension($fileName)
    {
        $exploded = explode('.', $fileName);
        if(!count($exploded)) { return $fileName; }
        array_pop($exploded);
        $withoutExtension = implode('.', $exploded);

        $exploded = explode('/', $withoutExtension);
        if(!count($exploded)) { return $withoutExtension; }
        return array_pop($exploded);
    }

    /**
     * Get extension
     * @param $fileName
     * @return string
     */
    static public function getFileExtension($fileName)
    {
        $exploded = explode('.', $fileName);
        if(!count($exploded)) { return ''; }
        return $exploded[count($exploded) - 1];
    }

    /**
     * inject Bdd data to fileInfo
     * @param $dataFile
     * @param $dataModel
     * @return array
     */
    static public function injectBddData($dataFile, $dataModel) {
        unset($dataModel['path']);
        $dataModel['name_without_extension'] = $dataModel['name'];
        unset($dataModel['name']);
        return array_merge($dataFile, $dataModel);
    }

    /**
     * Get path without name of file
     * @param $fullPath
     * @return string
     */
    static public function getFilePathWithoutName($fullPath) {
        $exploded = explode('/', $fullPath);
        if(!count($exploded)) { return '/'; }
        array_pop($exploded);
        return implode('/', $exploded);
    }

    /**
     * get a filepath for file
     *
     * @param $name
     * @param $path
     * @param $extension
     * @return string
     */
    static function getFullPathFile($name, $path, $extension)
    {
        $fullpath = '';
        if ($path !== '/') {
            $fullpath .= $path.'/';
        }
        $fullpath .= $name.'.'.$extension;

        return $fullpath;
    }
}
