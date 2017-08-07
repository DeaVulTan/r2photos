<?php
//Авторизация с помощью twitter используя oauth
//Проходит в 2 стадии:
//1. формируем токен с помощью AuthTW::InitAuth => пользователь редиректится на страницу авторизации => возвращается на 2й скрипт
//2. завершается авторизация с помощью InitAuth::DoAuth

include_once( Site::GetParms( 'libPath' ).( 'social/AuthSocial.php' ) );

class AuthTW extends AuthSocial
{
    private $oauth_consumer_key;
    private $consumer_secret;
    //https://dev.twitter.com/apps/5443874/show
    function __construct()
    {
        $this->oauth_consumer_key   = 'iC5EIXhILpPvJ1yv7YwYsw';
        $this->consumer_secret      = 'rEidlRmIKkgNOtQKbhdbfiHRb0Y4cDnrR16Y8QAs';
        $this->socialPrefix         = 'tw';
        $this->InitCookies();
    }

    //формирование url'а, который отвечает за авторизацию
    //т.е. пользователь видит кнопку "вконтакта", жмёт её и попадает на этот url.
    //далее сервис перенаправляет пользователя на $authScript, передавая get-параметры
    //скрипт $authScript получает code, используя его curl-ом получает токен, используя который можно уже работать с API
    //[in]:
    //  $authScript - адрес скрипта, отвечающего за авторизацию/регистрацию (например, login/confirm/fb)
    public function GetLoginLink( $initScript, $authScript )
    {
        return Site::GetBaseRef().$initScript.'?refer='.( $authScript );
    }//GetLoginLink

    //Инициализация данных перед аутентификацией пользователя (генерируется временный токен)
    public function InitAuth()
    {
        
        include_once( Site::GetParms( 'libPath' ).'social/twitter/twitteroauth.php' );
        $connection = new TwitterOAuth( $this->oauth_consumer_key, $this->consumer_secret );
        $temporary_credentials = $connection->getRequestToken( Site::GetBaseRef().( $_GET['refer'] ) );
        Site::SetSession( 'oauth_token', $temporary_credentials['oauth_token'] );
        Site::SetSession( 'oauth_token_secret', $temporary_credentials['oauth_token_secret'] );
        $redirect_url = $connection->getAuthorizeURL( $temporary_credentials, FALSE );  //FALSE - авторизуется ползователь, TRUE - приложение
        

        /*
        $data = self::GetJSONFromHTTPS( 'https://api.twitter.com/oauth/request_token', false, false, array(
            'oauth_consumer_key'    => $this->oauth_consumer_key,
            'oauth_nonce' => $this->GenerateNonce(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_token' => '',//$oauth_access_token,
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0',
        ) );
        echo '<pre>';
        var_dump( $data );
        die();
        */

        return $redirect_url;
    }

    function GenerateNonce()
    {
        function make_seed() {
            list( $usec, $sec ) = explode( ' ', microtime() );
            return ( ( float ) $sec + ( ( float ) $usec * 100000 ) );
        }
        mt_srand( make_seed() );
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $size = strlen( $chars );
        for( $i = 0; $i < 32; ++$i ) $str .= $chars[ mt_rand( 0, $size - 1 ) ];
        return $str;
    }

    //DoAuth проверяет get-параметры, полученные от сервиса oauth и проделывает аутентификацию
    //[in]
    //  $get - перечень полученных get-параметров
    //[out]
    //  boolean: результат авторизации (true - ок, false - фейл)
    public function DoAuth( $get = false )
    {
        if( $get == false )
            $get = $_GET;
        if( strlen( $get['error'] ) ) {
            return false;
        } else {
            include_once( Site::GetParms( 'libPath' ).'social/twitter/twitteroauth.php' );
            $connection = new TwitterOAuth( $this->oauth_consumer_key, $this->consumer_secret, Site::GetSession( 'oauth_token' ), Site::GetSession( 'oauth_token_secret' ) );
            $token_credentials = $connection->getAccessToken( $_REQUEST['oauth_verifier'] );

            //берём дополнительную инфу о пользователе
            $content = $connection->get('account/verify_credentials');

            $userInfo = new UserInfo();
            $userInfo->uid = $token_credentials['user_id'];
            $userInfo->name = $content->name;
            $this->cookies['user_info'] = $userInfo;
            setcookie( $this->cookiesName, urlencode( serialize( $this->cookies ) ), time() + 60*60*24*30, '/' );
            return true;
        }
        return false;
    }//DoAuth
};