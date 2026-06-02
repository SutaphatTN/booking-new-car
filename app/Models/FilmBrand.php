<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FilmBrand extends Model
{
    use SoftDeletes;

    protected $table = 'tb_film_brands';

    protected $fillable = ['name', 'code'];

    public function stocks()
    {
        return $this->hasMany(FilmStock::class, 'film_brand_id');
    }
}
