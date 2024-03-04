
[![Latest Version](https://img.shields.io/packagist/v/littlefish/uberduck)](https://github.com/berman82312/uberduck)
[![Total downloads](https://img.shields.io/packagist/dt/littlefish/uberduck)](https://github.com/berman82312/uberduck)


# uberduck
Uberduck's Rest API for PHP. For the full document on Uberduck's API, please refer to the [official document](https://docs.uberduck.ai/reference).

## Documentation

* [Installation](#installation)
* [Usage](#usage)
    - [List Voices](#list-voices)
    - [List Voice Samples](#list-voice-samples)
    - [List Backing Tracks](#list-backing-tracks)
    - [Generate Lyrics](#generate-lyrics)
    - [Generate Freestyle](#generate-freestyle)
    - [Prompt Builder](#prompt-builder)

## Installation
To begin, you'll need to add the component to your `composer.json`
```shell
composer require littlefish/uberduck
```
After adding the component, update your packages using `composer update` or install them using `composer install`.

### Environment variables

This library **requires** you to have following settings in your environment variables:
``` shell
UBERDUCK_KEY=<your-uberduck-key>
UBERDUCK_SECRET=<your-uberduck-secret-key>
UBERDUCK_HOST=<uberduck-api-host> # optional
```

### Laravel config (optional)
You can use this package with your Laravel project directly after installed. If you need to modify the default config, you can add a `uberduck` config key:
``` php
'uberduck' => [
    'api_key' => 'your-uberduck-key',
    'api_secret' => 'your-uberduck-secret-key',
    'api_host' => 'uberduck-api-host'
]
```

## Usage

The following API methods are mostly following the [official Uberduck document](https://docs.uberduck.ai/reference). You can find more detailed usage by reading the document. For the payloads(parameters), please
find detailed description in the official document.

### List Voices
Get information on available voices.

Example:
```php
$response = uberduck->listVoices($payload);
```

### List Voice Samples
Return sample outputs from the specified VoiceModel.

Example:
```php
$response = uberduck->listVoiceSamples($voiceModelUuid);
```

### List Backing Tracks
List backing tracks.

Example:
```php
$response = uberduck->listBackingTracks();
// or
$response = uberduck->listBackingTracks($payload);
```

### Generate Lyrics

Example:
```php
$response = uberduck->generateLyrics($payload);
```

### Generate Freestyle

Example:
```php
$response = uberduck->generateFreestyle($payload);
```

### Prompt Builder
Custom prompt can be created through the `PROMPT BUILDER` tab in your Uberduck dashboard.

Example:
```php
/*
  $payload should be the variables you want to POST to your prompt, not including the `variables` key.
  $payload = [
    'parameter' => 'value'
  ];

  The function will stringify your $payload with following php object structure in the POST body:
  ['variables' => $payload]
*/
$response = uberduck->customPrompt($key, $payload);
```
