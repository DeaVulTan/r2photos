<?php
class LoginEngine {
   var $name; var $parms;
   function LoginEngine($name, $parms) { $this->name = $name; $this->parms = $parms; }

   function run() {
      if (!Site::GetParms('authorization')) return true;
      $nameHash = mb_strtolower(PROJECT_NAME, 'utf-8').'_hash';
      $login = (isset($_POST['logon_login']) && $_POST['logon_login'] ? $_POST['logon_login'] : '');
      $passwd = (isset($_POST['logon_passwd']) && $_POST['logon_passwd'] ? $_POST['logon_passwd'] : '');
      if ((trim($login) && !preg_match("/^[0-9A-Za-z\._-]+\@([0-9a-z_-]+\.)+[a-z]{2,4}$/i", $login)) || (trim($passwd) && !preg_match("/^([0-9a-zA-Z_]+)$/i", $passwd))) {
        Site::SetSession( 'r2photos-site-error', serialize( array( 'error' => 'Введены некорректные данные.', 'data' => $_REQUEST ) ) );
        return 'login'.(isset($_REQUEST['refer']) && $_REQUEST['refer'] ? '?refer='.$_REQUEST['refer'] : '');
      }
      $md5Hash = ($login && $passwd ? md5($login.':'.$passwd) : (isset($_COOKIE[$nameHash]) && $_COOKIE[$nameHash] ? $_COOKIE[$nameHash] : ''));
      if ($login && $passwd) setcookie($nameHash, '', 0, '/');
      if( isset( $_REQUEST['autologin_idmd5'] ) && !empty( $_REQUEST['autologin_idmd5'] ) ) {
        $md5Hash = $_REQUEST['autologin_idmd5'];
      }
      
      //соцсети
      if (Site::GetParms('social_authorization')) {
          if(!strlen($md5Hash)) {
            $engine = Site::GetEngine( 'social' );
            $engine->IsAuthorized( $md5Hash, $userInfo );
            unset( $engine );
          }
      }
      //-----//
      
      if ($md5Hash) {
         include_once(Site::GetParms('tablesPath')."Users.php");
         $user = Users::GetRow(array('idmd5' => $md5Hash, 'active' => 1));
         if ($user['id'] > 0) {
            //соцсети
            if (Site::GetParms('social_authorization')) {
                if ($user['vk_id']) $user['vk'] = $userInfo;
                if ($user['fb_id']) $user['fb'] = $userInfo;
                if ($user['tw_id']) $user['tw'] = $userInfo;
                if ($user['od_id']) $user['od'] = $userInfo;
                Site::SetParms( 'userInfo', $user );
            }
            //-----//
            
            setcookie($nameHash, $md5Hash, time() + 60*60*24*30, '/');
            define('UID', (int)$user['id']);
            Site::SetParms( 'userInfo', $user );
            if (isset($_REQUEST['refer']) && $_REQUEST['refer'] && preg_match("/^([0-9a-z\-\/]+)$/i", $_REQUEST['refer'])) header("Location: ".urldecode($_REQUEST['refer']));
         }
         else {
            setcookie( $nameHash, '', 0, '/' );
            Site::SetSession( 'r2photos-site-error', serialize( array( 'error' => 'Пользователь не существует либо введены некорректные данные', 'data' => $_REQUEST ) ) );
            if( Site::GetParms( 'isAjax' ) ) {
                return 'login'.(isset($_REQUEST['refer']) && $_REQUEST['refer'] ? '?refer='.$_REQUEST['refer'] : '');
            }
         }
      }
      return true;
   }
}
?>
