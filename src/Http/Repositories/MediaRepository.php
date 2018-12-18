<?php

namespace Webid\Filemanager\Http\Repositories;

use App\Media;
use App\Repositories\BaseRepository;
use Illuminate\Support\Collection;
use Webid\Filemanager\Http\Repositories\Contracts\MediaRepositoryContract;

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
     * Récupère toutes les Medias du site
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Retourne le Media par l'id
     * @param int $id
     * @return mixed
     */
    public function find(int $id)
    {
        return $this->model->find($id);
    }
}