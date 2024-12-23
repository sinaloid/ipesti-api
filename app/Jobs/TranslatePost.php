<?php

namespace App\Jobs;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;

class TranslatePost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $post;

    /**
     * Créer une nouvelle instance du Job.
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Exécuter le Job.
     */
    public function handle()
    {
        // Traduction du titre
        $responseTitre = Http::timeout(60)->post('https://translate.enumera.tech/translate', [
            'q' => $this->post->titre,
            'source' => 'auto',
            'target' => 'en',
            'format' => 'text',
            'api_key' => '',
        ]);

        // Traduction du contenu
        $responseContenu = Http::timeout(60)->post('https://translate.enumera.tech/translate', [
            'q' => $this->post->contenu,
            'source' => 'auto',
            'target' => 'en',
            'format' => 'html',
            'api_key' => '',
        ]);

        // Traduction du contenu
        $responseHtmlOne = Http::timeout(60)->post('https://translate.enumera.tech/translate', [
            'q' => $this->post->htmlOne,
            'source' => 'auto',
            'target' => 'en',
            'format' => 'html',
            'api_key' => '',
        ]);

        // Enregistrer les traductions dans la base de données
        $this->post->update([
            'titre_en' => $responseTitre->json()['translatedText'] ?? $this->post->titre,
            'contenu_en' => $responseContenu->json()['translatedText'] ?? $this->post->contenu,
            'htmlOne_en' => $responseHtmlOne->json()['translatedText'] ?? $this->post->htmlOne,
        ]);
    }
}
