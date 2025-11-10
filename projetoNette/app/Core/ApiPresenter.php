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
        $httpResponse->setHeader('Access-Control-Allow-Origin', 'http://localhost:8000'); // Ou '*' para testes
        $httpResponse->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $httpResponse->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $httpResponse->setHeader('Access-Control-Allow-Credentials', 'true');

        if ($this->getHttpRequest()->isMethod('OPTIONS')) {
            $this->sendJson(null, 204);
        }
        try {
            return parent::run($request);
        } catch (BadRequestException $e) {
            $this->getHttpResponse()->setCode($e->getCode() ?: 400);
            return new JsonResponse(['erro' => $e->getMessage()]);
        } catch (Nette\Database\ForeignKeyConstraintViolationException $e) {
            $this->getHttpResponse()->setCode(409);
            return new JsonResponse(['erro' => 'Conflito de dependência.']);
        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            $this->getHttpResponse()->setCode(409);
            return new JsonResponse(['erro' => 'Dado duplicado.']);
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
        // Desliga a Tracy para garantir que não "suja" o JSON
        \Tracy\Debugger::$productionMode = true;

        // ... (o resto do seu código startup, se houver)
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
