<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use App\Http\Requests\StoreMarcaRequest;
use App\Http\Requests\UpdateMarcaRequest;
use App\Repositories\MarcaRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
    public function index(Request $request)
    {
       
        $marcaRepository = new MarcaRepository($this->marca);
       
        if($request->has('atributos_modelos')){

            $atributos_modelos = 'modelos:id,'.$request->atributos_modelos;       
            $marcaRepository->selectAtributosRegistrosRelacionados($atributos_modelos);

        } else {
            $marcaRepository->selectAtributosRegistrosRelacionados('modelos');
        }


        if($request->has('filtro')){
            $marcaRepository->filtro($request->filtro);            
        }


        if($request->has('atributos')) {
            $marcaRepository->selectAtributos($request->atributos);
        } 

        return response()->json($marcaRepository->getResultado(), 200);

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
        $imagem_urn = $imagem->store('imagens/marcas', 'public');
        
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
        $marca = $this->marca->with('modelos')->find($id);
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
        $imagem_urn = $imagem->store('imagens/marcas', 'public');

        $marca->fill($request->all());
        $marca->imagem = $imagem_urn;
        $marca->save();

        // $marca->update([
        //     'nome'      => $request->nome,
        //     'imagem'    => $imagem_urn
        // ]);
        
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
