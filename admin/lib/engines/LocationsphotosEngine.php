<?php
class LocationsphotosEngine extends AdminEngine {
   function LocationsphotosEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['location_id'] = Site::GetSession($this->name."-location_id");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Locationsphotos::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['location_id'] = Site::GetSession($this->name."-location_id");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Locationsphotos::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         Site::SetSession($this->name.'-location_id', ($_POST['fromFF'] ? $_POST['location_id'] : $this->values[0]));
         Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         return Site::CreateUrl($this->name.'-list');
      }
   }

   function getPictureStr($name, $item) {
      $file = $item['picture'];
      echo ($file && file_exists(Site::GetParms('absolutePath').$file) ? '<img src="../'.$file.'" style="max-width: 400px;" />' : 'Картинки нет.');
   }

   function doBeforeRun() {
      if (IS_CATALOG !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'ord');
         Site::SetSession($this->name."-order", 'asc');
      }
   }

   function topNav() {
      $locationId = ( int ) Site::GetSession( $this->name.'-location_id' );
      $location = ( $locationId ? Locations::GetRow( array( 'id' => $locationId ) ) : array() );
      return '<a href="locations-list.htm" title="Локации">Локации</a> &gt; '.( $location['id'] ? $location['name'].': ' : '' ).'Фотографии';
   }
}
?>