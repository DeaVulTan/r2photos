<?php
class SubscribersEngine extends AdminEngine {
   function SubscribersEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['act'] = Site::GetSession($this->name."-act");
      $parms['nact'] = Site::GetSession($this->name."-nact");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Subscribers::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['act'] = Site::GetSession($this->name."-act");
      $parms['nact'] = Site::GetSession($this->name."-nact");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Subscribers::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         Site::SetSession($this->name.'-act', $_POST['act']);
         Site::SetSession($this->name.'-nact', $_POST['nact']);
         return Site::CreateUrl($this->name.'-list');
      }
   }

   function getDataStr($name, $item) {
      echo date("d.m.Y", $item[$name]);
   }

   function doBeforeRun() {
      if (IS_SUBSCRIBE !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'email');
         Site::SetSession($this->name."-order", 'asc');
      }
   }
}
?>