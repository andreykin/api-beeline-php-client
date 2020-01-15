<?php
declare(strict_types=1);

namespace Beeline;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use Beeline\Exception\Beeline301Exception;
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
     * @return ResponseInterface
     * @throws Beeline301Exception Если код ответа 200, но пользователь не авторизован
     * @throws Beeline401Exception Если код ответа 301
     * @throws Beeline404Exception Если код ответа 404
     */
    private function transformResponseToException(
        RequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface
    {
        /*if ($response->getStatusCode() == 400) {
            // $response->getReasonPhrase()
            throw new Beeline400Exception('Неправильно сформированы параметры запроса, указаны не все обязательные параметры.',
                $request, $response);
        }
        */

        if ($response->getStatusCode() == 404) {
            $message = 'Страница не найдена: ';
            $message .= $request->getUri()->__toString();

            throw new Beeline404Exception($message,
                $request, $response);
        }

        if ($response->getStatusCode() == 301) {
            $message = 'Страница перемещена: ';
            $message .= 'в ' . $response->getHeader('location')[0] . "\n";
            $message .= 'Вероятно, нужно добавить или убрать \ в запросе';

            throw new Beeline301Exception($message,
                $request, $response);
        }

        if ($response->getStatusCode() == 200) {
            // не ->getContents() из-за бага в сдвиге указателя на конец
            $result = BeelineResponseParser::parseXML($response->getBody()->__toString());

            $errors401 = [
                'User authentication failed',
                'Ошибка авторизации пользователя'
            ];

            if (in_array($result->errors->error, $errors401)) {
                throw new Beeline401Exception($result->errors->error,
                    $request, $response);
            }
        }

        return $response;
    }
}
