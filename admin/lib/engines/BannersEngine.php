<?php
class BannersEngine extends AdminEngine {
   function BannersEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['place_id'] = Site::GetSession($this->name."-place_id");
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Banners::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['place_id'] = Site::GetSession($this->name."-place_id");
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Banners::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         Site::SetSession($this->name.'-place_id', ($_POST['fromFF'] ? $_POST['place_id'] : $this->values[0]));
         Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         if (defined('LANG')) Site::SetSession($this->name.'-lang', $_POST['lang']);
         return Site::CreateUrl($this->name.'-list');
      }
      else if ($this->parms['action'] == 'clear') {
         $db =& Site::GetDB();
         $db->Query("DELETE FROM banner_stat WHERE banner_id='".$this->values[0]."'");
         $db->Query("UPDATE banners SET all_shows=0, all_clicks=0 WHERE id='".$this->values[0]."'");
         return Site::CreateUrl($this->name.'-list');
      }
   }

   function doBeforeChangeList() {
      if (is_array($_POST['deletedItem']) && sizeOf($_POST['deletedItem']) > 0 && $_POST['_del_x'] && $_POST['_del_y']) {
         $db =& Site::GetDB();
         foreach ($_POST['deletedItem'] as $k => $v) $db->Query("DELETE FROM banner_stat WHERE banner_id='".$k."'");
      }
   }

   function doAfterUpdateData($id) {
      $db =& Site::GetDB();
      $str = (sizeOf($_POST['days']) > 0 ? "|".implode("|", array_keys($_POST['days']))."|" : '');
      $db->Query("UPDATE banners SET days_show='".$str."' WHERE id='".$id."'");
   }

   function getPictureStr($name, $item) {
      $file = ($item['is_flash'] ? $item['file_door_flash'] : $item['file']);
      echo '<div id="flash'.$item['id'].'">'.($file && file_exists(Site::GetParms('absolutePath').$file) ? '<img src="../'.$file.'" />' : 'Картинки нет.').'</div>';
   }

   function getCtrStr($name, $item) {
      echo ($item['all_shows'] > 0 ? round(($item['all_clicks'] / $item['all_shows'] * 100), 2) : 0).'%';
   }

   function getClearStr($name, $item) {
      echo '<a href="'.Site::CreateUrl('banners-clear', array($item['id'])).'">сбросить статистику</a>';
   }

   function buttonsAddBottom($items) {
      if (sizeOf($items) > 0) {
      echo '<td><script type="text/javascript"><!--
       function checkFlash() {';
       foreach ($items as $id => $item) if ($item['is_flash']) {
         echo 'htmlOptions = { src: "../'.$item['file'].'", width: \''.$item['width'].'\', height: \''.$item['height'].'\', replace: \'Нет проигрывателя\' };
               $(\'#flash'.$id.'\').flash(htmlOptions, pluginOptions);';
       }
       echo 'return;
       }
      //-->
      </script></td>';
      }
   }

   function doBeforeRun() {
      if (IS_BAN !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'id');
         Site::SetSession($this->name."-order", 'asc');
      }
   }

   function topNav() {
      return '<a href="bannersplaces-list.htm" title="Банерные места">Банерные места</a> &gt; Банеры';
   }

   function echoAddTr($name) {
      if ($name == 'idate_to') {
         if (trim($this->table->GetValue('days_show'))) $curr = explode("|", preg_replace('/\|(.+)\|/', '\\1', trim($this->table->GetValue('days_show'))));
         else $curr = array();
         $days = array(1 => 'Понедельник', 2 => 'Вторник', 3 => 'Среда', 4 => 'Четверг', 5 => 'Пятница', 6 => 'Суббота', 0 => 'Воскресенье');
         echo '<tr valign="top"><td class="pr10 pb10" align="right">Показывать&nbsp;по&nbsp;дням</td><td class="w100 pb10">';
         foreach ($days as $k => $v) {
            Action::CheckBox("days[".$k."]", (in_array($k, $curr) ? 1 : 0));
            echo ' - '.$v."<br />";
         }
         echo '</td></tr>';
         echo $tr;
      }
   }
}
?>