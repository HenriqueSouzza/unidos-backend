<?php

namespace Modules\AtividadesComplementares\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Http\Controllers\Api\ApiControllerTrait;

class AlunosInscritosController extends Controller
{
    
    /**
     * <b>use ApiControllerTrait</b> Usa a trait e sobreescreve os seus nomes e sua visibilidade, para a classe
     * que esta utilizando a mesma. Sendo assim temos um método index neste classe e um na ApiControllerTrait. 
     * Para não causar conflito é alterado o seu nome exemplo: index as protected indexTrait;
     * Mais informações em: http://php.net/manual/en/language.oop5.traits.php (Changing Method Visibility)
    */
    use ApiControllerTrait
    {

        index as protected indexTrait;
        store as protected storeTrait;
        show as protected showTrait;
        update as protected updateTrait;
        destroy as protected destroyTrait;
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->indexTrait($request);
    }
    

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('atividadescomplementares::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('atividadescomplementares::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('atividadescomplementares::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
