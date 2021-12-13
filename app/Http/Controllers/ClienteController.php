<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Carro;
use App\Repositories\ClienteRepository;
use Illuminate\Http\Request;

class ClienteController extends Controller
{

    public function __construct(Cliente $cliente)
    {
        $this->cliente = $cliente;        
    }

    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $clientRepository = new ClienteRepository($this->client);   
        
        if($request->has('filtro')){
            $clientRepository->filtro($request->filtro);            
        }

        if($request->has('atributos')) {
            $clientRepository->selectAtributos($request->atributos);
        } 

        return response()->json($clientRepository->getResultado(), 200);
     
    }

   
   

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreClienteRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreClienteRequest $request)
    {
        $request->validate($this->cliente->rules());
        
        $cliente = $this->cliente->create($request->all());

        return $cliente;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Cliente  $cliente
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cliente = $this->cliente->find($id);
        if( $cliente === null ) {
            return response()->json(['erro' => 'O recurso pesquisado não existe'], 404);
        }
        return $cliente;
    }

   

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateClienteRequest  $request
     * @param  \App\Models\Cliente  $cliente
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateClienteRequest $request, $id)
    {
        $cliente = $this->cliente->find($id);

        if( $cliente === null ) {

            return response()->json(['erro' => 'Impossível realizar a atualização. O recurso solicitado não existe'], 404);

        }

        if ($request->method() === 'PATCH') {

            // return ['teste' => 'verbo PATCH'];

            $regrasDinamicas = [];

            foreach($cliente->rules() as $input => $rule) {

                if( array_key_exists($input, $request->all())){
                    $regrasDinamicas[$input] = $rule;                    
                }
            }

            $request->validate($regrasDinamicas);

        } else {

            $request->validate($this->cliente->rules());

        }


        $cliente->fill($request->all());
        $cliente->save();
        
        return $cliente;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cliente  $cliente
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cliente = $this->cliente->find($id);
       

        if( $cliente === null ) {
            return response()->json(['erro' => 'O cliente selecionado não existe'], 404);
        }

        $cliente->delete();
        return response()->json(['msg'=>'O cliente foi removido com Sucesso'], 200);
      
    }
}
