<?php
class LogoutEngine {
   var $name; var $parms;
   function LogoutEngine($name, $parms) { $this->name = $name; $this->parms = $parms; }

   function run() {
      $nameHash = strtolower(PROJECT_NAME).'_hash';
      setcookie($nameHash, false, 0, '/');

      if (Site::GetParms('social_authorization')) {
          $engine = Site::GetEngine( 'social' );
          $engine->ResetCookie();
          unset($engine);
          Site::SetParms( 'userInfo', '' );
      }
      
      return Site::GetBaseRef();
   }
}
?>