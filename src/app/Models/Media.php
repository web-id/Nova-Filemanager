<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Media extends Model
{
    use HasTranslations;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'image', 'alt', 'folder_id'];

    /**
     * @return mixed
     */
    public function folder() {
        return $this->belongsTo('App\Folder');
    }
}