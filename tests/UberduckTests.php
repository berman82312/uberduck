<?php

namespace littlefish\Uberduck;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class UberduckTests extends TestCase
{
    protected Uberduck $uberduck;
    protected Client $client;
    protected MockHandler $responseMock;
    protected array $requestHistory;

    protected function setUp(): void
    {
        parent::setUp();

        $config = require './config/uberduck.php';

        $this->responseMock = new MockHandler([]);
        $handlerStack = HandlerStack::create($this->responseMock);

        $this->requestHistory = [];
        $history = Middleware::history($this->requestHistory);
        $handlerStack->push($history);

        $this->client = new Client(['handler' => $handlerStack]);

        $this->uberduck = new Uberduck($config, $this->client);
    }

    protected function tearDown(): void
    {
        $this->responseMock->reset();
        $this->requestHistory = [];
        parent::tearDown();
    }

    public function testListVoices(): void
    {
        $testResponse = json_encode([
            [
                "category" => "",
                "display_name" => "German Male",
                "is_private" => true,
                "name" => "german-male",
                "voicemodel_uuid" => "28e13832-5bff-45f5-8b19-126d9e771f5b",
                "language" => "german"
            ],
            [
                "category" => "",
                "display_name" => "Oursong English",
                "is_private" => true,
                "name" => "oursong-english",
                "voicemodel_uuid" => "bb25b435-7e5c-4cab-9b0b-ff14b3fdd50b",
                "language" => "english"
            ]
        ]);
        $this->responseMock->append(new Response(200, ['Content-Type' => 'application/json'], $testResponse));
        $result = $this->uberduck->listVoices([
            'mode' => 'v2v'
        ]);

        $this->assertEquals(1, count($this->requestHistory));

        $transaction = $this->requestHistory[0];
        $request = $transaction['request'];

        $url = 'https://api.uberduck.ai/voices?mode=v2v';

        $this->assertEquals($url, $request->getUri()->__toString());
        $this->assertEquals(2, count($result));
    }

    public function testGenerateFreestyle(): void
    {
        $testResponse = json_encode([
            "mix_url" => "https://uberduck-outputs-permalink.s3-us-west-2.amazonaws.com/test.wav",
            "vocals_url" => "https://uberduck-outputs-permalink.s3-us-west-2.amazonaws.com/test.mp3",
            "title" => "This is a mocked response",
            "render_uuid" => "d8067893-6cc6-40ea-85dd-aae92f717fa6",
            "render_video_response" => "https://uberduck-temporary-assets.s3-us-west-2.amazonaws.com/test.mp4",
            "timestamps" => null,
            "bpm" => 100
        ]);
        $this->responseMock->append(new Response(200, ['Content-Type' => 'application/json'], $testResponse));
        $result = $this->uberduck->generateFreestyle([
            "backing_track" => "05da2fd9-469e-479b-81ff-b7192ebc78b9",
            "voicemodel_uuid" => "237fe1a9-ce23-4785-95ec-f4f4bb88c06a",
            "lyrics" => [
                [
                    "Lofi beats, they be soothing my soul",
                    "Like a warm blanket on a winter night, they console",
                    "The crackle and pop, like a fire's gentle roar",
                    "It's like stepping through a portal to another world, I explore",
                    "The melodies, they paint pictures in my mind",
                    "Like a brushstroke of calmness, leaving worries behind",
                    "The rhythm and flow, they carry me away",
                    "Like a gentle breeze on a hot summer day"
                ]
            ],
            "lines" => 8,
            "title" => "New beat",
            "render_video" => true,
        ]);

        $this->assertEquals(1, count($this->requestHistory));

        $transaction = $this->requestHistory[0];
        $request = $transaction['request'];

        $url = 'https://api.uberduck.ai/tts/freestyle';

        $this->assertEquals($url, $request->getUri()->__toString());
        $this->assertEquals("d8067893-6cc6-40ea-85dd-aae92f717fa6", $result['render_uuid']);
    }
}
