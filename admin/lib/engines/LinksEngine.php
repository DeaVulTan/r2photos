<?php
class LinksEngine extends AdminEngine {
   function LinksEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      $parms['lang'] = Site::GetSession($this->name."-lang");
      return Links::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      $parms['lang'] = Site::GetSession($this->name."-lang");
      return Links::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         Site::SetSession($this->name.'-like', $_POST['like']);
         if (defined('LANG')) Site::SetSession($this->name.'-lang', $_POST['lang']);
         return Site::CreateUrl($this->name.'-list');
      }
   }

   function doBeforeRun() {
      if (IS_OFFICE !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'url777regexp');
         Site::SetSession($this->name."-order", 'asc');
      }
   }
}
?>