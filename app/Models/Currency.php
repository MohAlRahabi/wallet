<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int id
 * @property string code
 * @property string name
 * @property int decimal_places
 * @mixin Builder
 */
class Currency extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'decimal_places',
    ];

    protected $casts = [
        'decimal_places' => 'integer',
    ];

}
