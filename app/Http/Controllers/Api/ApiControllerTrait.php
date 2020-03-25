<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Validator;

use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


trait ApiControllerTrait
{

    /**
     * <b>index</b>: Método responsável em listar os registros de um recurso retfull que use esta TRAIT.
     * Com este método é possível utilizar as seguintes operações em SQL:
     * ORDERBY, LIKE, WHERE, ASC, DESC, e JOIN(com o uso do metodo relationships), LIMIT
     * Veja abaixo como utilizar as operações descritas acima:
     * 
     * http://www.apirestfull/api/entidades?order=id,desc
     * http://www.apirestfull/api/entidades?like=title,abc
     * http://www.apirestfull/api/entidades?where[id]=107
     * http://www.apirestfull/api/entidades?order=id,asc
     * http://www.apirestfull/api/entidades?limit=25
     * 
     * OBS: Todas as operações descritas acima, deverão usar o verbo GET(HTTP)
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $resquest)
    {
        //limita a quantidade de itens por pagina pega 15 por padrão 
        $limit = $request->all()['limit'] ?? 15;
        
        //possibilita a ordenação de itens 
        $order = $request->all()['order'] ?? null;

        //Caso o parametro order não seja nulo 
        if ($order !== null) {
            $order = explode(',', $order);
        }

        //Após da o explode lá em cima, cria dois indice caso os mesmos não seja informado,eles pegam um valor padrão 
        $order[0] = $order[0] ?? $this->model->getPrimaryKey();
        $order[1] = $order[1] ?? 'asc';

        //verifica se tem o parametro where na url
        $where = $request->all()['where'] ?? [];

        //verifica se foi passado like na url para pesquisar algo
        $like = $request->all()['like'] ?? null;

        if ($like) {
            //like[0] = campo like[1] = condição
            $like = explode(',', $like);
            $like[1] = '%' . $like[1] . '%';
        }

        $class = $this->model->collection;
        $result = $class($this->model->orderBy($order[0], $order[1])
            ->where(function ($query) use ($like) {
                if ($like) {
                    return $query->where($like[0], 'like', $like[1]);
                }
                return $query;
            })
            ->where($where)
            ->with($this->relationships()) //metodo responsável por verificar e trazer dados de relacionamento entre tabelas
            ->paginate($limit));

        return $this->createResponse($result);
    }

    /**
     * <b>store</b>Método responsável em gravar os registros de um recurso restfull que use esta TRAIT.
     * Para utilização da mesma deve ser realizado um POST para a URL do recurso,
     * passando os campos e seus valores, veja abaixo um exemplo de URL:
     * 
     * http://www.apirestfull/api/entidades
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $values = $this->columnsInsert($request);

        $validate = validator($values, $this->model->rules, $this->model->messages);

        if ($validate->fails()) {
            $errors['messages'] = $this->columnsShow($validate->errors());
            $errors['error']    = true;

            return $this->createResponse($errors, 422);
        }

        $result = $this->model->create($values);

        return $this->createResponse($this->columnsShow($result), 201);
    }


    /**
     * <b>show</b>Método responsável em listar um  registro especifico de um recurso restfull que use esta TRAIT.
     * Para utilização deste método deve ser realizado, deverá ser feito um GET(HTTP) para a URL do recurso,
     * passando o id do registro desejado, veja abaixo um exemplo de URL:
     *  http://www.apirestfull/api/entidades/1
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $class = $this->model->resource;
        $result = new $class($this->model->with($this->relationships())
            ->findOrFail($id));

        return  $this->createResponse($result);
    }


    /**
     * <b>update</b>Método responsável em atualizar um  registro especifico de um recurso retfull que use esta TRAIT.
     * Para utilização deste método deve ser realizado, deverá ser feito um PUT(HTTP) para a URL do recurso,
     * passando o id do registro desejado, veja abaixo um exemplo de URL:
     * http://www.apirestfull/api/entidades/1
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $result = $this->model->findOrFail($id);
        $values = $this->columnsInsert($request);

        $validate = validator($values, $this->model->rules, $this->model->messages);

        if ($validate->fails()) {
            $errors['messages'] = $this->columnsShow($validate->errors());
            $errors['error']    = true;

            return $this->createResponse($errors, 422);
        }

        $result->update($values);
        return $this->createResponse($this->columnsShow($result));
    }

    /**
     * <b>destroy</b>Método responsável em excluir um  registro especifico de um recurso retfull que use esta TRAIT.
     * Para utilização deste método deve ser realizado, deverá ser feito um DELETE(HTTP) para a URL do recurso,
     * passando o id do registro desejado, veja abaixo um exemplo de URL:
     * http://www.apirestfull/api/entidades/1
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = $this->model->findOrFail($id);
        $result->delete();
        return $this->createResponse($this->columnsShow($result), 202);
    }



    /**
     * <b>relationships</b> Metodo responsável por verificar se a controller que esta fazendo requisição existe o atributo 
     * relationships com conteudo. Caso tenha realiza o relacionamentos)(join) De acordo com as especificações da model
     * o mesmo é realizado por meio do metodo with() informado na consulta, que utiliza este método.
     * @return Array 
     */
    protected function relationships()
    {
        if (isset($this->relationships)) {
            return $this->relationships;
        }
        return [];
    }


