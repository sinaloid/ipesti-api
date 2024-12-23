<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class TranslatePosts extends Command
{
    protected $signature = 'posts:translate';
    protected $description = 'Traduire tous les posts en arrière-plan';

    public function handle()
    {
        $posts = Post::whereNull('titre_en')->orWhereNull('contenu_en')->orWhereNull('htmlOne')->get();

        foreach ($posts as $post) {
            $post->dispatchTranslationJob();
        }

        $this->info('Toutes les traductions ont été lancées.');
    }
}
