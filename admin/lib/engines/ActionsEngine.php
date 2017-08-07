<?php
class ActionsEngine extends AdminEngine {
   function ActionsEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Actions::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Actions::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         if (defined('LANG')) Site::SetSession($this->name.'-lang', $_POST['lang']);
         return Site::CreateUrl($this->name.'-list');
      }
      return;
   }

   function getDataStr($name, $item) {
      echo date("d.m.Y", $item[$name]);
   }

   function getImageStr( $name, $item ) {
    $fileName = $item[ $name ];
    echo ( !empty( $fileName ) && file_exists( '../'.$fileName ) ? '<img src="../'.( $fileName ).'" />' : '-' );
   }//getImageStr

   function doBeforeUpdateData() {
    $thisUrl = Utils::Translit($this->table->data['href']);
    if (!$thisUrl) $thisUrl = Utils::Translit($this->table->data['name']);
    $this->table->SetValue('href', $thisUrl);
    return;
   }

   function doBeforeRun() {
      if (IS_ACTIONS !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'idate');
         Site::SetSession($this->name."-order", 'desc');
      }
   }
}
?>