<?php

namespace App\Model;

use Nette;
use Nette\Utils\DateTime;

final class Modelposts
{
    use Nette\SmartObject;

    private const NOME_TABELA = 'posts';
    private const TABELA_JUNCAO_TAGS = 'tags_has_posts';
    private const COLUNA_POST_ID_JUNCAO = 'posts_idposts';

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

    public function criarPost(array $dados): Nette\Database\Table\ActiveRow
    {
        if (empty($dados['data_publicacao'])) {
            $dados['data_publicacao'] = new DateTime();
        }

        return $this->database->table(self::NOME_TABELA)->insert($dados);
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
