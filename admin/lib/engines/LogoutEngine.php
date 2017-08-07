<?php
class LogoutEngine {
   var $name; var $parms;
   function LogoutEngine($name, $parms) { $this->name = $name; $this->parms = $parms; }

   function run() {
      $nameHash = mb_strtolower(PROJECT_NAME, 'utf-8').'_admin_hash-'.md5(date("Y").'-'.Site::GetParms('randomHash'));
      setcookie($nameHash, '', 0);
      return 'login.htm';
   }
}
?>
