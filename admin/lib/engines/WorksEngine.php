<?php
class WorksEngine extends AdminEngine {
   function WorksEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Works::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Works::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         if (defined('LANG')) Site::SetSession($this->name.'-lang', $_POST['lang']);
         return Site::CreateUrl($this->name.'-list');
      }
      return;
   }

   function getDataStr($name, $item) {
      echo date("d.m.Y", $item[$name]);
   }

    function getPhotographersStr( $name, $item ) {
        include_once( Site::GetParms( 'libPath' ).'Cataloglinksmgr.php' );
        $links = new Cataloglinksmgr();
        $type = $links->clType('works');
        $linksTo = $links->getCountLinksFrom($type, $item['id']);
        echo '['.$linksTo[$itemType = $links->clType('photographers')].' - <a href="cataloglinks-to_'.$type.'_'.$item['id'].'_'.$itemType.'.htm">смотреть</a>]<br />';
        unset( $links );
    }//getPhotographersStr

   function doBeforeRun() {
      if (IS_PHOTOGRAPHERS !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'name');
         Site::SetSession($this->name."-order", 'asc');
      }
   }
}
?>