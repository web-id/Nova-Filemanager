<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'folder_id'];

    /**
     * @return mixed
     */
    public function medias() {
        return $this->hasMany('App\Media');
    }

    /**
     * @return mixed
     */
    public function folder() {
        return $this->belongsTo('App\Folder');
    }
}