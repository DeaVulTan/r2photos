<?php
//Авторизация с помощью вконтакта используя oauth

include_once( Site::GetParms( 'libPath' ).( 'social/AuthSocial.php' ) );

class AuthVK extends AuthSocial
{
    private $appId;         //id приложения (сайта)
    private $sectetKey;     //защищенный код приложения
    private $urlConfirm;    //url окончания авторизации

    function __construct()
    {
        
        $this->appId        = '3651804';
        $this->sectetKey    = 'FPatlgUnPeWLTbDVRYnv';
        $this->socialPrefix = 'vk';
        $this->urlConfirm   = Site::GetBaseRef().'social/confirm/vk';
        $this->InitCookies();
    }

    //формирование url'а, который отвечает за авторизацию
    //т.е. пользователь видит кнопку "вконтакта", жмёт её и попадает на этот url.
    //далее сервис перенаправляет пользователя на $authScript, передавая get-параметры
    //скрипт $authScript получает code, используя его curl-ом получает токен, используя который можно уже работать с API
    //[in]:
    //  $authScript - адрес скрипта, отвечающего за авторизацию/регистрацию (например, login/confirm/vk)
    public function GetLoginLink( $authScript )
    {
        return 'https://oauth.vk.com/authorize?client_id='.( $this->appId ).'&scope=notify&redirect_uri='.( Site::GetBaseRef().$authScript ).'&response_type=code';
    }//GetLoginLink

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
        } else if( strlen( $get['code'] ) ) {   //получили code, с помощью которого необходимо подтвердить авторизацию и получить access_token
            //получаем access_token
            $data = self::GetJSONFromHTTPS( 'https://oauth.vk.com/access_token?client_id='.( $this->appId ).'&client_secret='.( $this->sectetKey ).'&code='.( $get['code'] ).'&redirect_uri='.( $this->urlConfirm ).'&' );
            if( strlen( $data->access_token ) && $data->user_id )   //получили токен и uid
            {
                //берём инфу о пользователе (имя, фамилия)
                $info = self::GetJSONFromHTTPS( 'https://api.vk.com/method/users.get?uids='.( $data->user_id ).'&access_token='.( $data->access_token ) );
                $this->cookies['user_info'] = $info->response[ 0 ];
                setcookie( $this->cookiesName, urlencode( serialize( $this->cookies ) ), time() + 60*60*24*30, '/' );
                return true;
            }
        }
        return false;
    }//DoAuth
};