<?php

namespace Meanify\LaravelObfuscator\Models;

use Illuminate\Database\Eloquent\Model;
class ObfuscatorFailure extends Model
{
    protected $table = 'obfuscator_failures';

    protected $fillable = [
        'model',
        'input',
        'reason',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];
}
