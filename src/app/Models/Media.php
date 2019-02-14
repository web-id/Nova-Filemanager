<?php

namespace WebId\Filemanager\App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Media extends Model
{
    use HasTranslations;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'medias';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'extension', 'alt', 'path'];

    protected $appends = ['fullpath'];

    public function getFullpathAttribute() {
        $fullpath = '';
        if($this->attributes['path'] !== '/') {
            $fullpath .= $this->attributes['path'] . '/';
        }
        $fullpath .= $this->attributes['name'] . '.' . $this->attributes['extension'];
        return $fullpath;
    }
}