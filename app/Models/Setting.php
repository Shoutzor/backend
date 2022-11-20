<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'key';
    protected $keyType = 'string';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'name',
        'description',
        'group',
        'order',
        'validation',
        'readonly'
    ];

    /**
     * "value" will always be of type JSON, containing a "data" field, which is of the type as defined by "type"
     */
    protected $casts = [
        'value' => AsArrayObject::class
    ];
}
