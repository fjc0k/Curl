<?php
/**
 * Created by PhpStorm.
 * User: 方剑成
 * Date: 2016/12/11
 * Time: 15:20
 */

namespace Funch\Curl;

use CURLFile;

class CurlClient
{
    /**
     * @var string
     */
    protected static $baseUrl = '';

    const DEFAULT_TIMEOUT = 15;


    /**
     * @param array $args
     * @param bool $raw
     * @return mixed
     * @throws CurlException
     * @throws CurlTimeoutException
     */
    public static function request(array $args, $raw = false)
    {
        if (!extension_loaded('curl'))
            throw new CurlException('cURL library is not loaded');

        $url =
        $post =
        $query =
        $cookies =
        $headers =
        $cookie_jar =
        $referer =
        $user_agent =
        $timeout =
        $proxy =
        $json =
        $files =
            null;
        $follow_location = true;

        extract($args);

        if (is_null($url)) throw new InvalidArgumentException('url is null');
        $full_url = self::$baseUrl . $url;
        $full_url = $query ? (
            strpos($full_url, '?') !== false ? '&' : '?'
            ).http_build_query($query) : '';
        $host = parse_url($full_url, PHP_URL_HOST);
        if (!$host) throw new InvalidArgumentException('invalid url: ' . $full_url);

        // curl start
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $full_url);
        curl_setopt($ch, CURLOPT_HEADER, $raw);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);

        // set headers
        $headers = self::generateHeaders($headers);
        $headers[] = 'Host: ' . $host;
        $headers[] = 'User-Agent: ' . ($user_agent ?: 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:32.0) Gecko/20100101 Firefox/32.0');
        $headers[] = 'Referer: ' . ($referer ?: $full_url);
        if ($json) {
            $headers[] = 'Content-Type: application/json';
        }
        if ($cookies) {
            $headers[] = 'Cookie: ' . self::generateCookies($cookies);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // set cookie jar
        if ($cookie_jar) {
            if (!file_exists($cookie_jar)) {
                file_put_contents($cookie_jar, '');
            }
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
        }

        // set follow location
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow_location);

        // set proxy
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }

        // set timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout ?: self::DEFAULT_TIMEOUT);

        // set file
        if ($files) {
            array_walk($files, function (&$file) {
                $file = new CURLFile($file, null, basename($file));
            });
            $post = $post ? array_merge($post, $files) : $files;
        } else {
            $post && ($post = is_array($post) ? http_build_query($post) : $post);
        }

        // set post/json
        if ($post || $json) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                $json ? json_encode($json, JSON_UNESCAPED_UNICODE) : (is_string($post) ? $post : http_build_query($post))
            );
        }

        // send request
        $response = curl_exec($ch);

        if ($response === false) {
            if (curl_errno($ch) == 28)
                throw new CurlTimeoutException('cURL request timeout');
            else
                throw new CurlException('cURL error: ' . curl_error($ch));
        }

        curl_close($ch);
        return $response;

    }


    /**
     * @param array $args
     * @param bool $raw
     * @return mixed
     * @throws CurlException
     */
    public static function json(array $args, $raw = false)
    {
        $res = self::request($args, $raw);
        $json = json_decode($res);
        if (JSON_ERROR_NONE !== json_last_error())
            throw new CurlException('JSON error: ' . json_last_error_msg() . ' (' . $res . ')');
        return $json;
    }


    /**
     * @param $cookies
     * @return string
     */
    private static function generateCookies($cookies)
    {
        return is_array($cookies) ? http_build_query($cookies, '', '; ') : $cookies;
    }

    /**
     * @param $headers
     * @return array
     */
    private static function generateHeaders($headers)
    {
        if (is_string($headers)) return preg_split('/[\r\n]{1,2}+/', $headers);
        if (is_array($headers)) {
            $t = [];
            foreach ($headers as $k => $v) {
                $t[] = "{$k}: {$v}";
            }
            return $t;
        }
        return [];
    }

    /**
     * @return string
     */
    public static function getBaseUrl()
    {
        return self::$baseUrl;
    }

    /**
     * @param string $baseUrl
     */
    public static function setBaseUrl($baseUrl)
    {
        self::$baseUrl = $baseUrl;
    }


}