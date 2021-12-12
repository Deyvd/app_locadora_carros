<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use App\Http\Requests\StoreMarcaRequest;
use App\Http\Requests\UpdateMarcaRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\TryCatch;

class MarcaController extends Controller
{

    public function __construct(Marca $marca){

       
        $this->marca = $marca;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        $marcas = $this->marca->all();

        return $marcas;
    }

    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreMarcaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMarcaRequest $request)
    {

        $request->validate($this->marca->rules(), $this->marca->feedback());

        $nome = $request->nome;

        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens/logomarca', 'public');
        
        $marca = $this->marca->create([
            'nome'      => $nome,
            'imagem'    => $imagem_urn
        ]);

        return $marca;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Marca  $marca
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $marca = $this->marca->find($id);
        if( $marca === null ) {
            return response()->json(['erro' => 'O recurso pesquisado não existe'], 404);
        }
        return $marca;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateMarcaRequest  $request
     * @param  \App\Models\Marca  $marca
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMarcaRequest $request, $id)
    {
        $marca = $this->marca->find($id);

        if( $marca === null ) {

            return response()->json(['erro' => 'Impossível realizar a atualização. O recurso solicitado não existe'], 404);

        }

        if ($request->method() === 'PATCH') {

            // return ['teste' => 'verbo PATCH'];

            $regrasDinamicas = [];

            foreach($marca->rules() as $input => $rule) {

                if( array_key_exists($input, $request->all())){
                    $regrasDinamicas[$input] = $rule;                    
                }
            }

            $request->validate($regrasDinamicas, $this->marca->feedback());

        } else {

            $request->validate($this->marca->rules(), $this->marca->feedback());

        }

        if ($request->file('imagem')){
            Storage::disk('public')->delete($marca->imagem);
        }

        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens/logomarca', 'public');
        

        $marca->update([
            'nome'      => $request->nome,
            'imagem'    => $imagem_urn
        ]);
        
        return $marca;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Marca  $marca
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $marca = $this->marca->find($id);
       

        if( $marca === null ) {
            return response()->json(['erro' => 'A marca selecionada não existe'], 404);
        }
 
        Storage::disk('public')->delete($marca->imagem);

        $marca->delete();
        
        return response()->json(['msg'=>'A marca foi removida com Sucesso'], 200);
        
    }
}
