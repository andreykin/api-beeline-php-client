<?php

namespace Beeline;

use Http\Client\HttpClient;
use Http\Client\Common\PluginClient;
use Http\Client\Common\Plugin;
use Http\Client\Common\Plugin\AuthenticationPlugin;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\UriFactoryDiscovery;
use Http\Message\Authentication\QueryParam;

class HttpClientFactory
{
    /**
     * Build the HTTP client to talk with the API.
     *
     * @param null $host
     * @param string $user логин для входа в систему на сайте
     * @param string $pass пароль для входа в систему на сайте
     * @param Plugin[] $plugins список дополнительных плагинов
     * @param HttpClient $client Base HTTP client
     * @param boolean $gzip gzip=none не использовать content-encoding: gzip
     * @param string|null $lang переменная клиента, предпочтение относительно языка (не обязательный параметр)
     * @param string|null $ip IP адрес клиента (не обязательный параметр)
     * @param string|null $comment описание подключения (не обязательный параметр; до 512 символов)
     *
     * usage $myApiClient = new Beeline\BeelineClient(Beeline\HttpClientFactory::create('https://beeline.amega-inform.ru/sms_send', 'john', 's3cr3t'));
     *
     * @return HttpClient
     */
    public static function create(
        $host = null,
        $user,
        $pass,
        array $plugins = [],
        HttpClient $client = null,
        $gzip = true,
        $lang = null,
        $ip = null,
        $comment = null
    )
    {
        if (!$client) {
            $client = HttpClientDiscovery::find();
        }
        $plugins[] = new BeelineErrorPlugin();

        $params = ['user' => $user, 'pass' => $pass];

        if ($gzip == false) {
            $params['gzip'] = 'none';
        }

        $params['HTTP_ACCEPT_LANGUAGE'] = $lang ?? $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? false;
        $params['CLIENTADR'] = $ip ?? $_SERVER['REMOTE_ADDR'] ?? false;
        $params['comment'] = $comment;

        $plugins[] = new AuthenticationPlugin(
            new QueryParam($params)
        );

        if ($host) {
            $plugins[] = new Plugin\BaseUriPlugin(UriFactoryDiscovery::find()->createUri($host));
        }

        return new PluginClient($client, $plugins);
    }
}
