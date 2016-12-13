#对Curl的简单封装

```php
<?php

use Funch\Curl\CurlClient as Curl;

$baseUrl = 'http://www.foo.com/';

Curl::setBaseUrl($baseUrl);

$res = Curl::request([
    'url' => 'user/1',
    'headers' => [
        'X-FROM' => 'CurlClient'
    ],
    'cookies' => [
        'sid' => '1234567890'
    ]
]);

echo $res;
```