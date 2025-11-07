<?php

declare(strict_types=1);

namespace App\Core;

use Nette;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\JsonResponse;

abstract class ApiPresenter extends Nette\Application\UI\Presenter
{
    public function run(Nette\Application\Request $request): Nette\Application\Response
    {
        $httpResponse = $this->getHttpResponse();

        $httpResponse->setHeader('Access-Control-Allow-Origin', 'http://localhost:8000');

        $httpResponse->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');

        $httpResponse->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

        $httpResponse->setHeader('Access-Control-Allow-Credentials', 'true');

        if ($this->getHttpRequest()->isMethod('OPTIONS')) {
            // envia uma resposta 204 (No Content) e termina a execução
            $this->sendJson(null, 204);
        }
        try {
            return parent::run($request);
        } catch (BadRequestException $e) {
            // 400 - Erro do cliente
            $this->getHttpResponse()->setCode($e->getCode());
            return new JsonResponse(['erro' => $e->getMessage()]);
        } catch (Nette\Database\ForeignKeyConstraintViolationException $e) {
            // 409 - Conflito
            $this->getHttpResponse()->setCode(409);
            return new JsonResponse(['erro' => 'Conflito de dependência. O recurso está em uso.']);
        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            // 409 - Conflito
            $this->getHttpResponse()->setCode(409);
            return new JsonResponse(['erro' => 'Conflito de dados. O recurso já existe.']);
        } catch (\Exception $e) {

            $this->getHttpResponse()->setCode(500);
            return new JsonResponse(['erro' => 'Erro interno do servidor', 'detalhe' => $e->getMessage()]);
        }
    }

    protected function getJsonBody(): array
    {
        $corpo = $this->getHttpRequest()->getRawBody();
        $dados = json_decode($corpo, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // isso vai disparar o catch (BadRequestException $e) la de cima
            throw new BadRequestException('JSON inválido no corpo da requisição.', 400);
        }
        return $dados;
    }


    public function sendJson($data, int $code = 200): void
    {
        $this->getHttpResponse()->setCode($code);
        $this->sendResponse(new JsonResponse($data));
    }
}
