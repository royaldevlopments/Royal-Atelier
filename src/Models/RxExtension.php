<?php

namespace RoyalPanel\RoyalAtelier\Models;

use Illuminate\Database\Eloquent\Model;

class RxExtension extends Model
{
    protected $table = 'rx_extensions';

    protected $fillable = [
        'extension_id', 'name', 'version', 'author',
        'description', 'icon', 'website', 'installed', 'enabled', 'config',
    ];

    protected $casts = [
        'installed' => 'boolean',
        'enabled' => 'boolean',
        'config' => 'array',
    ];
}
