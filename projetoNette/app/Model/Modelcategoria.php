<?php

namespace App\Model\Modelcategoria;
use Nette;

final class Modelcategoria
{
    use Nette\SmartObject;

    private const NOME_TABELA = "categoria";
    private const COLUNA_ID = "idcategoria";

    private Nette\Database\Explorer $database;


    public function __construct(Nette\Database\Explorer $database)
    {
        $this->database = $database;
    }

    public function listarCategorias(): Nette\Database\Table\Selection
    {
        return $this->database->table(self::NOME_TABELA);
    }

    public function buscarCategoria(int $id): ?Nette\Database\Table\ActiveRow
    {
        return $this->listarCategorias()->get($id);
    }

    public function criaCategoria(array $dados): Nette\Database\Table\ActiveRow
    {
        return $this->database->table(self::NOME_TABELA)->insert($dados);
    }

    public function atualizaCategoria(int $id, array $dados): void
    {
        $this->buscarCategoria($id)->update($dados);
    }

    public function deletaCategoria(int $id): void
    {
        $this->buscarCategoria($id)->delete();
    }
}

