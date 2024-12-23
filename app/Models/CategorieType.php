<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategorieType extends Model
{
    use HasFactory;

    protected $fillable = [
        "label",
        "slug",
        "is_deleted",
    ];


    public function types (){

        return $this->hasMany(Type::class);
    }
}
