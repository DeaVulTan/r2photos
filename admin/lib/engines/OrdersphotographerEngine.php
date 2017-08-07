<?php
class OrdersphotographerEngine extends AdminEngine {
   function OrdersphotographerEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Ordersphotographer::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Ordersphotographer::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         if (defined('LANG')) Site::SetSession($this->name.'-lang', $_POST['lang']);
         return Site::CreateUrl($this->name.'-list');
      }
      return;
   }

   function getPictureStr($name, $item) {
      $file = $item[ $name ];
      echo ($file && file_exists(Site::GetParms('absolutePath').$file) ? '<img style="max-width: 400px;" src="../'.$file.'" />' : 'Картинки нет.');
   }

   function getPhotographerStr($name, $item) {
      $photographer = ( $item[ $name ] ? Photographers::GetRow( array( 'id' => $item[ $name ] ) ) : array() );
      echo $photographer['name'];
   }

   function getDataStr($name, $item) {
      echo date("d.m.Y", $item[$name]);
   }

   function doBeforeRun() {
      if (IS_ORDERS !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'idate');
         Site::SetSession($this->name."-order", 'desc');
      }
   }
}
?>