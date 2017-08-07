<?php
class CardsEngine extends AdminEngine {
   function CardsEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Cards::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Cards::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         if (defined('LANG')) Site::SetSession($this->name.'-lang', $_POST['lang']);
         return Site::CreateUrl($this->name.'-list');
      }
      return;
   }

   function getCityStr( $name, $item ) {
    static $allCityList = false;
    if( $allCityList === false ) {
        $allCityList = City::GetRows();
    }
    echo $allCityList[ $item[ $name ] ]['name'];
   }//getCityStr

   function getStatusStr( $name, $item ) {
    static $statusList = false;
    if( $statusList === false ) {
        $statusList = Cards::GetStatusList();
    }
    echo $statusList[ $item[ $name ] ];
   }//getStatusStr

   function getDataStr($name, $item) {
      echo date("d.m.Y", $item[$name]);
   }

   function doBeforeRun() {
      if (IS_ACTIVATION !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'id');
         Site::SetSession($this->name."-order", 'desc');
      }
   }
}
?>