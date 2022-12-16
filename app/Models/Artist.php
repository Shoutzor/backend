<?php

namespace App\Models;

use App\Traits\UsesUUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Artist extends Model
{
    use UsesUUID, HasFactory;

    const STORAGE_PATH = 'artist/';

    protected $fillable = ['name', 'image'];

    public function albums()
    {
        return $this->belongsToMany('App\Models\Album');
    }

    public function media()
    {
        return $this->belongsToMany('App\Models\Media');
    }

    public function getImageAttribute($value)
    {
        if(File::exists(storage_path(self::STORAGE_PATH . $value))) {
            return $value;
        }

        return '/images/placeholder/artist_placeholder.png';
    }
}
