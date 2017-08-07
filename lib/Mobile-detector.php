<?php
// Используется API Yandex Detector

if (preg_match('/iPad/', $_SERVER['HTTP_USER_AGENT'])) goMobile();
else {
    $detect = detectMobile();
    if (preg_match('/<yandex-mobile-info>/', $detect)) goMobile();
}

function detectMobile() {
    $headers = array();
    $hmask = array(
        'profile',
        'wap-profile',
        'x-wap-profile',
        'user-agent',
        'x-operamini-phone-ua',
    );
     
    foreach ($_SERVER as $key => $value) {
        if (strpos($key, "HTTP_") === 0) {
            $field = substr($key, 5);
            $field = strtolower($field);
            $field = str_replace('_', '-', $field);
            if (in_array($field, $hmask)) $headers[$field] = $value;
        }
    }
     
    $res = '';
    $ch = curl_init('http://phd.yandex.net/detect?'.http_build_query($headers));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $res = curl_exec($ch);
    curl_close($ch);
    unset($headers);
    return $res;
}

function goMobile() {
    header("Location: http://mobile.site.ru/");
    die;
}
?>