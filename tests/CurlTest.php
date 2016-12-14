<?php

/**
 * Created by PhpStorm.
 * User: 方剑成
 * Date: 2016/12/11
 * Time: 19:18
 */

namespace Funch\Curl\Tests;

use Funch\Curl\CurlClient as Curl;
use Funch\Curl\CurlException;
use Funch\Curl\InvalidArgumentException;
use Funch\Curl\CurlTimeoutException;


class CurlTest extends \PHPUnit_Framework_TestCase
{

    const TEST_URL = 'http://localhost:8000/test_server.php';

    private $now;

    public function setUp()
    {
        $this->now = time();
    }

    public function testGet () {
        $body = Curl::request([
            'url' => self::TEST_URL,
            'query' => [
                't' => 'get',
                'time' => $this->now
            ]
        ]);
        $this->assertEquals($this->now, $body);
    }
    public function testPost () {
        $body = Curl::request([
            'url' => self::TEST_URL,
            'query' => [
                't' => 'post'
            ],
            'post' => [
                'time' => $this->now
            ]
        ]);
        $this->assertEquals($this->now, $body);
    }
    public function testPostJSON () {
        $body = Curl::request([
            'url' => self::TEST_URL,
            'query' => [
                't' => 'post_json'
            ],
            'json' => [
                'time' => $this->now
            ]
        ]);
        $this->assertJsonStringEqualsJsonString(
            json_encode([ 'time' => $this->now ]),
            $body
        );
    }
    public function testParseJSON () {
        $jsonObj = Curl::json([
            'url' => self::TEST_URL,
            'query' => [
                't' => 'parse_json',
                'time' => $this->now
            ]
        ]);
        $this->assertObjectHasAttribute('time', $jsonObj);
        $this->assertEquals($this->now, $jsonObj->time);
    }

    public function testFollowLocation () {
        $body = Curl::request([
            'url' => self::TEST_URL,
            'query' => [
                't' => 'follow_location',
                'time' => $this->now
            ],
            'follow_location' => true
        ]);
        $this->assertEquals($this->now, $body);
    }
    public function testUserAgent () {
        $userAgent = 'Funch-Curl';
        $body = Curl::request([
            'url' => self::TEST_URL,
            'query' => [
                't' => 'user_agent'
            ],
            'user_agent' => $userAgent
        ]);
        $this->assertEquals($userAgent, $body);
    }

    public function testCookies () {
        $cookies = [
            'foo' => 'bar',
            'hello' => 'world'
        ];
        $body = Curl::request([
            'url' => self::TEST_URL,
            'query' => [
                't' => 'cookies'
            ],
            'cookies' => $cookies
        ]);
        $this->assertEquals(join('.', $cookies), $body);
    }

    public function testCookieJar () {
        $cookieJar = sys_get_temp_dir() . '/Funch-Curl-Cookie-Jar';
        Curl::request([
            'url' => self::TEST_URL,
            'query' => [
                't' => 'cookie_jar',
                'set_cookie_value' => $this->now
            ],
            'cookie_jar' => $cookieJar
        ]);
        $cookie = file_get_contents($cookieJar);
        $this->assertContains('Funch-Curl-Cookie-Jar', $cookie);

        $body = Curl::request([
            'url' => self::TEST_URL,
            'query' => [
                't' => 'cookie_jar_result'
            ],
            'cookie_jar' => $cookieJar
        ]);
        $this->assertEquals($this->now, $body);
    }

    public function testTimeout () {
        $this->expectException(CurlTimeoutException::class);
        $timeout = 2;
        Curl::request([
            'url' => self::TEST_URL,
            'query' => [
                't' => 'timeout',
                'timeout' => $timeout
            ],
            'timeout' => $timeout
        ]);
    }

    public function testCurlException ()
    {
        $this->expectException(CurlException::class);
        Curl::request([
            'url' => 'x'.self::TEST_URL
        ]);
    }

    public function testUrlException ()
    {
        $this->expectException(InvalidArgumentException::class);
        Curl::request([
            'url' => 'xxxx'
        ]);
    }

}