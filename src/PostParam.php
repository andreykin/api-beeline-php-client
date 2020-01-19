<?php


namespace Beeline;

use Http\Discovery\StreamFactoryDiscovery;
use Http\Message\Authentication;
use Psr\Http\Message\RequestInterface;

final class PostParam implements Authentication
{
    /**
     * @var array
     */
    private $params = [];

    /**
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(RequestInterface $request)
    {
        $streamFactory = StreamFactoryDiscovery::find();
        $body = $request->getBody()->__toString();
        $params = [];

        parse_str($body, $params);
        $params = array_merge($params, $this->params);
        $newBody = http_build_query($params, null, '&');
        return $request->withBody($streamFactory->createStream($newBody));
    }
}