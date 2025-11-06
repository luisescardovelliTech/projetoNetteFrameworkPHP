<?php

namespace App\Model\Modeltags;

use Nette;

final class Modeltags
{
    use Nette\SmartObject;

    private const NOME_TABELA = "tags";

    private Nette\Database\Explorer $database;

    public function __construct(Nette\Database\Explorer $database)
    {
        $this->database = $database;
    }

    public function listarTags(): Nette\Database\Table\Selection
    {
        return $this->database->table(self::NOME_TABELA);
    }

    public function buscarTag(int $id): ?Nette\Database\Table\ActiveRow
    {
        return $this->listarTags()->get($id);
    }

    public function criarTags(array $dados): Nette\Database\Table\ActiveRow
    {
        return $this->database->table(self:: NOME_TABELA)->insert($dados);
    }

    public function autalizaTag(int $id, array $dados): void
    {
        $this->buscarTag($id)->update($dados);
    }

    public function deletaTag(int $id): void
    {
        $this->buscarTag($id)->delete();
    }
}

