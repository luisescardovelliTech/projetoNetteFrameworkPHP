<?php

namespace App\Autor;

use App\Model\Modelautor\Modelautor;
use Nette;

use Nette\Application\UI\Form;

final class AutorPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Modelautor $modelo) //Nette injeta o modelAutor feito
    {
        parent::__construct();
    }


    public function renderDefault(): void //pagina padrao
    {
        $this->template->autores = $this->modelo->listarAutores();
    }

    public function renderEditar(?int $id = null): void
    {
        if ($id) {
            $autor = $this->modelo->buscarAutor($id);
            if (!$autor) {
                $this->error('Autor não encontrado');
            }
            $this['formAutor']->setDefaults($autor);
            $this->template->nomeAutor = $autor->nome;
        } else {
            $this->template->nomeAutor = 'Novo Autor';
        }
    }

    public function DeletarAutor(int $id): void // funçao para deletar o autor
    {
        try {
            $this->modelo->deletaAutor($id);
            $this->flashMessage('Autor deletado com sucesso', 'success');
        } catch (\Exception $e) {
            $this->flashMessage('Erro ao deletar autor.', 'danger');
        }
        $this->redirect('default');
    }

    protected function criarFormularioAutor(): Form // formulario do autor
    {
        $formulario = new Form;
        $formulario->addText('nome', 'Nome: ')->setRequired();
        $formulario->addEmail('email', 'E-mail: ')->setRequired();
        $formulario->addText('celular', 'Celular: ');
        $formulario->addSubmit('send', 'Salvar');
        $formulario->onSuccess[] = [$this, 'formAutorSucesso'];
        return $formulario;
    }

    public function formAutorSucesso(Form $form, array $valores): void //funçao é chamada quando o formulario é salvo com sucesso
    {
        $id = $this->getParameter('id');

        if ($id) {
            $this->modelo->autualizaAutor($id, $valores);
            $this->flashMessage('Atuor atualizado.', 'success');
        } else {
            $this->modelo->criarAutor($valores);
            $this->flashMessage('Autor criado.', 'success');
        }
        $this->redirect('default');
    }
}
