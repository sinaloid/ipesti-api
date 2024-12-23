<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Type;
use App\Models\CategorieType;

class TypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Type::with("categorieType")->where("is_deleted", false)->get();

        if ($data->isEmpty()) {
            return response()->json(['message' => 'Aucune types trouvée'], 404);
        }

        return response()->json(['message' => 'Types récupérées', 'data' => $data], 200);
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

        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:255',
            'categorie' => 'required|string|max:8',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }
        
        $categorie = CategorieType::where("slug",$request->categorie)->first();

        if (!$categorie) {
            return response()->json(['message' => 'Catégorie non trouvée'], 404);
        }

        $data = Type::create([
            'label' => $request->input('label'),
            'description' => $request->input('description'),
            'categorie_type_id' => $categorie->id,
            'slug' => Str::random(8),
        ]);

        if ($data) {
            return response()->json(['message' => 'Type créé avec succès', 'data' => $data], 200);
        }

        return response()->json(['error' => 'Échec lors de la création'], 422);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $data = Type::where("slug",$slug)->first();

        if (!$data) {
            return response()->json(['message' => 'Type non trouvée'], 404);
        }

        if ($data->is_deleted) {
            return response()->json(['message' => 'Type supprimée'], 404);
        }

        return response()->json(['message' => 'Type trouvée', 'data' => $data], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        // Vérifier que les champs obligatoires sont remplis
        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:255',
            'categorie' => 'required|string|max:8',
            'description' => 'nullable|string|max:1000',
        ]);
        
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }

        $categorie = CategorieType::where('slug',$request->categorie)->where("is_deleted",false)->first();
        
        if (!$categorie) {
            return response()->json(['message' => 'Catégorie non trouvée'], 404);
        }

        $data = Type::where("slug", $slug)->where("is_deleted",false)->first();

        if (!$data) {
            return response()->json(['message' => 'Type non trouvée'], 404);
        }

        $data->update([
            'label' => $request->input('label'),
            'description' => $request->input('description'),
            'categorie_type_id' => $categorie->id,
        ]);

        return response()->json(['message' => 'Type modifié avec succès', 'data' => $data], 200);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        // Trouver la catégorie de maison à supprimer
        $data = Type::where("slug",$slug)->where("is_deleted",false)->first();
        if (!$data) {
            return response()->json(['message' => 'Type non trouvée'], 404);
        }


        // Supprimer la catégorie de maison
        $data->update(["is_deleted" => true]);

        return response()->json(['message' => 'Type supprimé avec succès']);
    }
}