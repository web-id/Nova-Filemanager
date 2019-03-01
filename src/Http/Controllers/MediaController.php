<?php

namespace WebId\Filemanager\Http\Controllers;

use App\Http\Controllers\Controller;
use WebId\Filemanager\App\Repositories\Contracts\MediaRepositoryContract;
use Illuminate\Http\Request;

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
    public function index(Request $request) {
        $mediaRepository = app()->make(MediaRepositoryContract::class);
        return response()->json($mediaRepository->all(true, [
            'search' => $request->search,
            'notIn' => $request->notIn,
            'in' => $request->in,
            'paginateCount' => $request->paginateCount,
        ]));
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