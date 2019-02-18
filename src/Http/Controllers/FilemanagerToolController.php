<?php

namespace WebId\Filemanager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use WebId\Filemanager\App\Models\Media;
use WebId\Filemanager\App\Repositories\Contracts\MediaRepositoryContract;
use WebId\Filemanager\Http\Services\FileManagerService;

class FilemanagerToolController extends Controller
{
    /**
     * @var mixed
     */
    protected $service;

    /**
     * @param FileManagerService $filemanagerService
     */
    public function __construct(FileManagerService $filemanagerService)
    {
        $this->service = $filemanagerService;
    }

    /**
     * @param Request $request
     */
    public function getData(Request $request)
    {
        return $this->service->ajaxGetFilesAndFolders($request);
    }

    /**
     * @param \Illuminate\Http\Request $request
     */
    public function getSearchData(Request $request)
    {
        return $this->service->ajaxSearchFromAllFilesAndFolders($request->search);
    }

    /**
     * @param Request $request
     */
    public function createFolder(Request $request)
    {
        return $this->service->createFolderOnPath($request->folder, $request->current);
    }

    /**
     * @param Request $request
     */
    public function deleteFolder(Request $request)
    {
        return $this->service->deleteDirectory($request->current);
    }

    /**
     * @param Request $request
     */
    public function upload(Request $request)
    {
        $mediaRepository = app()->make(MediaRepositoryContract::class);
        $fileName = FileManagerService::getFileNameWithoutExtension($request->file->getClientOriginalName());
        $extension = FileManagerService::getFileExtension($request->file->getClientOriginalName());
        $existeModel = $mediaRepository->all(false, [
            'search' => $fileName,
            'extension' => $extension
        ]);
        $fileName = FileManagerService::getFileNameWithoutExtension($this->service->uploadFile($request->file, $request->current, !!$existeModel->count()));
        if($fileName) {
            $data = $request->all();
            unset($data['file']);
            $data['path'] = $data['current'];
            unset($data['current']);
            $data['name'] = $fileName;
            $data['extension'] = $extension;
            if($mediaRepository->create($data)) {
                return response()->json(['success' => true, 'name' => $fileName]);
            } else {
                return response()->json(['success' => false]);
            }
        } else {
            return response()->json(['success' => false]);
        }
    }

    /**
     * @param Request $request
     */
    public function getInfo(Request $request)
    {
        $info = $this->service->getFileInfo($request->file);
        $mediaRepository = app()->make(MediaRepositoryContract::class);
        $existeModel = $mediaRepository->findByPath($info['path']);
        if($existeModel) {
            $bddInfo = $existeModel->toArray();
            $mergedInfo = $this->service::injectBddData($info, $bddInfo);
            return response()->json($mergedInfo);
        } else {
            return response()->json(false);
        }
    }

    /**
     * @param Request $request
     */
    public function removeFile(Request $request)
    {
        $mediaRepository = app()->make(MediaRepositoryContract::class);
        $existeModel = $mediaRepository->findByPath($request->file);
        if($existeModel) {
            if($mediaRepository->delete($existeModel->id)) {
                return $this->service->removeFile($request->file);
            } else {
                return response()->json(false);
            }
        } else {
            return response()->json(false);
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     */
    public function moveFile(Request $request)
    {
        $mediaRepository = app()->make(MediaRepositoryContract::class);
        $file = $mediaRepository->findByPath($request->filePath);
        if($mediaRepository->update($file->id, ['path' => $request->folderPath])) {
            return $this->service->ajaxMoveFileOnFolder($request->filePath, $request->folderPath);
        } else {
            return response()->json([
                'message'   => 'Error server !'
            ], 500);
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     */
    public function updateFile(Request $request)
    {
        $mediaRepository = app()->make(MediaRepositoryContract::class);
        $file = $mediaRepository->find($request->id);
        $data = $request->only((new Media)->getFillable());
        $path = $data['path'];
        $extension = $data['extension'];
        unset($data['path']);
        unset($data['extension']);
        if($file) {
            if($mediaRepository->update($file->id, $data)) {
                if(isset($request->name) && $file->name != $request->name) {
                    return $this->service->renameFile($path, $request->name, $extension);
                } else {
                    return response()->json(true);
                }
            } else {
                return response()->json([
                    'message'   => 'Error server !'
                ], 500);
            }
        } else {
            return response()->json([
                'message'   => 'Error server !'
            ], 500);
        }
    }
}
