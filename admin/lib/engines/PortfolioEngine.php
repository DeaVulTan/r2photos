<?php
class PortfolioEngine extends AdminEngine {
   function PortfolioEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Portfolio::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Portfolio::GetRows($parms, $limit, $order);
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

   function getPictureStr($name, $item) {
      $file = $item['picture'];

// что-то динамический кропер /thumb.php выдаёт на хосте 500-ую ошибку. Нужно разбираться, почему
//      echo ($file && file_exists(Site::GetParms('absolutePath').$file) ? '<img src="/thumbs/360x1440/'.$file.'" style="max-width:200px;"/>' : 'Картинки нет.');

      echo ($file && file_exists(Site::GetParms('absolutePath').$file) ? '<img src="/'.$file.'" style="max-width:200px;"/>' : 'Картинки нет.');
   }

   function getPhotosStr($name, $item) {
      $count = Retouchesphotos::GetCountRows( array( 'location_id' => $item['id'] ) );
      echo '['.( $count ).' - <a href="locationsphotos-filter_'.( $item['id'] ).'.htm">смотреть</a>]';
   }

   function doBeforeChangeList()
   {
      $path = Site::GetParms('absolutePath');
      if (is_array($_POST['deletedItem']) && $_POST['_del_x'] && $_POST['_del_y']) {

         foreach ($_POST['deletedItem'] as $k => $v) {
            $item = Portfolio::GetRow(array('id' => $k));

            if ($item['picture']) {
               unlink($path . $item['picture']);
            }
         }
      }
      return true;
   }

   function doBeforeRun() {
      if (IS_LOCATIONS !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'ord');
         Site::SetSession($this->name."-order", 'asc');
      }
   }
}
?>