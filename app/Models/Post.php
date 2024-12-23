<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        "titre",
        "date",
        "date_debut",
        "date_fin",
        "lien",
        "image",
        "contenu",
        "htmlOne",
        "email",
        "post_ordre",
        "slug",
        "is_deleted",
        "parent_id",
        "user_id",
        'titre_en',
        'contenu_en',
        'htmlOne_en'
    ];



    /*public function toutesSousCategories()
    {
        return $this->enfants->map(function ($sousCategorie) {
            return collect([$sousCategorie])->merge($sousCategorie->toutesSousCategories());
        })->flatten();
    }



    public function scopeAvecToutesSousCategories($query)
    {
        return $query->with('enfants')->with('enfants.enfants');
    }*/
    public function toutesSousCategories()
    {
        return $this->enfants()->with('toutesSousCategories','ressource_images');
    }


    /*public function toutesSousCategories()
    {
        return $this->enfants->map(function ($sousCategorie) {
            return collect([$sousCategorie])->merge($sousCategorie->toutesSousCategories());
        })->flatten();
    }*/

    public function parent()
    {
        return $this->belongsTo(Post::class, 'parent_id');
    }

    public function enfants()
    {
        return $this->hasMany(Post::class, 'parent_id');
    }

    public function ressource_images()
    {
        return $this->hasMany(RessourceImage::class, 'post_id');
    }

    /**
     * Démarre la traduction si nécessaire.
     */
    public function dispatchTranslationJob()
    {
        if (!$this->titre_en || !$this->contenu_en || !$this->htmlOne_en) {
            \App\Jobs\TranslatePost::dispatch($this);
        }
    }
}
