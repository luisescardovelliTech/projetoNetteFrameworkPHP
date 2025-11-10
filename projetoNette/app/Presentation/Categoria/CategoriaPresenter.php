<?php

namespace App\Presentation\Categoria;

use Nette;
use App\Model\Modelcategoria;
use App\Core\ApiPresenter;
use Nette\Application\BadRequestException;

final class CategoriaPresenter extends ApiPresenter
{
    public function __construct(private Modelcategoria $modelo)
    {
        parent::__construct();
    }

    public function acaoPadrao(?int $id = null): void
    {
        $metodoHttp = $this->getHttpRequest()->getMethod();

        if ($id) {
            switch ($metodoHttp) {
                case 'GET':
                    $this->buscarCategoria($id);
                    break;
                case 'PUT':
                    $this->atualizaCategoria($id);
                    break;
                case 'DELETE':
                    $this->deletaCategoria($id);
                default:
                    $this->sendJson(['erro' => 'Método não permitido'], 405);
            }
        } else {
            switch ($metodoHttp) {
                case 'GET':
                    $this->listarTodas();
                    break;
                case 'POST':
                    $this->criaCategoria();
                default:
                    $this->sendJson(['erro' => 'Método não permitido'], 405);
            }
        }
    }


    private function listarTodas(): void
    {
        $this->sendJson($this->modelo->listarCategorias()->fetchAll());
    }

    private function buscarCategoria(int $id): void
    {
        $categoria = $this->modelo->buscarCategoria($id);
        if ($categoria) {
            $this->sendJson($categoria);
        } else {
            $this->sendJson(['erro' => 'Categoria não encontrada'], 404);
        }
    }

    private function criaCategoria(): void
    {
        $dados = $this->getJsonBody();

        if (empty($dados['nome'])) {
            throw new BadRequestException('O campo "nome" é obrigatório.', 400);
        }

        $novaCategoria = $this->modelo->criaCategoria($dados);
        $this->sendJson($novaCategoria, 201);
    }

    private function deletaCategoria(int $id): void
    {
        if (!$this->modelo->buscarCategoria($id)) {
            $this->sendJson(['erro' => 'Categoria não encontrada.'], 404);
        }
        return;

        $this->modelo->deletaCategoria($id);
        $this->sendJson(null, 204);
    }
}
