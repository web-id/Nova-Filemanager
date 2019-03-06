<?php

namespace Webid\Filemanager\Http\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface MediaRepositoryContract
{
    /**
     * Récupère toutes les Medias du site
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Retourne le Media par l'id
     * @param int $id
     * @return mixed
     */
    public function find(int $id);
}