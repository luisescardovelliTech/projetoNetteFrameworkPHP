<?php

namespace App\Presentation\Tags;

use Nette;
use App\Core\ApiPresenter;
use App\Model\Modeltags;
use Nette\Application\BadRequestException;

final class TagsPresenter extends ApiPresenter
{
    public function __construct(private Modeltags $modelo)
    {
        parent::__construct();
    }

    public function actionDefault(?int $id = null): void
    {
        $metodoHttp = $this->getHttpRequest()->getMethod();

        if ($id) {
            switch ($metodoHttp) {
                case 'GET':
                    $this->buscarTags($id);
                    break;
                case 'PUT':
                    $this->autalizarTags($id);
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
                    $this->criarTags();
                default:
                    $this->sendJson(['erro' => 'Método não permitido'], 405);
            }
        }
    }

    private function listarTodos()
    {
        $tags = $this->modelo->listarTags()->fetchAll();
        $resultado = [];
        foreach ($tags as $tag) {
            $resultado[] = $tag->toArray();
        }
        $this->sendJson($resultado);
    }

    private function buscarTags(int $id): void
    {
        $tag = $this->modelo->buscarTags($id);

        if ($tag) {
            $this->sendJson($tag->toArray());
        } else {
            $this->sendJson(['erro' => 'Tag não encontrada'], 404);
        }
    }

    private function criarTags(): void
    {
        $dados = $this->getJsonBody();
        if (empty($dados['nome'])) {
            throw new BadRequestException('O campo "nome" é obrigatório.', 400);
        }
        $novaTag = $this->modelo->criarTags($dados);
        $this->sendJson($novaTag->toArray(), 201);
    }

    private function autalizarTags(int $id): void
    {
        if (!$this->modelo->buscarTags($id)) {
            $this->sendJson(['erro' => 'Tag não encontrada'], 404);
            return;
        }
        $dados = $this->getJsonBody();
        $this->modelo->autalizarTags($id, $dados);
        $this->sendJson($this->modelo->buscarTags($id));
    }

    private function deletaTags(int $id): void
    {
       if (!$this->modelo->buscarTags($id)) {
            $this->sendJson(['erro' => 'Tag não encontrada'], 404);
            return;
        }
        $this->modelo->deletaTag($id);
        $this->sendJson(null, 204);
    }
}
