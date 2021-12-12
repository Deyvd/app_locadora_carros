<?php

namespace App\Http\Controllers;

use App\Models\Modelo;
use App\Http\Requests\StoreModeloRequest;
use App\Http\Requests\UpdateModeloRequest;
use Illuminate\Support\Facades\Storage;

class ModeloController extends Controller
{
    public function __construct(Modelo $modelo){

       
        $this->modelo = $modelo;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $modelos = $this->modelo->all();
        $modelos = $this->modelo->with('marca')->get();

        // return $modelos;

        return response()->json($modelos, 200);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreModeloRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreModeloRequest $request)
    {
        $request->validate($this->modelo->rules());

        $nome = $request->nome;

        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens/modelos', 'public');
        
        $modelo = $this->modelo->create([
            'marca_id'      =>$request->marca_id, 
            'nome'          => $nome,
            'imagem'        => $imagem_urn,
            'numero_portas' =>$request->numero_portas,
            'lugares'       =>$request->lugares,
            'air_bag'       =>$request->air_bag,
            'abs'           =>$request->abs
        ]);

        return $modelo;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Modelo  $modelo
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $modelo = $this->modelo->with('marca')->find($id);
        if( $modelo === null ) {
            return response()->json(['erro' => 'O recurso pesquisado não existe'], 404);
        }
        return $modelo;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateModeloRequest  $request
     * @param  \App\Models\Modelo  $modelo
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateModeloRequest $request, $id)
    {

        $modelo = $this->modelo->find($id);

        if( $modelo === null ) {

            return response()->json(['erro' => 'Impossível realizar a atualização. O recurso solicitado não existe'], 404);

        }

        if ($request->method() === 'PATCH') {

            // return ['teste' => 'verbo PATCH'];

            $regrasDinamicas = [];

            foreach($modelo->rules() as $input => $rule) {

                if( array_key_exists($input, $request->all())){
                    $regrasDinamicas[$input] = $rule;                    
                }
            }

            $request->validate($regrasDinamicas);

        } else {

            $request->validate($this->modelo->rules());

        }

        if ($request->file('imagem')){
            Storage::disk('public')->delete($modelo->imagem);
        }

        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens/modelos', 'public');
        

        $modelo->update([
            'marca_id'      =>$request->marca_id, 
            'nome'          => $request->nome,
            'imagem'        => $imagem_urn,
            'numero_portas' =>$request->numero_portas,
            'lugares'       =>$request->lugares,
            'air_bag'       =>$request->air_bag,
            'abs'           =>$request->abs
        ]);
        
        return $modelo;
       
       
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Modelo  $modelo
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $modelo = $this->modelo->find($id);
       

        if( $modelo === null ) {
            return response()->json(['erro' => 'O modelo selecionado não existe'], 404);
        }
 
        Storage::disk('public')->delete($modelo->imagem);

        $modelo->delete();
        
        return response()->json(['msg'=>'O modelo foi removido com Sucesso'], 200);
        
    }
}
