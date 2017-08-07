<?php
class PhotoarchivEngine extends AdminEngine {
   function PhotoarchivEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      $parms['lang'] = Site::GetSession($this->name."-lang");
      return Photoarchiv::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      $parms['lang'] = Site::GetSession($this->name."-lang");
      return Photoarchiv::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         if (defined('LANG')) Site::SetSession($this->name.'-lang', $_POST['lang']);
         return Site::CreateUrl($this->name.'-list');
      }
      return;
   }

   function doBeforeChangeList() {
      if (is_array($_POST['deletedItem']) && sizeOf($_POST['deletedItem']) > 0 && $_POST['_del_x'] && $_POST['_del_y']) {
         $db =& Site::GetDB();
         foreach ($_POST['deletedItem'] as $k => $v) $db->Query("DELETE FROM photo_images WHERE parts_id='".$k."'");
      }
   }
   
   function doBeforeUpdateData() {
    $thisUrl = Utils::Translit($this->table->data['href']);
    if (!$thisUrl) $thisUrl = Utils::Translit($this->table->data['name']);
    $this->table->SetValue('href', $thisUrl);
    return;
   }

   function getItemsStr($name, $item) {
      echo $item['countImages'].' - <a href="'.Site::CreateUrl('photoimages-filter', array($item['id'])).'">смотреть</a>';
   }

   function doBeforeRun() {
      if (IS_PHOTO !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'ord');
         Site::SetSession($this->name."-order", 'asc');
      }
   }
}
?>