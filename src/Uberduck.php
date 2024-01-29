<?php

namespace littlefish\Uberduck;

use Http\Client\Common\Plugin\AuthenticationPlugin;
use Http\Client\Common\Plugin\ContentTypePlugin;
use Http\Client\Common\PluginClient;
use Http\Client\Exception\HttpException;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Message\Authentication\BasicAuth;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Uberduck
{
    protected $host;
    protected ClientInterface $client;
    protected RequestFactoryInterface $requestFactory;
    protected StreamFactoryInterface $streamFactory;

    public function __construct(private readonly array $config, ?ClientInterface $client = null)
    {
        $this->host = $config['api_host'];
        $this->client = $client ?? Psr18ClientDiscovery::find();

        $authentication = new BasicAuth('username', 'password');
        $authenticationPlugin = new AuthenticationPlugin($authentication);
        $contentTypePlugin = new ContentTypePlugin();

        $this->requestFactory = Psr17FactoryDiscovery::findRequestFactory();

        $this->streamFactory = Psr17FactoryDiscovery::findStreamFactory();

        $this->client = new PluginClient($this->client, [$authenticationPlugin, $contentTypePlugin]);
    }

    public function listVoices(array $payload)
    {
        $url = $this->makeUrl('voices');

        $response = $this->getRequest($url, $payload);

        return $response;
    }

    public function generateFreestyle(array $payload)
    {
        $url = $this->makeUrl('tts/freestyle');

        $response = $this->postRequest($url, $payload);

        return $response;
    }

    protected function makeUrl(string $path)
    {
        return $this->host . $path;
    }

    protected function getRequest(string $url, array $payload)
    {
        $query = http_build_query($payload);
        $url = $url . '?' . $query;

        $request = $this->requestFactory->createRequest('GET', $url);

        $response = $this->sendRequest($request);

        return $response;
    }

    protected function postRequest(string $url, array $payload)
    {
        $request = $this->requestFactory->createRequest('POST', $url)
            ->withBody($this->streamFactory->createStream(json_encode(array_filter($payload), JSON_THROW_ON_ERROR)));

        $response = $this->sendRequest($request);

        return $response;
    }

    protected function sendRequest(RequestInterface $request)
    {
        $response = $this->client->sendRequest($request);

        $body = $response->getBody();

        $result = json_decode((string) $body, true, 512, JSON_THROW_ON_ERROR);

        if ($response->getStatusCode() !== 200) {
            throw new HttpException($result['error']['message'], $request, $response);
        }

        return $result;
    }
}
