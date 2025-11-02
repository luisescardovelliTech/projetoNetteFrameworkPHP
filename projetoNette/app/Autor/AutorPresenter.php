<?php

namespace App\Autor;

use Nette;

use App\Model\Modelautor\Modelautor;
use App\Core\ApiPresenter;
use Nette\Application\BadRequestException;

final class AutorPresenter extends ApiPresenter
{
    public function __construct(private Modelautor $modelo)
    {
        parent::__construct();
    }

    public function acaoPadrao(?int $id = null): void
    {
        $metodoHttp = $this->getHttpRequest()->getMethod();

        if ($id) {
            switch ($metodoHttp) {
                case 'GET':
                    $this->buscarUm($id);
                    break;
                case 'PUT':
                    $this->atualizaAutor($id);
                    break;
                case 'DELETE':
                    $this->deletaAutor($id);
                default:
                    $this->sendJson(['erro' => 'Método não permitido'], 405);
            }
        } else {
            switch ($metodoHttp) {
                case 'GET':
                    $this->listarTodos();
                    break;
                case 'POST':
                    $this->criarAutor();
                default:
                    $this->sendJson(['erro' => 'Método não permitido'], 405);
            }
        }

    }

    private function listarTodos()
    {
        $this->sendJson($this->modelo->listarAutores()->fetchAll());
    }

    private function buscarUm(int $id): void
    {
        $autor = $this->modelo->buscarAutor($id);
        if ($autor){
            $this->sendJson($autor);
        } else{
            $this->sendJson(['erro' => 'Autor não encontrado'], 404);
        }
    }

    private function criarAutor(): void
    {
        $dados = $this->getJsonBody();

        if(empty($dados['nome']) || empty($dados['email'])){
            throw new BadRequestException('Campos "nome" e "email" são obrigatórios.', 400);
        }

        $novoAutor = $this->modelo->criarAutor($dados);
        $this->sendJson($novoAutor, 201); // 201 criado
    }

    private function atualizaAutor(int $id): void
    {
        if(!$this->modelo->buscarAutor($id)){
            $this->sendJson(['erro' =>'Autor não encontrado'], 404);
            return;
        }

        $dados =$this->getJsonBody();
        $this->modelo->atualizaAutor($id, $dados);
        $this->sendJson($this->modelo->buscarAutor($id));

    }

    private function deletaAutor(int $id): void
    {
        if (!$this->modelo->buscarAutor($id)){
            $this->sendJson(['erro' => 'Autor não encontrado'], 404);
            return;
        }

        $this->modelo->deletaAutor($id);
        $this->sendJson(null, 204);
    }

}
