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

    public function actionDefault(?int $id = null): void
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
                    $this->listarPosts();
                    break;
                case 'POST':
                    $this->criaPosts();
                    break;
                default:
                    $this->sendJson(['erro' => 'Método não permitido'], 405);
            }
        }
    }

    private function listarPosts(): void
    {

        $postsDb = $this->modelo->listarPosts()->fetchAll();
        $resultado = [];
        foreach ($postsDb as $post) {
            $resultado[] = $post->toArray();
        }
        $this->sendJson($resultado);
    }

    private function buscarPosts(int $id): void
    {
        $post = $this->modelo->buscarPosts($id);
        if ($post) {
            $this->sendJson($post->toArray());
        } else {
            $this->sendJson(['erro' => 'Post não encontrado'], 404);
        }
    }

    private function criaPosts(): void
    {
        $dados = $this->getJsonBody();

        // $this->sendJson(['tags_recebidas' => $dados['tags'] ?? 'Nenhuma tag enviada']);

        // extrai as tags
        $tagIds = $dados['tags'] ?? [];
        unset($dados['tags']);

        if (empty($dados['titulo']) || empty($dados['autores_idautores']) || empty($dados['categoria_idcategoria'])) {
            throw new BadRequestException('Campos titulo, autor e categoria são obrigatórios.', 400);
        }
        // cria o post
        $novoPostId = $this->modelo->criarPost($dados, $tagIds)->idposts;

        $this->sendJson($this->modelo->buscarPosts($novoPostId)->toArray(), 201);
    }

    private function atualizaPosts(int $id): void
    {
        if (!$this->modelo->buscarPosts($id)) {
            $this->sendJson(['erro' => 'Post não encontrado'], 404);
            return;
        }

        $dados = $this->getJsonBody();

        // Verifica se 'tags' foi enviado no JSON
        $tagIds = null;
        if (array_key_exists('tags', $dados)) {
            $tagIds = $dados['tags'];
        }
        unset($dados['tags']);

        $this->modelo->atualizaPost($id, $dados, $tagIds);
        $this->sendJson($this->modelo->buscarPosts($id)->toArray());
    }

    private function deletaPosts(int $id): void
    {
        if (!$this->modelo->buscarPosts($id)) {
            $this->sendJson(['erro' => 'Post não encontrado'], 404);
            return;
        }
        $this->modelo->deletaPost($id);
        $this->sendJson(null, 204);
    }

    /**
     * NOVO ENDPOINT: POST /posts/upload
     * Recebe um arquivo via 'multipart/form-data' e retorna a URL.
     */
    public function actionUpload(): void
    {
        if (!$this->getHttpRequest()->isMethod('POST')) {
             $this->sendJson(['erro' => 'Método não permitido. Use POST.'], 405);
        }

        $file = $this->getHttpRequest()->getFile('imagem');

        if (!$file || !$file->isOk()) {
            $this->sendJson(['erro' => 'Nenhum arquivo enviado ou falha no upload.'], 400);
            return;
        }

        if (!$file->isImage()) {
            $this->sendJson(['erro' => 'O arquivo deve ser uma imagem (jpg, png, gif).'], 400);
            return;
        }

        $extensao = pathinfo($file->getSanitizedName(), PATHINFO_EXTENSION);
        $novoNome = uniqid() . '.' . $extensao;
        // Define o caminho onde vamos salvar (na pasta www/uploads)
        $caminhoDestino = __DIR__ . '/../../../www/uploads/' . $novoNome;

try {
            // Move o arquivo
            $file->move($caminhoDestino);

            // ... (geração da URL) ...
            $urlPublica = 'http://localhost/projetoNette/projetoNette/www/uploads/' . $novoNome;

            // Retorna a URL (ISTO LANÇA UMA AbortException!)
            $this->sendJson(['url' => $urlPublica], 201);

        } catch (Nette\Application\AbortException $e) {
            // SOLUÇÃO: Se for a exceção de "abortar" (sucesso),
            // nós relançamo-la para o Nette fazer o seu trabalho.
            throw $e;

        } catch (\Exception $e) {
            // Se for qualquer outro erro REAL, nós capturamos aqui.
            $this->sendJson([
                'erro' => 'Erro ao salvar o arquivo no disco.',
                'detalhe' => $e->getMessage()
            ], 500);
        }
    }
}
