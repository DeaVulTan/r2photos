<?php
include_once( Site::GetParms( 'tablesPath' )."Users.php" );
class SocialEngine {
   var $name; var $parms;
   function SocialEngine($name, $parms) { $this->name = $name; $this->parms = $parms; }

   function run()
   {
    return $this->doLoginBySocial();
   }

   //список используемых соцсетей
   public function GetList()
   {
    return array(
        'AuthVK' => array( 'social/confirm/vk' ),
        'AuthFB' => array( 'social/confirm/fb' ),
        'AuthTW' => array( 'social/init/tw', 'social/confirm/tw' ),
        'AuthOD' => array( 'social/confirm/od' ),
    );
   }

   //Авторизация/регистрация
   private function doLoginBySocial()
   {
    switch( $this->parms['action'] )
    {
        case 'confirm':
        {
            switch( $this->parms['type'] )
            {
                case 'vk':
                {
                    include_once( Site::GetParms( 'libPath' )."social/AuthVK.php" );
                    $vkApi = new AuthVK();
                    if( $vkApi->DoAuth() )
                    {
                        $userInfo = $vkApi->GetUserInfo();

                        $user = Users::GetRow( array( 'vk_id' => $userInfo->uid ) );
                        if( !$user['id'] )  //добавление нового пользователя
                        {
                            $userName = ( $userInfo->last_name ).' '.( $userInfo->first_name );
                            $passwd = $vkApi->GetMd5Hash();
                            $user = Users::Get();
                            $user->SetValue( 'idate', time() );
                            $user->SetValue( 'fio', $userName );
                            $user->SetValue( 'password', $passwd );
                            $user->SetValue( 'is_active', 1 );
                            $user->SetValue( 'vk_id', $userInfo->uid );
                            //$user->SetValue( 'type', 1 );
                            $user->StoreRow();
                        }

                        unset( $vkApi );
                        $retUrl = (Site::GetSession('callback-url-social-auth') ? Site::GetSession('callback-url-social-auth') : '/cabinet');
                        Site::SetSession('callback-url-social-auth', '');
                        return $retUrl;
                    }
                    return $_SERVER['HTTP_REFERER'];
                }
                break;//vk
                case 'fb':
                {
                    include_once( Site::GetParms( 'libPath' )."social/AuthFB.php" );
                    $fbApi = new AuthFB();
                    if( $fbApi->DoAuth() )
                    {
                        $userInfo = $fbApi->GetUserInfo();

                        $user = Users::GetRow( array( 'fb_id' => $userInfo->uid ) );
                        if( !$user['id'] )  //добавление нового пользователя
                        {
                            $userName = ( $userInfo->last_name ).' '.( $userInfo->first_name );
                            $passwd = $fbApi->GetMd5Hash();
                            $user = Users::Get();
                            $user->SetValue( 'idate', time() );
                            $user->SetValue( 'fio', $userName );
                            $user->SetValue( 'password', $passwd );
                            $user->SetValue( 'is_active', 1 );
                            $user->SetValue( 'fb_id', $userInfo->uid );
                            //$user->SetValue( 'type', 1 );
                            $user->StoreRow();
                        }

                        unset( $fbApi );
                        $retUrl = (Site::GetSession('callback-url-social-auth') ? Site::GetSession('callback-url-social-auth') : '/cabinet');
                        Site::SetSession('callback-url-social-auth', '');
                        return $retUrl;
                    }
                    return $_SERVER['HTTP_REFERER'];
                }
                break;//fb
                case 'tw':
                {
                    include_once( Site::GetParms( 'libPath' )."social/AuthTW.php" );
                    $twApi = new AuthTW();
                    if( $twApi->DoAuth( $_REQUEST ) )
                    {
                        $userInfo = $twApi->GetUserInfo();

                        $user = Users::GetRow( array( 'tw_id' => $userInfo->uid ) );
                        if( !$user['id'] )  //добавление нового пользователя
                        {
                            $userName = $userInfo->name;
                            $passwd = $twApi->GetMd5Hash();
                            $user = Users::Get();
                            $user->SetValue( 'idate', time() );
                            $user->SetValue( 'fio', $userName );
                            $user->SetValue( 'password', $passwd );
                            $user->SetValue( 'is_active', 1 );
                            $user->SetValue( 'tw_id', $userInfo->uid );
                            //$user->SetValue( 'type', 1 );
                            $user->StoreRow();
                        }

                        unset( $twApi );
                        $retUrl = (Site::GetSession('callback-url-social-auth') ? Site::GetSession('callback-url-social-auth') : '/cabinet');
                        Site::SetSession('callback-url-social-auth', '');
                        return $retUrl;
                    }
                    return $_SERVER['HTTP_REFERER'];
                }
                break;//tw
                case 'od':
                {
                    include_once( Site::GetParms( 'libPath' )."social/AuthOD.php" );
                    $odApi = new AuthOD();
                    if( $odApi->DoAuth() )
                    {
                        $userInfo = $odApi->GetUserInfo();

                        $user = Users::GetRow( array( 'od_id' => $userInfo->uid ) );
                        if( !$user['id'] )  //добавление нового пользователя
                        {
                            $userName = ( $userInfo->last_name ).' '.( $userInfo->first_name );
                            $passwd = $odApi->GetMd5Hash();
                            $user = Users::Get();
                            $user->SetValue( 'idate', time() );
                            $user->SetValue( 'fio', $userName );
                            $user->SetValue( 'password', $passwd );
                            $user->SetValue( 'is_active', 1 );
                            $user->SetValue( 'od_id', $userInfo->uid );
                            //$user->SetValue( 'type', 1 );
                            $user->StoreRow();
                        }

                        unset( $odApi );
                        $retUrl = (Site::GetSession('callback-url-social-auth') ? Site::GetSession('callback-url-social-auth') : '/cabinet');
                        Site::SetSession('callback-url-social-auth', '');
                        return $retUrl;
                    }
                    return $_SERVER['HTTP_REFERER'];
                }
                break;//od
            }
        }//confirm
        break;
        case 'init':
            include_once( Site::GetParms( 'libPath' )."social/AuthTW.php" );
            $twApi = new AuthTW();
            return $twApi->InitAuth();
        break;
    }//switch action
    return false;
   }//doLoginBySocial

