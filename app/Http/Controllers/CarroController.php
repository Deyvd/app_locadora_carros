<?php

namespace App\Http\Controllers;

use App\Models\Carro;
use App\Http\Requests\StoreCarroRequest;
use App\Http\Requests\UpdateCarroRequest;
use App\Repositories\CarroRepository;
use Illuminate\Http\Request;

class CarroController extends Controller
{
    public function __construct(Carro $carro)
    {
        $this->carro = $carro;        
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $carroRepository = new CarroRepository($this->carro);
        
        if($request->has('atributos_modelo')){

            $atributos_modelo = 'modelo:id,'.$request->atributos_modelo;       
            $carroRepository->selectAtributosRegistrosRelacionados($atributos_modelo);

        } else {
            $carroRepository->selectAtributosRegistrosRelacionados('modelo');

        }


        if($request->has('filtro')){
            $carroRepository->filtro($request->filtro);            
        }


        if($request->has('atributos')) {
            $carroRepository->selectAtributos($request->atributos);
        } 

        return response()->json($carroRepository->getResultado(), 200);
        
    }

   
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCarroRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCarroRequest $request)
    {
        $request->validate($this->carro->rules());
        
        $carro = $this->carro->create($request->all());

        return $carro;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Carro  $carro
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $carro = $this->carro->with('modelo')->find($id);
        if( $carro === null ) {
            return response()->json(['erro' => 'O recurso pesquisado não existe'], 404);
        }
        return $carro;
    }

    
    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCarroRequest  $request
     * @param  \App\Models\Carro  $carro
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCarroRequest $request, $id)
    {
        $carro = $this->carro->find($id);

        if( $carro === null ) {

            return response()->json(['erro' => 'Impossível realizar a atualização. O recurso solicitado não existe'], 404);

        }

        if ($request->method() === 'PATCH') {

            // return ['teste' => 'verbo PATCH'];

            $regrasDinamicas = [];

            foreach($carro->rules() as $input => $rule) {

                if( array_key_exists($input, $request->all())){
                    $regrasDinamicas[$input] = $rule;                    
                }
            }

            $request->validate($regrasDinamicas);

        } else {

            $request->validate($this->carro->rules());

        }


        $carro->fill($request->all());
        $carro->save();
        
        return $carro;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Carro  $carro
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $carro = $this->carro->find($id);
       

        if( $carro === null ) {
            return response()->json(['erro' => 'O carro selecionado não existe'], 404);
        }

        $carro->delete();
        return response()->json(['msg'=>'O carro foi removido com Sucesso'], 200);
      
    }
}
