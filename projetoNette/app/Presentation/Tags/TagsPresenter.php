<?php

namespace App\Presentation\Tags;

use Nette;
use App\Core\ApiPresenter;
use App\Model\Modeltags;
use Nette\Application\BadRequestException;

final class TagsPresenter extends ApiPresenter
{
    public function __construct(Modeltags $modelo)
    {
        parent::__construct();
    }

    public function acaoPadrao(?int $id = null): void
    {
        $metodoHttp = $this->getHttpRequest()->getMethod();

        if ($id) {
            switch ($metodoHttp) {
                case 'GET':
                    $this->buscarTags($id);
                    break;
                case 'PUT':
                    $this->atualizaTags($id);
                    break;
                case 'DELETE':
                    $this->deletaTags($id);
                default:
                    $this->sendJson(['erro' => 'Método não permitido'], 405);
            }
        } else {
            switch ($metodoHttp) {
                case 'GET':
                    $this->listarTodos();
                    break;
                case 'POST':
                    $this->criaTags();
                default:
                    $this->sendJson(['erro' => 'Método não permitido'], 405);
            }
        }
    }

    private function listarTodos()
    {
        $this->sendJson($this->modelo->listarTodos()->fetchAll);
    }

    private function buscarTags(int $id): void
    {
        $tag = $this->modelo->buscarTags($id);

        if ($tag) {
            $this->sendJson($tag);
        } else {
            $this->sendJson(['erro' => 'Tag não encontrada'], 404);
        }
    }

    private function criaTags(): void
    {
        $dados = $this->getJsonBody();

        if (empty($dados['nome'])) {
            throw new BadRequestException('Campo "nome" é obrigatório.', 400);
        }

        $novaTag = $this->modelo->criaTag($dados);
        $this->sendJson($novaTag, 201); // criado
    }

    private function atualizaTags(int $id): void
    {
        if (!$this->modelo->buscarTags($id)) {
            $this->sendJson(['erro' => 'Tag não encontrada'], 404);
            return;
        }
        $dados = $this->getJsonBody();
        $this->modelo->atualizaTags($id, $dados);
        $this->sendJson($this->modelo->buscarTags($id));
    }

    private function deletaTags(int $id): void
    {
        if(!$this->modelo->buscarTags($id)){
            $this->sendJson(['erro' => 'Tag não encontrada'], 404);
            return;
        }
    }
}
