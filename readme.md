# Projeto Nette API 

Este é um backend RESTful API construído com [Nette Framework](https://nette.org/), desenhado para servir como API para uma aplicação frontend (ex: Django, React).

O sistema gerencia um CRUD completo para **Autores**, **Categorias**, **Tags** e **Posts**, incluindo suporte para upload de imagens e relações complexas (Muitos-para-Muitos).

## Pré-requisitos

  * **PHP 8.1** ou superior.
  * **Composer** (Gestor de dependências do PHP).
  * **Servidor Web** (Apache recomendado, via XAMPP, Laragon ou similar).
  * **MySQL/MariaDB**.
  * **Postman** (para testar os endpoints).

## Configuração do PHP (`php.ini`)

Para que o sistema funcione corretamente, especialmente o upload de imagens, você **precisa** ajustar o seu ficheiro `php.ini`.

1.  **Localize o `php.ini`:** No XAMPP, clique em *Config \> PHP (php.ini)* no painel de controlo.
2.  **Ative a Extensão GD** (Essencial para validação de imagens):
      * Procure por `;extension=gd`.
      * Remova o ponto e vírgula (`;`) do início para ativar: `extension=gd`.
3.  **Aumente os Limites de Upload** (Opcional, mas recomendado para evitar erros com imagens grandes):
      * Procure e altere para valores maiores, por exemplo:
          * `upload_max_filesize = 20M`
          * `post_max_size = 25M`
4.  **Reinicie o Apache** após salvar as alterações.

## 🚀 Instalação Passo-a-Passo

1.  **Clonar/Baixar** o projeto para a sua pasta de servidor (ex: `C:\xampp\htdocs\projetoNette`).
2.  **Instalar Dependências:**
    Abra o terminal na pasta do projeto e execute:
    ```bash
    composer install
    ```
3.  **Configurar Base de Dados:**
      * Crie uma base de dados MySQL (ex: `nette_blog`).
      * Importe o esquema SQL (tabelas `autores`, `categoria`, `tags`, `posts`, `tags_has_posts`).
      * Configure a conexão no ficheiro `config/local.neon` (crie-o se não existir, baseando-se no `common.neon`):
        ```neon
        database:
            dsn: 'mysql:host=127.0.0.1;dbname=nette_blog'
            user: root
            password: '' # Sua senha
        ```
4.  **Criar Pastas Necessárias:**
    O sistema precisa destas pastas com permissão de escrita:
      * `temp/`
      * `log/`
      * `www/uploads/` (Crucial para o upload de imagens\!)

## Configuração do Servidor (Apache/XAMPP)

Se encontrar erros `403 Forbidden` ou `404 Not Found` nas rotas da API:

1.  Certifique-se de que o módulo **`mod_rewrite`** está ativo no seu `httpd.conf`.
2.  Garanta que a diretiva **`AllowOverride All`** está definida para a sua pasta `htdocs` no `httpd.conf` (e não está a ser anulada pelo `httpd-xampp.conf`).
3.  Verifique se o ficheiro **`.htaccess`** padrão do Nette está presente na pasta `www/`.

## Endpoints da API

A API comunica exclusivamente via **JSON**.
URL Base (exemplo): `http://localhost/projetoNette/www`

| Recurso | Método | Endpoint | Descrição |
| :--- | :--- | :--- | :--- |
| **Autores** | `GET` | `/autor` | Lista todos os autores |
| | `POST` | `/autor` | Cria um novo autor |
| | `GET` | `/autor/{id}` | Obtém um autor específico |
| | `PUT` | `/autor/{id}` | Atualiza um autor |
| | `DELETE`| `/autor/{id}` | Remove um autor (se não tiver posts) |
| **Categorias**| `GET` | `/categoria` | (Mesma estrutura de Autores) |
| **Tags** | `GET` | `/tags` | (Mesma estrutura de Autores) |
| **Upload** | `POST` | `/posts/upload`| Enviar imagem via `multipart/form-data`. Retorna `{ "url": "..." }` |
| **Posts** | `GET` | `/posts` | Lista posts com dados relacionados |
| | `POST` | `/posts` | Cria post. JSON deve incluir IDs: `autores_idautores`, `categoria_idcategoria`, `tags` (array) e `url_imagem` (opcional) |

### Exemplo de JSON para Criar Post

```json
{
    "titulo": "Meu Novo Post",
    "conteudo": "Conteúdo do post...",
    "autores_idautores": 1,
    "categoria_idcategoria": 5,
    "tags": [2, 4],
    "url_imagem": "http://localhost/.../uploads/imagem.jpg"
}
```

## CORS (Cross-Origin Resource Sharing)

A API já está configurada para aceitar pedidos de origens externas (como o seu frontend Django em `localhost:8000`).
Para alterar as permissões, edite o método `run()` no ficheiro `app/Core/ApiPresenter.php`.
