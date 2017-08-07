<?php
class PhotoimagesEngine extends AdminEngine {
   function PhotoimagesEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['parts_id'] = Site::GetSession($this->name."-parts_id");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Photoimages::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['parts_id'] = Site::GetSession($this->name."-parts_id");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Photoimages::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         Site::SetSession($this->name.'-parts_id', ($_POST['fromFF'] ? $_POST['parts_id'] : $this->values[0]));
         Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         return Site::CreateUrl($this->name.'-list');
      }
   }

   function getPictureStr($name, $item) {
      $file = $item['picture_small'];
      echo ($file && file_exists(Site::GetParms('absolutePath').$file) ? '<img style="max-width:320px; max-height:320px;" src="../'.$file.'" />' : 'Картинки нет.');
   }

   function doBeforeRun() {
      if (IS_PHOTO !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'ord');
         Site::SetSession($this->name."-order", 'asc');
      }
   }

   function topNav() {
      return '<a href="photographers-list.htm" title="Фотографы">Фотографы</a> &gt; Фотографии';
   }
}
?>