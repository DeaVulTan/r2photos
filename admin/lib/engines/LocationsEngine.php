<?php
class LocationsEngine extends AdminEngine {
   function LocationsEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Locations::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Locations::GetRows($parms, $limit, $order);
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

   function getPictureStr($name, $item) {
      $file = $item['picture'];
      echo ($file && file_exists(Site::GetParms('absolutePath').$file) ? '<img src="../'.$file.'" style="max-width: 400px;" />' : 'Картинки нет.');
   }

   function getPhotosStr($name, $item) {
      $count = Locationsphotos::GetCountRows( array( 'location_id' => $item['id'] ) );
      echo '['.( $count ).' - <a href="locationsphotos-filter_'.( $item['id'] ).'.htm">смотреть</a>]';
   }

   function doBeforeRun() {
      if (IS_LOCATIONS !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'ord');
         Site::SetSession($this->name."-order", 'asc');
      }
   }
}
?>