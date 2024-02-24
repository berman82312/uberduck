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

        $uberduckClient = new UberduckClient($config, $this->client);

        $this->uberduck = new Uberduck($uberduckClient);
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

    public function testGenerateLyrics(): void
    {
        $testResponse = json_encode([
            "title" => "Lofi Magic",
            "lyrics" => [
                [
                    "Lofi is awesome, it's like a warm embrace",
                    "Soothing my soul, putting me in a peaceful space",
                    "Beats like a gentle breeze, melodies like a lullaby",
                    "It's a masterpiece of sound, elevating me so high",
                    "The crackle of vinyl, like rain on a tin roof",
                    "It's a symphony of simplicity, the ultimate proof",
                    "Of music's power to transport and heal",
                    "Lofi is the remedy, the magic I feel",
                    "The warmth of a tape deck, the hum of a record player",
                    "Lofi's like a time machine, taking me back to a place that's familiar",
                    "Nostalgia and comfort, wrapped in a blanket of sound",
                    "It's a treasure chest of emotion, waiting to be found"
                ]
            ]
        ]);
        $this->responseMock->append(new Response(200, ['Content-Type' => 'application/json'], $testResponse));
        $result = $this->uberduck->generateLyrics([
            "subject" => "Lofi is awesome",
            "lines" => [16]
        ]);

        $this->assertEquals(1, count($this->requestHistory));

        $transaction = $this->requestHistory[0];
        $request = $transaction['request'];

        $url = 'https://api.uberduck.ai/tts/lyrics';

        $this->assertEquals($url, $request->getUri()->__toString());
        $this->assertEquals("Lofi Magic", $result['title']);
    }

    public function testListBackingTracks(): void
    {
        $testResponse = json_encode([
            "backing_tracks" => [
                [
                    "bpm" => 102.0,
                    "uuid" => "726f4142-c85a-4afc-a1e8-e76342692329",
                    "name" => "\$eries A (Hip Hop)",
                    "source" => "Custom",
                    "bucket" => "uberduck-reference-audio",
                    "path" => "1735361/c89cdbdf-9795-423b-8fee-600dbb1a3bc1-RnBH 8 (102bpm Dm).wav",
                    "is_public" => true,
                    "verses" => [
                        [
                            "label" => "Verse 1",
                            "start" => 9.4118,
                            "length_in_measures" => 20
                        ]
                    ]
                ],
                [
                    "bpm" => 109.0,
                    "uuid" => "2008268e-583a-407d-9ae9-b2a8593a35b4",
                    "name" => "Burn rate (Hip Hop)",
                    "source" => "Custom",
                    "bucket" => "uberduck-reference-audio",
                    "path" => "1735361/19027b9a-f81e-47f6-9a83-e3af51bb7416-RnBH 15 (109bpm Bm).wav",
                    "is_public" => true,
                    "verses" => [
                        [
                            "label" => "Verse 1",
                            "start" => 17.604874376922428,
                            "length_in_measures" => 16
                        ]
                    ]
                ]
            ]
        ]);
        $this->responseMock->append(new Response(200, ['Content-Type' => 'application/json'], $testResponse));
        $result = $this->uberduck->listBackingTracks();

        $this->assertEquals(1, count($this->requestHistory));

        $transaction = $this->requestHistory[0];
        $request = $transaction['request'];

        $url = 'https://api.uberduck.ai/reference-audio/backing-tracks';

        $this->assertEquals($url, $request->getUri()->__toString());
        $this->assertEquals(2, count($result['backing_tracks']));
    }

    public function testListVoiceSamples(): void
    {
        $testResponse = json_encode([
            [
                "transcription" => "Now this is a story all about how my life got flipped turned upside down and I'd like to take a minute Just sit right there I'll tell you how I became the prince of a town called Bel Air.",
                "url" => "https://uberduck-audio-permalink.s3.amazonaws.com/84a12281-6270-4de6-acd7-4baeb1ad9e7d/audio.wav?AWSAccessKeyId=AKIAY5QT7KYNL5RNFMAE&Signature=QX0cnnn9jTOumFzY2F1brPBi5fI%3D&Expires=1706673464"
            ],
            [
                "transcription" => "They told him don't you ever come around here, don't wanna see your face, you better disappear. The fire's in their eyes and their words are really clear, so beat it, just beat it.",
                "url" => "https://uberduck-audio-permalink.s3.amazonaws.com/d1a26746-5bea-44f5-be73-fbe8de4b2a26/audio.wav?AWSAccessKeyId=AKIAY5QT7KYNL5RNFMAE&Signature=ca6j8y6CLhnvuIVXsgiY1dhfynU%3D&Expires=1706673464"
            ]
        ]);
        $this->responseMock->append(new Response(200, ['Content-Type' => 'application/json'], $testResponse));
        $result = $this->uberduck->listVoiceSamples('639f5a27-edbc-444f-bfe9-c7b62aa014f8');

        $this->assertEquals(1, count($this->requestHistory));

        $transaction = $this->requestHistory[0];
        $request = $transaction['request'];

        $url = 'https://api.uberduck.ai/voices/639f5a27-edbc-444f-bfe9-c7b62aa014f8/samples';

        $headers = $request->getHeaders();

        $auth = base64_encode('testing_key:testing_secret');

        $this->assertEquals($url, $request->getUri()->__toString());
        $this->assertArrayHasKey('Authorization', $headers);
        $this->assertEquals('Basic ' . $auth, $headers['Authorization'][0]);
        $this->assertEquals(2, count($result));
    }

    public function testCustomPrompt(): void
    {
        $testResponse = json_encode([
            "choices" => [
                [
                    "finish_reason" => "stop",
                    "index" => 0,
                    "logprobs" => null,
                    "message" => [
                        "content" => "俺の心は燃えてる　夢を追いかけて\n日々の戦い　立ち向かって\n街中を駆け抜ける　俺のスタイル\n誰も止められない　この炎のような情熱\n\n時には苦しい　時には辛い\nでも俺は立ち止まらない　前だけ見て\n夢を叶えるために　全力で走る\nこの街の王になる　その日まで\n\n誰もが俺の名前を知るだろう\n俺の音楽が響く　どこまでも\n日本中を揺るがす　俺のメロディ\nこれが俺の物語　誰にも邪魔させない\n\n俺の血は熱い　俺の魂は強い\nこのリリックが刻む　俺の遺産\n日本の誇りを背負って　進む\n俺のラップが変える　未来を見せる",
                        "role" => "assistant",
                        "function_call" => null,
                        "tool_calls" => null
                    ]
                ]
            ],
            "created" => 1708759229,
            "id" => "test",
            "model" => "gpt-35-turbo",
            "object" => "chat.completion",
            "usage" => [
                "completion_tokens" => 296,
                "prompt_tokens" => 45,
                "total_tokens" => 341
            ]
        ]);
        $this->responseMock->append(new Response(200, ['Content-Type' => 'application/json'], $testResponse));
        $result = $this->uberduck->customPrompt('japanese-lyrics');

        $this->assertEquals(1, count($this->requestHistory));

        $transaction = $this->requestHistory[0];
        $request = $transaction['request'];

        $url = 'https://api.uberduck.ai/templates/deployments/japanese-lyrics/generate';

        $headers = $request->getHeaders();
        $requestBody = (string) $request->getBody();

        $auth = base64_encode('testing_key:testing_secret');

        $this->assertEquals($url, $request->getUri()->__toString());
        $this->assertArrayHasKey('Authorization', $headers);
        $this->assertEquals('Basic ' . $auth, $headers['Authorization'][0]);
        $this->assertEmpty(json_decode($requestBody));
    }

    public function testCustomPromptWithVariables(): void
    {
        $testResponse = json_encode([
            "choices" => [
                [
                    "finish_reason" => "stop",
                    "index" => 0,
                    "logprobs" => null,
                    "message" => [
                        "content" => "俺の心は燃えてる　夢を追いかけて\n日々の戦い　立ち向かって\n街中を駆け抜ける　俺のスタイル\n誰も止められない　この炎のような情熱\n\n時には苦しい　時には辛い\nでも俺は立ち止まらない　前だけ見て\n夢を叶えるために　全力で走る\nこの街の王になる　その日まで\n\n誰もが俺の名前を知るだろう\n俺の音楽が響く　どこまでも\n日本中を揺るがす　俺のメロディ\nこれが俺の物語　誰にも邪魔させない\n\n俺の血は熱い　俺の魂は強い\nこのリリックが刻む　俺の遺産\n日本の誇りを背負って　進む\n俺のラップが変える　未来を見せる",
                        "role" => "assistant",
                        "function_call" => null,
                        "tool_calls" => null
                    ]
                ]
            ],
            "created" => 1708759229,
            "id" => "test",
            "model" => "gpt-35-turbo",
            "object" => "chat.completion",
            "usage" => [
                "completion_tokens" => 296,
                "prompt_tokens" => 45,
                "total_tokens" => 341
            ]
        ]);
        $this->responseMock->append(new Response(200, ['Content-Type' => 'application/json'], $testResponse));
        $result = $this->uberduck->customPrompt('japanese-lyrics', ['lyrics' => 'japanese']);

        $this->assertEquals(1, count($this->requestHistory));

        $transaction = $this->requestHistory[0];
        $request = $transaction['request'];

        $url = 'https://api.uberduck.ai/templates/deployments/japanese-lyrics/generate';

        $headers = $request->getHeaders();
        $requestBody = (string) $request->getBody();
        $body = json_decode($requestBody);

        $auth = base64_encode('testing_key:testing_secret');

        $this->assertEquals($url, $request->getUri()->__toString());
        $this->assertArrayHasKey('Authorization', $headers);
        $this->assertEquals('Basic ' . $auth, $headers['Authorization'][0]);
        $this->assertObjectHasProperty('variables', $body);
        $this->assertObjectHasProperty('lyrics', $body->variables);
        $this->assertEquals('japanese', $body->variables->lyrics);
    }
}
