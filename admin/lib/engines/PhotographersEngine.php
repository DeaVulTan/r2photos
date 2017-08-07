<?php
class PhotographersEngine extends AdminEngine {
   function PhotographersEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Photographers::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Photographers::GetRows($parms, $limit, $order);
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

   function getPhotosStr( $name, $item ) {
    $count = Photoimages::GetCountRows( array( 'parts_id' => $item['id'] ) );
    echo '['.( $count ).' - <a href="photoimages-filter_'.( $item['id'] ).'.htm">смотреть</a>]';
   }//getPhotosStr

    function getItemsStr( $name, $item ) {
        include_once( Site::GetParms( 'libPath' ).'Cataloglinksmgr.php' );
        $links = new Cataloglinksmgr();
        $type = $links->clType('photographers');
        $linksTo = $links->getCountLinksFrom($type, $item['id']);
        echo '['.$linksTo[$itemType = $links->clType('items')].' - <a href="cataloglinks-to_'.$type.'_'.$item['id'].'_'.$itemType.'.htm">смотреть</a>]<br />';
        unset( $links );
    }//getItemsStr

    function getWorksStr( $name, $item ) {
        include_once( Site::GetParms( 'libPath' ).'Cataloglinksmgr.php' );
        $links = new Cataloglinksmgr();
        $type = $links->clType('photographers');
        $linksTo = $links->getCountLinksFrom($type, $item['id']);
        echo '['.$linksTo[$itemType = $links->clType('works')].' - <a href="cataloglinks-to_'.$type.'_'.$item['id'].'_'.$itemType.'.htm">смотреть</a>]<br />';
        unset( $links );
    }//getWorksStr

   function doBeforeUpdateData() {
    $thisUrl = Utils::Translit($this->table->data['href']);
    if (!$thisUrl) $thisUrl = Utils::Translit($this->table->data['name']);
    $this->table->SetValue('href', $thisUrl);
    return;
   }

   function doBeforeRun() {
      if (IS_PHOTOGRAPHERS !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'ord');
         Site::SetSession($this->name."-order", 'asc');
      }
   }
}
?>