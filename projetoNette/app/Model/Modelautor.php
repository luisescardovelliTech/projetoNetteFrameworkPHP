<?php

namespace App\Model\Modelautor;
use Nette;

final class Modelautor
{
    use Nette\SmartObject;

    private const NOME_TABELA = "autores";
    private const COLUNA_ID = "idautores";

    private Nette\Database\Explorer $database;

    public function __construct(Nette\Database\Explorer $database)
    {
        $this->database = $database;
    }

    public function listarAutores(): Nette\Database\Table\Selection
    {

        // select * from autores
        return $this->database->table(self::NOME_TABELA);
    }

    public function buscarAutor(int $id): ?Nette\Database\Table\ActiveRow
    {
        // select autores com where
        return $this->listarAutores()->get($id);
    }

    public function criarAutor(array $dados): Nette\Database\Table\ActiveRow
    {
        return $this->database->table(self::NOME_TABELA)->insert($dados);
    }

    public function autualizaAutor(int $id, array $dados): void
    {
        $this->buscarAutor($id)->update($dados);
    }

    public function deletaAutor(int $id): void
    {
        $this->buscarAutor($id)->delete();
    }
}
