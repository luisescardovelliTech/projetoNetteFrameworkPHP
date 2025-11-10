<?php

namespace App\Presentation\Posts;

use Nette;
use App\Model\Modelposts;
use App\Core\ApiPresenter;
use Nette\Application\BadRequestException;

final class PostsPresenter extends ApiPresenter
{
    public function __construct(
        private Modelposts $modelo
    ) {
        parent::__construct();
    }

    public function acaoPadrao(?int $id = null): void
    {


        $metodoHttp = $this->getHttpRequest()->getMethod();

        if ($id) {
            // Rotas com ID
            switch ($metodoHttp) {
                case 'GET':
                    $this->buscarPosts($id);
                    break;
                case 'PUT':
                    $this->atualizaPosts($id);
                    break;
                case 'DELETE':
                    $this->deletaPosts($id);
                    break;
                default:
                    $this->sendJson(['erro' => 'Método não permitido'], 405);
            }
        } else {
            // Rotas sem ID
            switch ($metodoHttp) {
                case 'GET':
                    $this->listarTodos();
                    break;
                case 'POST':
                    $this->critaPosts();
                    break;
                default:
                    $this->sendJson(['erro' => 'Método não permitido'], 405);
            }
        }
    }

    private function listarTodos(): void
    {

        $this->sendJson($this->modelo->listarPosts()->fetchAll());
    }

    private function buscarPosts(int $id): void
    {
        $post = $this->modelo->buscarPost($id);
        if ($post) {
            $this->sendJson($post);
        } else {
            $this->sendJson(['erro' => 'Post não encontrado'], 404);
        }
    }

    private function critaPosts(): void
    {
        $dados = $this->getJsonBody();

        if (empty($dados['titulo'])) {
            throw new BadRequestException('O campo "titulo" é obrigatório.', 400);
        }
        if (empty($dados['autores_idautores'])) {
            throw new BadRequestException('O campo "autores_idautores" (ID do autor) é obrigatório.', 400);
        }
        if (empty($dados['categoria_idcategoria'])) {
            throw new BadRequestException('O campo "categoria_idcategoria" (ID da categoria) é obrigatório.', 400);
        }

        $novoPost = $this->modelo->critaPostsPost($dados);
        $postCompleto = $this->modelo->buscarPost($novoPost->idposts);
        $this->sendJson($postCompleto, 201); // 201 Created
    }

    private function atualizaPosts(int $id): void
    {
        if (!$this->modelo->buscarPost($id)) {
            $this->sendJson(['erro' => 'Post não encontrado'], 404);
            return;
        }

        $dados = $this->getJsonBody();
        $this->modelo->atualizaPost($id, $dados);

        $this->sendJson($this->modelo->buscarPost($id));
    }

    private function deletaPosts(int $id): void
    {
        if (!$this->modelo->buscarPost($id)) {
            $this->sendJson(['erro' => 'Post não encontrado'], 404);
            return;
        }

        $this->modelo->deletaPost($id);
        $this->sendJson(null, 204);
    }
}
