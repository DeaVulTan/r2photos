<?php
class LicensesEngine extends AdminEngine {
   function LicensesEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      return Licenses::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      return Licenses::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         return Site::CreateUrl($this->name.'-list');
      }
   }

   function getPictureStr($name, $item) {
      $file = $item['image_small'];
      echo '<div>'.($file && file_exists(Site::GetParms('absolutePath').$file) ? '<img src="../'.$file.'" />' : 'Картинки нет.').'</div>';
   }

   function doBeforeRun() {
      if (IS_BAN !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'ord');
         Site::SetSession($this->name."-order", 'asc');
      }
   }
}
?>