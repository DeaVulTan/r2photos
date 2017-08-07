<?php
//Авторизация с помощью facebook используя oauth

include_once( Site::GetParms( 'libPath' ).( 'social/AuthSocial.php' ) );

class AuthFB extends AuthSocial
{
    private $appId;         //id приложения (сайта)
    private $sectetKey;     //защищенный код приложения
    private $urlConfirm;    //url окончания авторизации
    
    
    //https://developers.facebook.com/apps/183101611892841/
    function __construct()
    {
        $this->appId        = '183101611892841';
        $this->sectetKey    = '65a175a0bda40585b7a78bd7c0621fde';
        $this->socialPrefix = 'fb';
        $this->urlConfirm   = Site::GetBaseRef().'social/confirm/fb';
        $this->InitCookies();
    }

    //формирование url'а, который отвечает за авторизацию
    //т.е. пользователь видит кнопку "вконтакта", жмёт её и попадает на этот url.
    //далее сервис перенаправляет пользователя на $authScript, передавая get-параметры
    //скрипт $authScript получает code, используя его curl-ом получает токен, используя который можно уже работать с API
    //[in]:
    //  $authScript - адрес скрипта, отвечающего за авторизацию/регистрацию (например, login/confirm/fb)
    public function GetLoginLink( $authScript )
    {
        return 'http://www.facebook.com/dialog/oauth/?client_id='.( $this->appId ).'&redirect_uri='.( Site::GetBaseRef().$authScript );
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
            $data = $this->GetDataFromHTTPS( 'https://graph.facebook.com/oauth/access_token?client_id='.( $this->appId ).'&client_secret='.( $this->sectetKey ).'&code='.( $get['code'] ).'&redirect_uri='.( $this->urlConfirm ) );
            if( strlen( $data['access_token'] ) )   //получили токен
            {
                //берём инфу о пользователе (имя, фамилия)
                $info = self::GetJSONFromHTTPS( 'https://graph.facebook.com/me?access_token='.( $data['access_token'] ) );
                $info->uid = $info->id;
                $this->cookies['user_info'] = $info;
                setcookie( $this->cookiesName, urlencode( serialize( $this->cookies ) ), time() + 60*60*24*30, '/' );
                return true;
            }
        }
        return false;
    }//DoAuth

    //Забирает HTTPS-страницу, которая отдаёт POST, и возвращает хэш-массив
    private function GetDataFromHTTPS( $url )
    {
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL,              $url );
        curl_setopt( $ch, CURLOPT_HEADER,           false );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER,   true );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT,   30 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER,   false );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST,   false );
        $data = curl_exec( $ch );
        curl_close( $ch );
        if( strlen( $data ) )
        {
            $fields = explode( '&', $data );
            $data = array();
            foreach( $fields as $field )
            {
                $info = explode( '=', $field );
                if( count( $info ) > 1 )
                    $data[ $info[ 0 ] ] = $info[ 1 ];
                else
                    $data[ $field ] = $field;
            }
        }
        return $data;
    }//GetJSONFromHTTPS
};