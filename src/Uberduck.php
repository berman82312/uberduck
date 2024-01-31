<?php

namespace littlefish\Uberduck;

class Uberduck
{
    protected UberduckClient $client;

    public function __construct(UberduckClient $client)
    {
        $this->client = $client;
    }

    public function listVoices(array $payload)
    {
        $url = 'voices';

        $response = $this->client->get($url, $payload);

        return $response;
    }

    public function listVoiceSamples(string $voicemodelUuid)
    {
        $url = 'voices/' . $voicemodelUuid . '/samples';

        $response = $this->client->get($url);

        return $response;
    }

    public function listBackingTracks(?array $payload = null)
    {
        $url = 'reference-audio/backing-tracks';

        $response = $this->client->get($url, $payload);

        return $response;
    }

    public function generateLyrics(array $payload)
    {
        $url = 'tts/lyrics';

        $response = $this->client->post($url, $payload);

        return $response;
    }

    public function generateFreestyle(array $payload)
    {
        $url = 'tts/freestyle';

        $response = $this->client->post($url, $payload);

        return $response;
    }
}
