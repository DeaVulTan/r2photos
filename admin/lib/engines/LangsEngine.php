<?php
class LangsEngine extends AdminEngine {
   function LangsEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      return Langs::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      return Langs::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         return Site::CreateUrl($this->name.'-list');
      }
      return;
   }

   function doBeforeRun() {
      if (IS_ADMINS !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'name');
         Site::SetSession($this->name."-order", 'asc');
      }
   }
}
?>