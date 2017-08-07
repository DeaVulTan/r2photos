<?php
//Авторизация с помощью соц. сетей
class AuthSocial
{
    protected $cookiesName;     //наименование кукисов
    protected $cookies;         //текущие значения куки
    protected $socialPrefix;    //префикс для наименования куков и прочего

    //формирование url'а, который отвечает за авторизацию
    //т.е. пользователь видит кнопку "вконтакта", жмёт её и попадает на этот url.
    //далее сервис перенаправляет пользователя на $authScript, передавая get-параметры
    //скрипт $authScript получает code, используя его curl-ом получает токен, используя который можно уже работать с API
    //[in]:
    //  $authScript - адрес скрипта, отвечающего за авторизацию/регистрацию (например, login/confirm/vk)
    public function GetLoginLink( $authScript )
    {
        return false;
    }

    //DoAuth проверяет get-параметры и проделывает аутентификацию
    //[in]
    //  $get - перечень полученных get-параметров
    //[out]
    //  boolean: результат авторизации (true - ок, false - фейл)
    public function DoAuth( $get = false )
    {
        return false;
    }//DoAuth

    //Возвращает информацию о пользователе (если он авторизован)
    public function GetUserInfo()
    {
        return $this->cookies['user_info'];
    }//GetUserInfo

    //Проверка авторизации пользователя в соц. сети
    public function IsAuthorized()
    {
        return ( $this->cookies['user_info']->uid > 0 );
    }//IsAuthorized

    //генерация хеша. его можно использовать в `users`.`password` для авторизации в LoginEngine.php
    public function GetMd5Hash()
    {
        return md5( '-'.( $this->socialPrefix ).'-'.( $this->cookies['user_info']->uid ).'-' );
    }//GetMd5Hash

    //сброс куков
    public function ResetCookie()
    {
        $this->cookies = false;
        setcookie( $this->cookiesName, '', 0, '/' );
    }//ResetCookie

    //Забирает HTTPS-страницу, которая отдаёт JSON, и возвращает объект json_decode от результата
    static public function GetJSONFromHTTPS( $url, $isPost = false, $postData = array(), $oAuth = false )
    {
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL,              $url );
        curl_setopt( $ch, CURLOPT_HEADER,           false );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER,   true );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT,   30 );
        if( substr( $url, 0, 5 ) == 'https' )
        {
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER,   false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST,   false );
        }
        if( $isPost )
        {
            curl_setopt( $ch, CURLOPT_POST, true );
            /*
            $postStr = array();
            foreach( $postData as $name => $value )
                $postStr[] = ( rawurlencode( $name ).'='.rawurlencode( $value ) );
            */
            //curl_setopt( $ch, CURLOPT_POSTFIELDS, implode( '&', $postStr ) );
            $requestString = http_build_query( $postData, '', '&' );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $requestString );
        }
        if( $oAuth != false )
        {
            $base_info = self::buildBaseString( $url, 'POST', $oAuth );
            $composite_key = rawurlencode($consumer_secret) . '&' . rawurlencode($oauth_access_token_secret);
            $oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
            $oAuth['oauth_signature'] = $oauth_signature;
            //$header = array( self::buildAuthorizationHeader( $oAuth ) /*, 'Expect:' */ );
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, self::buildAuthorizationHeader( $oAuth ) );
            //curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            echo 'header: ';
            echo '<pre>';print_r($header);
            //die();
        }
        $res = curl_exec( $ch );
        //var_dump($res);die();
        $data = json_decode( $res );
        curl_close( $ch );
        return $data;
    }//GetJSONFromHTTPS

    static public function buildAuthorizationHeader( $oauth )
    {
        $r = 'Authorization: OAuth ';
        $values = array();
        foreach( $oauth as $key=>$value )
            $values[] = "$key=\"" . rawurlencode($value) . "\"";
        $r .= implode( ', ', $values );
        return $r;
    }

    static public function buildBaseString( $baseURI, $method, $params )
    {
        $method = strtoupper( $method );
        $r = array();
        ksort( $params );
        foreach( $params as $key=>$value )
            $r[] = "$key=" . rawurlencode( $value );
        return $method."&" . rawurlencode( $baseURI ) . '&' . rawurlencode( implode( '&', $r ) );
    }









    //чтение инфы из куков
    protected function InitCookies()
    {
        $this->cookiesName  = md5( PROJECT_NAME.'-'.( $this->socialPrefix ).'-auth' );
        $this->cookies      = unserialize( urldecode( $_COOKIE[ $this->cookiesName ] ) );
    }//InitCookies
};

class UserInfo
{
    public $uid;
    public $last_name;
    public $first_name;
};
