<?php
class CorrEngine extends AdminEngine {
   function CorrEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      $parms['ord_id'] = Site::GetSession($this->name."-orders_id");
      return Corr::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['ord_id'] = Site::GetSession($this->name."-orders_id");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Corr::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         if ($_POST['fromFF']) {
            Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
            Site::SetSession($this->name.'-orders_id', $_POST['orders_id']);
         }
         else Site::SetSession($this->name.'-orders_id', $this->values[0]);
         return Site::CreateUrl($this->name.'-list');
      }
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