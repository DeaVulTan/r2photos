<?php
//Авторизация с помощью одноклассников используя oauth

include_once( Site::GetParms( 'libPath' ).( 'social/AuthSocial.php' ) );

class AuthOD extends AuthSocial
{
    private $appId;             //id приложения (сайта)
    private $sectetKey;         //секретный ключ приложения
    private $application_key;   //публичный ключ приложения
    private $urlConfirm;        //url окончания авторизации
    // http://dev.odnoklassniki.ru/wiki/pages/viewpage.action?pageId=5668937
    function __construct()
    {
        $this->appId            = '176011520';
        $this->sectetKey        = '30CCE2B940EFD602115F6DD2';
        $this->application_key  = 'CBAKLOHLABABABABA';
        $this->socialPrefix     = 'od';
        $this->urlConfirm       = Site::GetBaseRef().'social/confirm/od';
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
        return 'http://www.odnoklassniki.ru/oauth/authorize?client_id='.( $this->appId ).'&scope=VALUABLE ACCESS&redirect_uri='.( Site::GetBaseRef().$authScript ).'&response_type=code';
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
        } else if( strlen( $get['code'] ) ) {   //получили code, с помощью которого необходимо получить access_token
            $data = self::GetJSONFromHTTPS( 'http://api.odnoklassniki.ru/oauth/token.do', true, array(
                'code' => $get['code'],
                'redirect_uri' => $this->urlConfirm,
                'grant_type' => 'authorization_code',
                'client_id' => $this->appId,
                'client_secret' => $this->sectetKey,
            ) );
            if( strlen( $data->access_token ) )   //получили токен
            {
                $parms = array(
                    'method'            => 'users.getCurrentUser',
                    'application_key'   => $this->application_key,
                );
                //формируем сигнатуру
                ksort( $parms );
                $signature = md5( str_replace( '%2C', ',', http_build_query( $parms, '', '' ) ).md5( $data->access_token.$this->sectetKey ) );

                $parms['access_token'] = $data->access_token;
                $parms['sig'] = $signature;
                //берём инфу о пользователе
                $info = self::GetJSONFromHTTPS( 'http://api.odnoklassniki.ru/fb.do?'.http_build_query( $parms, '', '&' ) );

                $this->cookies['user_info'] = $info;
                setcookie( $this->cookiesName, urlencode( serialize( $this->cookies ) ), time() + 60*60*24*30, '/' );
                return true;
            }
        }
        return false;
    }//DoAuth
};