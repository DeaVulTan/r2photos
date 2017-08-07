<?php
class ConfigsEngine extends AdminEngine {
   function ConfigsEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['parts_id'] = Site::GetSession($this->name."-parts_id");
      $parms['like'] = Site::GetSession($this->name."-like");
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['not_robots'] = true;
      $parms['not_counters'] = true;
      return Configs::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['parts_id'] = Site::GetSession($this->name."-parts_id");
      $parms['like'] = Site::GetSession($this->name."-like");
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['not_robots'] = true;
      $parms['not_counters'] = true;
      return Configs::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         Site::SetSession($this->name.'-parts_id', $_POST['parts_id']);
         Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         if (defined('LANG')) Site::SetSession($this->name.'-lang', $_POST['lang']);
         return Site::CreateUrl($this->name.'-list', $this->values);
      }
   }

   function doBeforeRun() {
      if (IS_CONFIG !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'name');
         Site::SetSession($this->name."-order", 'asc');
      }
   }
}
?>