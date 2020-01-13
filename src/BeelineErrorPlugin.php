<?php
declare(strict_types=1);

namespace Beeline;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use Beeline\Exception\Beeline400Exception;
use Beeline\Exception\Beeline401Exception;
use Beeline\Exception\Beeline404Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Преобразует коды ответа HTTP в исключения приложения
 */
class BeelineErrorPlugin implements Plugin
{
    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        $promise = $next($request);

        return $promise->then(function (ResponseInterface $response) use ($request) {
            return $this->transformResponseToException($request, $response);
        });
    }

    /**
     * Превращает ответ в ошибку, если нужно.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface Если код ответа не 401/401/404, вернуть ответ
     * @throws Beeline400Exception Если код ответа 400
     * @throws Beeline401Exception Если код ответа 401
     * @throws Beeline404Exception Если код ответа 404
     */
    private function transformResponseToException(
        RequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        if ($response->getStatusCode() == 400) {
            // $response->getReasonPhrase()
            throw new Beeline400Exception('Неправильно сформированы параметры запроса, указаны не все обязательные параметры.',
                $request, $response);
        }

        if ($response->getStatusCode() == 401) {
            throw new Beeline401Exception('Неправильно задано имя пользователя или пароль.',
                $request, $response);
        }

        if ($response->getStatusCode() == 404) {
            throw new Beeline404Exception('Сообщение с указанным id для указанного пользователя не найдено.',
                $request, $response);
        }

        return $response;
    }
}
