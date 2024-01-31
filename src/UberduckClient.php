<?php

namespace littlefish\Uberduck;

use Http\Client\Exception\HttpException;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class UberduckClient
{
    protected string $host;
    protected string $auth;
    protected ClientInterface $client;
    protected RequestFactoryInterface $requestFactory;
    protected StreamFactoryInterface $streamFactory;

    public function __construct(private readonly array $config, ?ClientInterface $client = null)
    {
        $this->host = $config['api_host'];
        $this->client = $client ?? Psr18ClientDiscovery::find();

        $this->auth = base64_encode($config['api_key'] . ':' . $config['api_secret']);

        $this->requestFactory = Psr17FactoryDiscovery::findRequestFactory();

        $this->streamFactory = Psr17FactoryDiscovery::findStreamFactory();
    }

    protected function makeUrl(string $path)
    {
        return $this->host . $path;
    }

    public function get(string $url, ?array $payload = null)
    {
        $url = $this->makeUrl($url);

        if ($payload) {
            $query = http_build_query($payload);
            $url = $url . '?' . $query;
        }

        $request = $this->requestFactory->createRequest('GET', $url);

        $response = $this->sendRequest($request);

        return $response;
    }

    public function post(string $url, array $payload)
    {
        $url = $this->makeUrl($url);

        $request = $this->requestFactory->createRequest('POST', $url)
            ->withBody($this->streamFactory->createStream(json_encode(array_filter($payload), JSON_THROW_ON_ERROR)));

        $response = $this->sendRequest($request);

        return $response;
    }

    protected function sendRequest(RequestInterface $request)
    {
        $request = $request->withHeader('Authorization', 'Basic ' . $this->auth)
            ->withHeader('content-type', 'application/json');

        $response = $this->client->sendRequest($request);

        $body = $response->getBody();

        $result = json_decode((string) $body, true, 512, JSON_THROW_ON_ERROR);

        if ($response->getStatusCode() !== 200) {
            throw new HttpException($result['error']['message'], $request, $response);
        }

        return $result;
    }
}
