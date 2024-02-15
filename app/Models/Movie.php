<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'url',
        'picture',
        'description',
        'score',
        'trailer',
        'content',
        'year',
        'is_show',
        'number_of_page',
        'number_of_movie',
    ];
}
