<?php

// php -S localhost:8000

$t = isset($_GET['t']) ? $_GET['t'] : '';

switch ($t){
    case 'get':
        echo $_GET['time'];
        break;
    case 'post':
        echo $_POST['time'];
        break;
    case 'follow_location':
        header('Location: '.$_SERVER['SCRIPT_NAME'].'?t=follow_location_result&time='.$_GET['time']);
        break;
    case 'follow_location_result':
        echo $_GET['time'];
        break;
    case 'post_json':
        echo file_get_contents('php://input');
        break;
    case 'parse_json':
        echo json_encode(['time' => $_GET['time']]);
        break;
    case 'timeout':
        sleep($_GET['timeout'] + 10);
        break;
    case 'user_agent':
        echo $_SERVER['HTTP_USER_AGENT'];
        break;
    case 'cookies':
        echo $_COOKIE['foo'].'.'.$_COOKIE['hello'];
        break;
    case 'cookie_jar':
        setcookie('Funch-Curl-Cookie-Jar', $_GET['set_cookie_value'], time() + 300);
        break;
    case 'cookie_jar_result':
        echo $_COOKIE['Funch-Curl-Cookie-Jar'];
        break;
    case 'server':
        print_r($_SERVER);
        break;
    default:
        break;
}