<?php

namespace WebId\Filemanager\App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface MediaRepositoryContract
{
    /**
     * Récupère toutes les medias du site
     * @param bool $paginate
     * @param array $options
     * @return mixed
     */
    public function all(Bool $paginate = false, Array $options = []);

    /**
     * Retourne le media par l'id
     * @param int $id
     * @return mixed
     */
    public function find(int $id);

    /**
     * Creation d'un media
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * Suppression d'un media
     * @param int $id
     * @return mixed
     */
    public function delete(int $id);

    /**
     * Update d'un media
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update(int $id, array $data);

    /**
     * Retourne le media à partir du path
     * @param string $path
     * @return mixed
     */
    public function findByPath(string $path);
}