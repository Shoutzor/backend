<?php

namespace App\Models;

use App\Traits\UsesUUID;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use \Nuwave\Lighthouse\Execution\Utils\Subscription;

class Upload extends Model
{
    use UsesUUID;

    const STATUS_QUEUED = 0; # In queue to be processed
    const STATUS_PROCESSING = 1; # Currently being processed
    const STATUS_FAILED_RETRY = 2; # Processing failed, retry allowed.
    const STATUS_FAILED_FINAL = 3; # Processing failed, no retry.

    const QUEUE_NAME = 'uploads';
    const STORAGE_PATH = 'temp/upload/';

    const CREATED_AT = 'uploaded_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'original_filename',
        'filename',
        'source',
        'uploaded_by',
        'status',
        'data' // A field containing JSON that can be used by MediaSources to store custom properties
    ];

    protected $casts = [
        'data' => AsArrayObject::class
    ];

    protected static function boot() {
        parent::boot();

        static::updated(function(Upload $upload) {
            Subscription::broadcast('uploadUpdated', $upload);
        });

        static::deleted(function(Upload $upload) {
            Storage::delete($upload->getFilePath());
            Subscription::broadcast('uploadDeleted', $upload);
        });
    }

    public function uploaded_by()
    {
        return $this->hasOne('App\Models\User', 'id', 'uploaded_by');
    }

    public function getFilePath() {
        return storage_path('app/' . static::STORAGE_PATH . $this->filename);
    }

}
