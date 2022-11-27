<?php

namespace App\Models;

use App\Traits\UsesUUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Album extends Model
{
    use UsesUUID, HasFactory;

    const STORAGE_PATH = 'album/';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function artists()
    {
        return $this->hasManyThrough('App\Models\Artist', 'App\Models\Media', 'media_id', 'album_id', 'album_id', '');
    }

    public function media()
    {
        return $this->hasMany('App\Models\Media');
    }

    public function getImageAttribute($value)
    {
        if(File::exists(storage_path($value))) {
            return $value;
        }

        return '/images/placeholder/album_placeholder.jpg';
    }
}