   //Возвращает список первоначальных ссылок, начинающих авторизацию
   //[out]
   //   array(
   //       'AuthVK'
   //       'AuthFB'
   //       'AuthTW'
   //       ...            
   //   )
   public function GetLinks()
   {
    $res = array();
    $types = $this->GetList();
    foreach( $types as $soc => $links )
    {
        include_once( Site::GetParms( 'libPath' )."social/".$soc.".php" );
        $api = new $soc();
        $res[ $soc ] = $api->GetLoginLink( $links[ 0 ], $links[ 1 ] );
        unset( $api );
    }

    return $res;
   }//GetLinks

   //сброс всех куков
   public function ResetCookie()
   {
    $types = $this->GetList();
    foreach( $types as $soc => $links )
    {
        include_once( Site::GetParms( 'libPath' )."social/".$soc.".php" );
        $api = new $soc();
        $api->ResetCookie();
        unset( $api );
    }
   }//ResetCookie

   //проверка на авторизованность в одной из соц. сетей
   //[in+out]
   public function IsAuthorized( &$md5Hash, &$userInfo )
   {
    $types = $this->GetList();

    foreach( $types as $soc => $links )
    {
        include_once( Site::GetParms( 'libPath' )."social/".$soc.".php" );
        $api = new $soc();
        if( $api->IsAuthorized() )
        {
            $md5Hash = $api->GetMd5Hash();
            $userInfo = $api->GetUserInfo();
            unset( $api );
            return true;
        }
    }

    return false;
   }//IsAuthorized
};
