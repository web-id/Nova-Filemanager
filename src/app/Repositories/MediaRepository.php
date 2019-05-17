<?php

namespace WebId\Filemanager\App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use WebId\Filemanager\App\Models\Media;
use WebId\Filemanager\App\Repositories\Contracts\MediaRepositoryContract;
use WebId\Filemanager\Http\Services\FileManagerService;

class MediaRepository extends BaseRepository implements MediaRepositoryContract
{
    /**
     * MediaRepository constructor.
     *
     * @param Media $model
     */
    public function __construct(Media $model)
    {
        parent::__construct($model);
    }

    /**
     * Récupère tout les medias du site
     * @param bool $paginate
     * @param array $options
     * @return mixed
     */
    public function all(Bool $paginate = false, Array $options = [])
    {
        //OPTIONS
        $search = isset($options['search']) ? $options['search'] : null;
        $notIn = isset($options['notIn']) ? $options['notIn'] : null;
        $in = isset($options['in']) ? $options['in'] : null;
        $extension = isset($options['extension']) ? $options['extension'] : null;
        $paginateCount = isset($options['paginateCount']) ? $options['paginateCount'] : env('MODULE_FILEMANAGER_PAGINATE', 15);

        $query = $this->model;
        if($search) { $query = $query->where('name', 'LIKE', "%$search%"); }
        if($notIn) { $query = $query->whereNotIn('id', $notIn); }
        if($in) { $query = $query->whereIn('id', $in); }
        if($extension) { $query = $query->where('extension', $extension); }
        $query = $query->orderBy('updated_at', 'desc');

        return $paginate ? $query->paginate($paginateCount) : $query->get();
    }

    /**
     * Retourne le media en fonction de l'id
     * @param int $id
     * @return mixed
     * @throws ModelNotFoundException
     */
    public function find(int $id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Creation d'un media
     * @param array $data
     * @return mixed
     */
    public function create(array $data) {
        return $this->model->create($data);
    }

    /**
     * Suppression d'un media
     * @param int $id
     * @return mixed
     */
    public function delete(int $id) {
        return $this->model->destroy($id);
    }

    /**
     * Update d'un media
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update(int $id, array $data) {
        try {
            $model = $this->find($id);
            return $model->update($data);
        }
        catch (ModelNotFoundException $exception) {
            return 0;
        }
    }

    /**
     * Retourne le media à partir du path
     * @param string $path
     * @return mixed
     */
    public function findByPath(string $path) {
        $exploded = explode('/', $path);
        $fileName = FileManagerService::getFileNameWithoutExtension(count($exploded) ? $exploded[count($exploded) - 1] : $path);
        $fileExtension = FileManagerService::getFileExtension(count($exploded) ? $exploded[count($exploded) - 1] : $path);
        return $this->model->where('name', $fileName)->where('extension', $fileExtension)->first();
    }
}