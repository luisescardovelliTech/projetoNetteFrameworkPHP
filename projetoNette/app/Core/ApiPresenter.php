<?php

declare(strict_types=1);

namespace App\Core;

use Nette;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Template;


abstract class ApiPresenter extends Nette\Application\UI\Presenter
{
    public function run(Nette\Application\Request $request): Nette\Application\Response
    {

        $httpResponse = $this->getHttpResponse();

        $httpResponse->setHeader('Access-Control-Allow-Origin', '*');

        $httpResponse->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $httpResponse->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

        if ($this->getHttpRequest()->isMethod('OPTIONS')) {
            $this->sendJson(null, 204); // Responde 204 
        }
        try {
            return parent::run($request);
        } catch (BadRequestException $e) {
            $this->getHttpResponse()->setCode($e->getCode() ?: 400);
            return new JsonResponse(['erro' => $e->getMessage()]);
        } catch (Nette\Database\ForeignKeyConstraintViolationException $e) {
            $this->getHttpResponse()->setCode(409);
            return new JsonResponse(['erro' => 'Conflito de dependência. O recurso está em uso.']);

        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            $this->getHttpResponse()->setCode(409);
            return new JsonResponse(['erro' => 'Conflito de dados. O recurso já existe.']);

        } catch (\Exception $e) {
            $this->getHttpResponse()->setCode(500);

            return new JsonResponse(['erro' => 'Erro interno', 'detalhe' => $e->getMessage()]);
        }
    }

    public function sendTemplate(?Template $template = null): void
    {

    }

    protected function startup(): void
    {
        parent::startup();
        \Tracy\Debugger::$productionMode = true;

    }

    protected function getJsonBody(): array
    {
        $corpo = $this->getHttpRequest()->getRawBody();
        $dados = json_decode($corpo, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new BadRequestException('JSON inválido.', 400);
        }
        return $dados;
    }

    public function sendJson($data, int $code = 200): void
    {
        $this->getHttpResponse()->setCode($code);
        $this->sendResponse(new JsonResponse($data));
    }
}
