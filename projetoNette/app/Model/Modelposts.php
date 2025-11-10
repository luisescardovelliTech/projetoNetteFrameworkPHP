<?php

namespace App\Model;

use Nette;
use Nette\Utils\DateTime;
use Nette\Database\Explorer;

final class Modelposts
{
    use Nette\SmartObject;

    private const NOME_TABELA = 'posts';
    private const TABELA_JUNCAO_TAGS = 'tags_has_posts';
    private const COLUNA_POST_ID_JUNCAO = 'posts_idposts';
    private const COLUNA_TAG_ID_JUNCAO = 'tags_idtags';

    private Nette\Database\Explorer $database;

    public function __construct(Nette\Database\Explorer $database)
    {
        $this->database = $database;
    }

    public function listarPosts(): Nette\Database\Table\Selection
    {
        return $this->database->table(self::NOME_TABELA)
            ->select('posts.*')
            ->select('autores.nome AS autor_nome')
            ->select('categoria.nome AS categoria_nome');
    }


    public function buscarPost(int $id): ?Nette\Database\Table\ActiveRow
    {
        return $this->listarPosts()->get($id);
    }

    private function relacaoTags(int $postId, array $tagIds): void
    {
        $this->database->table(self::TABELA_JUNCAO_TAGS)
            ->where(self::COLUNA_POST_ID_JUNCAO, $postId)
            ->delete();

        if (!empty($tagIds)) {
            $dadosJuncao = [];
            foreach ($tagIds as $tagId) {
                $dadosJuncao[] = [
                    self::COLUNA_POST_ID_JUNCAO => $postId,
                    self::COLUNA_TAG_ID_JUNCAO => $tagId
                ];
            }
            $this->database->table(self::TABELA_JUNCAO_TAGS)->insert($dadosJuncao);
        }
    }

    public function criarPost(array $dados, array $tagIds = []): Nette\Database\Table\ActiveRow
    {
        if (empty($dados['data_publicacao'])) {
            $dados['data_publicacao'] = new DateTime();
        }

        $this->database->beginTransaction();

        try {
            $post = $this->database->table(self::NOME_TABELA)->insert($dados);

            if (!$post instanceof Nette\Database\Table\ActiveRow) {
                // Se não for um ActiveRow, algo deu muito errado.
                throw new \RuntimeException('Falha ao criar o post. O insert não retornou um objeto ActiveRow.');
            }

            $this->relacaoTags($post->idposts, $tagIds);

            $this->database->commit();

            return $post;
        } catch (\Exception $e) {
            $this->database->rollBack();
            throw $e;
        }
    }


    public function atualizaPost(int $id, array $dados): void
    {
        $this->database->table(self::NOME_TABELA)->get($id)->update($dados);
    }

    public function deletaPost(int $id): void
    {
        $this->database->table(self::TABELA_JUNCAO_TAGS)
            ->where(self::COLUNA_POST_ID_JUNCAO, $id)
            ->delete();

        $this->database->table(self::NOME_TABELA)->get($id)->delete();
    }
}