    public function validateInputs(Request $request)
    {
        $values = $this->columnsInsert($request);

        $validate = Validator::make($values, $this->model->rules, $this->model->messages);

        if ($validate->fails()) {
            $errors['messages'] = $this->columnsShow($validate->errors());
            $errors['error']    = true;

            return $this->createResponse($errors, 422);
        }

        return $this->createResponse($validate);
    }

    /**
     * <b>columnsInsert:<b/> Pega o alias(apelido das colunas para o mundo externo) e inverte para o nome da coluna no banco de dados
     * Converte os nomes a serem apresentado para o mundo externo para os nomes das colunas da tabela.
     * exemplo vara => nm_vara aonde vara é o nome conhecido para o mundo externo e nm_vara é o nome da coluna na tabela.
     * @param  \Illuminate\Http\Request  $request
     * @return $columnsAndValues 
     */

    protected function columnsInsert(Request $request)
    {

        $columnsModel = $this->model->map;
        $columnsRequest = array_keys($request->all());
        $columnsAndValues = [];

        foreach ($columnsRequest as $column) {


            //se existir a columa enviada na requisição no mapeamento
            if (array_key_exists($column, $columnsModel) && !is_a($request->$column, UploadedFile::class)) {

                if (array_key_exists($column, $request->all())) {
                    $columnInsert = $columnsModel[$column];
                    $valueInsert = $request->input($column);
                    $columnsAndValues[$columnInsert] = $valueInsert;
                }
            } else {
                if (array_key_exists($column, $columnsModel)) {
                    $columnInsert = $columnsModel[$column];
                    $valueInsert = $request->file($column);
                    $columnsAndValues[$columnInsert] = $valueInsert;
                }
            }
        }

        return $columnsAndValues;
    }



    /**
     * <b>collumnsShow</b> Converte os nomes das colunas da tabela para os nomes das a serem apresentado para o mundo externo.
     * exemplo nm_vara => vara aonde nm_vara é o nome da coluna na tabela e vara é o nome a ser apresentado ao retornar o dado.
     * @param  \Illuminate\Http\Request  $result
     * @return $columnsAndValues 
     */
    protected function columnsShow($result)
    {
        $response = (Object) $result;
        $arrayResult = $response->toArray($result);
        $columnsResult = array_keys($arrayResult);
        $columnsModel = $this->model->map;
        $columnsAndValues = [];

        foreach ($columnsResult as $column) {
            //se existir a columa enviada na requisição no mapeamento
            if (in_array($column, $columnsModel)) {
                //retorna o indice do array
                $columnResult = array_search($column, $columnsModel);
                $valueResult = $arrayResult[$column];
                $columnsAndValues[$columnResult] = $valueResult;
            }
        }

        return $columnsAndValues;
    }



    /**
     * <b>createResponse</b> Retorna o response em Json no formato: Resposta{conteudo[]}
     * Padronizando a apresentação dos dados e o formato do mesmo, para o mundo "externo"
     * OBS: Pode receber um objeto do Eloquent que herda da classe Illuminate\Pagination\AbstractPaginator
     * @param   $result
     * @return \Illuminate\Http\Response
     */

    protected function createResponse($result, $statusCode = null)
    {

        $response = (Object)$result;
        $statusCode = is_null($statusCode) ? app('Illuminate\Http\Response')->status() : $statusCode;


        if (property_exists($response, 'collects')) {

            $data['content']       = (Array)$result->items();
            $data['status']         = $statusCode; //alterar a atribuição de status
            $data['code']           = 01; //alterar a atribuição de code

            //Atributos da classe de paginação
            $data['links'] = [

                'first_page_url' => $response->url(1),
                'last_page_url'  => $response->url($response->lastPage()),
                'next_page_url'  => $response->nextPageUrl(),
                'prev_page_url'  => $response->previousPageUrl(),

            ];
            $data['meta'] = [

                'current_page'   => $response->currentPage(),
                'from'           => $response->firstItem(),
                'last_page'      => $response->lastPage(),
                'path'           => $response->resolveCurrentPath(),
                'per_page'       => $response->perPage(),
                'to'             => $response->lastItem(),
                'total'          => $response->total(),
            ];
        } else {
            $data['content']       = $response;
            $data['status']         = $statusCode; //alterar a atribuição de status
            $data['code']           = 01; //alterar a atribuição de code

        }

        //retorna os dados em formato json
        return response()
            ->json(['response' => $data], $statusCode, [], JSON_UNESCAPED_UNICODE)
            ->setStatusCode($statusCode);
    }

}