<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\Post;
use App\Models\RessourceImage;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$data = Categorie::where("is_deleted", false)->get();
        //$data = Categorie::with('enfants')->whereNull('parent_id')->get();
        //$data = Categorie::avecToutesSousCategories()->whereNull('parent_id')->get();
        $data = Post::with('toutesSousCategories')->whereNull('parent_id')->get();

        if ($data->isEmpty()) {
            return response()->json(['message' => 'Aucun post trouvée'], 404);
        }

        return response()->json(['message' => 'Posts récupérés', 'data' => $data], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Vérifier que les champs obligatoires sont remplis
        //dd($request->parent);
        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:255',
            'parent' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'date' => 'nullable|date',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'lien' => 'nullable|string',
            'email' => 'nullable|string|email|max:255',
            'contenu' => 'nullable|string',
            'htmlOne' => 'nullable|string',

        ]);

        //dd(Str::slug($request->titre));
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }

        $parent = Post::where("id",$request->parent)->first();

        $data = Post::create([
            'titre' => $request->input('titre'),
            'contenu' => $request->input('contenu'),
            'htmlOne' => $request->input('htmlOne'),
            'email' => $request->input('email'),
            'date' => $request->input('date'),
            'date_debut' => $request->input('date_debut'),
            'date_fin' => $request->input('date_fin'),
            'lien' => $request->input('lien'),
            'parent_id' => isset($parent) ? $parent->id : null,
            'user_id' => Auth::user()->id,
            'slug' => Str::slug($request->titre),
        ]);

        if ($request->hasFile('image')) {
            // Générer un nom aléatoire pour l'image
            $imageName = Str::random(10) . '.' . $request->image->getClientOriginalExtension();

            // Enregistrer l'image dans le dossier public/images
            $imagePath = $request->image->move(public_path('posts'), $imageName);

            if ($imagePath) {
                $data->update([
                    'image' => 'posts/' . $imageName,
                ]);
            }else{
                return response()->json(['error' => "Échec lors de l'enregistrement de l'image"], 422);

            }
        }

        if ($request->hasFile('files')) {
            $files = $request["files"];
            foreach($files as $file){
                // Générer un nom aléatoire pour l'image
                $fileName = Str::random(10) . '.' . $file->getClientOriginalExtension();

                // Enregistrer l'image dans le dossier public/images
                $filePath = $file->move(public_path('ressources_images'), $fileName);

                if ($filePath) {
                    // Créer la nouvelle catégorie de média
                    $doc = RessourceImage::create([
                        'name' => $fileName,
                        'url' => 'ressources_images/' . $fileName,
                        'slug' => Str::random(8),
                        'post_id' => $data->id,
                    ]);

                }
            }
        }

        return response()->json(['message' => 'Post créé avec succès', 'data' => $data], 200);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Post::where("id",$id)->with('toutesSousCategories')->first();

        if (!$data) {
            return response()->json(['message' => 'Post non trouvé'], 404);
        }

        if ($data->is_deleted) {
            return response()->json(['message' => 'Post supprimé'], 404);
        }

        // Démarre la traduction si nécessaire
        $data->dispatchTranslationJob();

        return response()->json(['message' => 'Post trouvé', 'data' => $data], 200);
    }

    public function showBySlug($slug)
    {
        $data = Post::where("slug",$slug)->with('toutesSousCategories')->first();

        if (!$data) {
            return response()->json(['message' => 'Post non trouvé'], 404);
        }

        if ($data->is_deleted) {
            return response()->json(['message' => 'Post supprimé'], 404);
        }

        return response()->json(['message' => 'Post trouvé', 'data' => $data], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Vérifier que les champs obligatoires sont remplis
        $validator = Validator::make($request->all(), [
            'titre' => 'nullable|string|max:255',
            'parent' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'date' => 'nullable|date',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'lien' => 'nullable|string',
            'email' => 'nullable|string|email|max:255',
            'contenu' => 'nullable|string',
            'htmlOne' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }

        $data = Post::where("id", $id)->where("is_deleted",false)->first();
        $parent = Post::where("id", $request->parent)->where("is_deleted",false)->first();

        if (!$data) {
            return response()->json(['message' => 'Post non trouvée'], 404);
        }

        $data->update([
            'titre' => $request->input('titre'),
            'contenu' => $request->input('contenu'),
            'htmlOne' => $request->input('htmlOne'),
            'email' => $request->input('email'),
            'date' => $request->input('date'),
            'date_debut' => $request->input('date_debut'),
            'date_fin' => $request->input('date_fin'),
            'lien' => $request->input('lien'),
            'slug' => Str::slug($request->titre),
        ]);

        if($parent){
            $data->update([
                'parent_id' => $parent->id,
            ]);
        }

        if ($request->hasFile('image')) {
            // Générer un nom aléatoire pour l'image
            $imageName = Str::random(10) . '.' . $request->image->getClientOriginalExtension();

            // Enregistrer l'image dans le dossier public/images
            $imagePath = $request->image->move(public_path('posts'), $imageName);

            if ($imagePath) {
                //Storage::delete($data->image);
                File::delete(public_path($data->image));
                $data->update([
                    'image' => 'posts/' . $imageName,
                ]);

            }
        }

        if ($request->hasFile('files')) {
            $files = $request["files"];
           // dd($files);
            foreach($files as $file){
               // dd($file);
                // Générer un nom aléatoire pour l'image
                $fileName = Str::random(10) . '.' . $file->getClientOriginalExtension();

                // Enregistrer l'image dans le dossier public/images
                $filePath = $file->move(public_path('ressources_images'), $fileName);

                if ($filePath) {
                    // Créer la nouvelle catégorie de média
                    $doc = RessourceImage::create([
                        'name' => $fileName,
                        'url' => 'ressources_images/' . $fileName,
                        'slug' => Str::random(8),
                        'post_id' => $data->id,
                    ]);

                }
            }
            //return response()->json(['message' => 'Fichiers ajoutés avec succès'], 200);

        }

        // Relancer la traduction
        $data->dispatchTranslationJob();

        return response()->json(['message' => 'Post modifié avec succès', 'data' => $data], 200);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Trouver la catégorie de maison à supprimer
        $data = Post::where("id",$id)->where("is_deleted",false)->first();
        if (!$data) {
            return response()->json(['message' => 'Post non trouvée'], 404);
        }


        // Supprimer la catégorie de maison
        $data->update(["is_deleted" => true]);

        return response()->json(['message' => 'Post supprimée avec succès']);
    }
}
