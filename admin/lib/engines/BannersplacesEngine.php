<?php
class BannersplacesEngine extends AdminEngine {
   function BannersplacesEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      return Bannersplaces::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      return Bannersplaces::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         return Site::CreateUrl($this->name.'-list');
      }
      return;
   }

   function doBeforeChangeList() {
      if (is_array($_POST['deletedItem']) && sizeOf($_POST['deletedItem']) > 0 && $_POST['_del_x'] && $_POST['_del_y']) {
         $db =& Site::GetDB();
         foreach ($_POST['deletedItem'] as $k => $v) {
            $ids =& $db->SelectSet("SELECT id FROM banners WHERE place_id='".$k."'", 'id');
            if (sizeOf($ids) > 0) foreach ($ids as $k1 => $v1) {
               $db->Query("DELETE FROM banners WHERE id='".$k1."'");
               $db->Query("DELETE FROM banner_stat WHERE banner_id='".$k1."'");
            }
         }
      }
   }

   function getItemsStr($name, $item) {
      echo $item['bannersCount'].' - <a href="'.Site::CreateUrl('banners-filter', array($item['id'])).'">смотреть</a>';
   }

   function doBeforeRun() {
      if (IS_BAN !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'name');
         Site::SetSession($this->name."-order", 'asc');
      }
   }
}
?>