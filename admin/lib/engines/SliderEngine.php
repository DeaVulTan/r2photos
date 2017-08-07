<?php
class SliderEngine extends AdminEngine {
   function SliderEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Slider::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Slider::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         if (defined('LANG')) Site::SetSession($this->name.'-lang', $_POST['lang']);
         Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         return Site::CreateUrl($this->name.'-list');
      }
      return;
   }

   function getPictureStr($name, $item) {
      $file = $item['picture'];
      echo ($file && file_exists(Site::GetParms('absolutePath').$file) ? '<img style="max-width: 800px;" src="../'.$file.'" />' : 'Картинки нет.');
   }

   function doBeforeRun() {
      if (IS_MENU !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'ord');
         Site::SetSession($this->name."-order", 'asc');
      }
   }
}
?>