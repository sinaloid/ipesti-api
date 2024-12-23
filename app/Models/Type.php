<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use HasFactory;

    protected $fillable = [
        "label",
        "slug",
        "description",
        "is_deleted",
        "categorie_type_id",
    ];

    public function categorieType (){

        return $this->belongsTo(CategorieType::class);
    }
}
