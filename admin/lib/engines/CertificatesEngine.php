<?php
class CertificatesEngine extends AdminEngine {
   function CertificatesEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Certificates::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Certificates::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         if (defined('LANG')) Site::SetSession($this->name.'-lang', $_POST['lang']);
         return Site::CreateUrl($this->name.'-list');
      }
      return;
   }

   function getImageStr( $name, $item ) {
    $fileName = $item[ $name ];
    echo ( !empty( $fileName ) && file_exists( '../'.$fileName ) ? '<img style="max-width: 300px;" src="../'.( $fileName ).'" />' : '-' );
   }//getImageStr

   function getDataStr($name, $item) {
      echo date("d.m.Y", $item[$name]);
   }

   function getTypesStr($name, $item) {
       $count = Certificatestypes::GetCountRows( array( 'certificate_id' => $item['id'] ) );
       echo '['.( $count ).' - <a href="certificatestypes-filter_'.( $item['id'] ).'.htm">смотреть</a>]';
   }

   function getItemsStr( $name, $item ) {
    include_once( Site::GetParms( 'libPath' ).'Cataloglinksmgr.php' );
    $links = new Cataloglinksmgr();
    $type = $links->clType('certificates');
    $linksTo = $links->getCountLinksFrom($type, $item['id']);
    echo '['.$linksTo[$itemType = $links->clType('items')].' - <a href="cataloglinks-to_'.$type.'_'.$item['id'].'_'.$itemType.'.htm">смотреть</a>]<br />';
    unset( $links );
   }//getItemsStr

   function getLocationsStr( $name, $item ) {
    include_once( Site::GetParms( 'libPath' ).'Cataloglinksmgr.php' );
    $links = new Cataloglinksmgr();
    $type = $links->clType('certificates');
    $linksTo = $links->getCountLinksFrom($type, $item['id']);
    echo '['.$linksTo[$itemType = $links->clType('locations')].' - <a href="cataloglinks-to_'.$type.'_'.$item['id'].'_'.$itemType.'.htm">смотреть</a>]<br />';
    unset( $links );
   }//getLocationsStr

   function getPhotosStr($name, $item) {
      $count = Certificatesphotos::GetCountRows( array( 'certificate_id' => $item['id'] ) );
      echo '['.( $count ).' - <a href="certificatesphotos-filter_'.( $item['id'] ).'.htm">смотреть</a>]';
   }

   function doBeforeRun() {
      if (IS_CATALOG !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'ord');
         Site::SetSession($this->name."-order", 'asc');
      }
   }
}
?>