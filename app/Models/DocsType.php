<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocsType extends Model
{
    protected $table = 'docs_types';

    protected $fillable = [
        'name',
    ];
}
