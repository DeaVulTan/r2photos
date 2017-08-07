<?php
class VotesEngine extends AdminEngine {
   function VotesEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      $parms['lang'] = Site::GetSession($this->name."-lang");
      return Votes::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      $parms['lang'] = Site::GetSession($this->name."-lang");
      return Votes::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         if (defined('LANG')) Site::SetSession($this->name.'-lang', $_POST['lang']);
         return Site::CreateUrl($this->name.'-list');
      }
      if ($this->parms['action'] == 'clear') {
         $db =& Site::GetDB();
         $db->Query("UPDATE poll_variants SET q='0' WHERE poll_id='".$this->values[0]."'");
         return Site::CreateUrl($this->name.'-list');
      }
      return;
   }

   function doBeforeChangeList() {
      if (is_array($_POST['deletedItem']) && sizeOf($_POST['deletedItem']) > 0 && $_POST['_del_x'] && $_POST['_del_y']) {
         $db =& Site::GetDB();
         foreach ($_POST['deletedItem'] as $k => $v) {
            $db->Query("DELETE FROM poll_variants WHERE poll_id='".$k."'");
            $db->Query("DELETE FROM poll_ip WHERE poll_id='".$k."'");
         }
      }
   }
   
   function doBeforeUpdateData() {
    $thisUrl = Utils::Translit($this->table->data['href']);
    if (!$thisUrl) $thisUrl = Utils::Translit($this->table->data['question']);
    $this->table->SetValue('href', $thisUrl);
    return;
   }

   function getItemsStr($name, $item) {
      echo $item['varsCount'].' - <a href="'.Site::CreateUrl('votesvars-filter', array($item['id'])).'">смотреть</a>';
   }

   function getClearStr($name, $item) {
      echo '<a href="'.Site::CreateUrl('votes-clear', array($item['id'])).'">сбросить результаты</a>';
   }

   function doBeforeRun() {
      if (IS_VOTES !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'ord');
         Site::SetSession($this->name."-order", 'asc');
      }
   }
}
?>