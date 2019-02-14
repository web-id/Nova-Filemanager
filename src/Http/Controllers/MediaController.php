<?php

namespace WebId\Filemanager\Http\Controllers;

use App\Http\Controllers\Controller;
use WebId\Filemanager\App\Repositories\Contracts\MediaRepositoryContract;

/**
 * Class MediaController
 *
 * @package Webid\ModuleFileManager\App\Http\Controllers
 */
class MediaController extends Controller
{
    /**
     * @return mixed
     */
    public function index() {
        $mediaRepository = app()->make(MediaRepositoryContract::class);
        return response()->json($mediaRepository->all());
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id) {
        $mediaRepository = app()->make(MediaRepositoryContract::class);
        return response()->json($mediaRepository->find($id));
    }
}