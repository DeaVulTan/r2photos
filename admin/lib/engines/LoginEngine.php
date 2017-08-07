<?php
include_once(Site::GetParms('tablesPath')."Admins.php");
class LoginEngine {
   var $name; var $parms;
   function LoginEngine($name, $parms) { $this->name = $name; $this->parms = $parms; }

   function run() {
      $nameHash = mb_strtolower(PROJECT_NAME, 'utf-8').'_admin_hash-'.md5(date("Y").'-'.Site::GetParms('randomHash'));
      $login = (isset($_POST['logon_login']) && $_POST['logon_login'] ? $_POST['logon_login'] : '');
      $passwd = (isset($_POST['logon_passwd']) && $_POST['logon_passwd'] ? $_POST['logon_passwd'] : '');
      if ((trim($login) && !preg_match("/^([0-9a-zA-Z_\-]+)$/", $login)) || (trim($passwd) && !preg_match("/^([0-9a-zA-Z_\-]+)$/", $passwd))) return 'login.htm';
      $md5Hash = ($login && $passwd ? md5($login.':'.md5('-'.$passwd.'-')) : $_COOKIE[$nameHash]);
      if ($login && $passwd) setcookie($nameHash, '');
      if ($md5Hash) {
         $parms = array('active' => 1);
         $parms['idmd5'] = $md5Hash;
         $admin = Admins::GetRow($parms);
         if ($admin['id'] > 0) {
            if (!$_COOKIE[$nameHash]) setcookie($nameHash, $md5Hash);
            define('UID_ADMIN', (int)$admin['id']);
            define('UID_FIO', $admin['fio']);
            define('IS_ADMINS', ($admin['is_admins'] ? true : false));
            define('IS_CONFIG', ($admin['is_config'] ? true : false));
            define('IS_META', ($admin['is_meta'] ? true : false));
            define('IS_MENU', ($admin['is_menu'] ? true : false));
            define('IS_MENU_', ($admin['menus'] ? $admin['menus'] : false));
            define('IS_NEWS', ($admin['is_news'] ? true : false));
            define('IS_ARTICLES', ($admin['is_articles'] ? true : false));
            define('IS_CATALOG', ($admin['is_catalog'] ? true : false));
            define('IS_USERS', ($admin['is_users'] ? true : false));
            define('IS_FAQ', ($admin['is_faq'] ? true : false));
            define('IS_OPINIONS', ($admin['is_opinions'] ? true : false));
            define('IS_FORUM', ($admin['is_forum'] ? true : false));
            define('IS_ORDERS', ($admin['is_orders'] ? true : false));
            define('IS_BAN', ($admin['is_ban'] ? true : false));
            define('IS_VACS', ($admin['is_vacs'] ? true : false));
            define('IS_VOTES', ($admin['is_votes'] ? true : false));
            define('IS_SUBSCRIBE', ($admin['is_subscribe'] ? true : false));
            define('IS_LICENCE', ($admin['is_licence'] ? true : false));
            define('IS_PARTNERS', ($admin['is_partners'] ? true : false));
            define('IS_PHOTO', ($admin['is_photo'] ? true : false));
            define('IS_CALLBACK', ($admin['is_callback'] ? true : false));
            define('IS_ACTIONS', ($admin['is_actions'] ? true : false));
            define('IS_PHOTOGRAPHERS', ($admin['is_photographers'] ? true : false));
            define('IS_ACTIVATION', ($admin['is_activation'] ? true : false));
            define('IS_LOCATIONS', ($admin['is_locations'] ? true : false));
            define('IS_RETOUCHES', ($admin['is_retouches'] ? true : false));
            return true;
         }
      }
      return 'login.htm';
   }
}
?>
