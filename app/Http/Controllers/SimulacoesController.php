<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;

class SimulacoesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), $this->_rules());

            if ($validator->fails()) {
                return response()->json($validator->messages(), 400);
            }

            $emprestimo = [];
            $json_file = file_get_contents(\Storage::disk('s3')->url('taxas_instituicoes.json'));
            $taxas = collect(json_decode($json_file, true))->when($request->instituicoes, function($q) use($request){
                return $q->whereIn('instituicao', $request->instituicoes);
            })->when($request->convenios, function($q) use($request){
                return $q->whereIn('convenio', $request->convenios);
            })->when($request->parcela, function($q) use($request){
                return $q->where('parcelas', $request->parcela);
            })->groupBy('instituicao');
                        
            foreach($taxas as $key => $taxa){

                $emprestimo[$key] = [];

                foreach($taxa as $dados){
                    $valor_parcela = $dados['coeficiente'] * $request->valor_emprestimo;
                    array_push($emprestimo[$key], [
                        'taxa' => $dados['taxaJuros'],
                        'parcelas' => $dados['parcelas'],
                        'valor_parcela' => $valor_parcela,
                        'convenio' => $dados['convenio']
                    ]);
                }
            }

            return response()->json($emprestimo);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    private function _rules()
    {
        return [
            'valor_emprestimo' => 'required|numeric',
            'instituicoes' => 'array',
            'convenios' => 'array',
            'parcela' => 'numeric'
        ];
    }
}
