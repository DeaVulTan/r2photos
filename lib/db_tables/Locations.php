<?php
include_once(Site::GetParms('tablesPath')."Locationsphotos.php");
class Locations {
   function Locations() { }
   function Structure($init = false) {
      if ($init) {
         $form = array(
            'is_active' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Активность'))),
            'ord' => array('default' => 0, 'form' => array('Input' => array('name' => 'Сортировка', 'style' => 'size="4"'))),
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Наименование', 'style' => 'style="width: 100%;"'))),
            'picture' => array('default' => '', 'form' => array('Upload' => array('text' => true, 'path' => 'data/image/locations','name' => 'Фотография', 'style' => 'style="width: 40%;"'))),
            'price' => array('default' => 0, 'form' => array('Input' => array('name' => 'Стоимость', 'style' => 'style="width: 200px;"'))),
         );
         $columns = array(
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
            'ord' => array('name' => 'Сортировка', 'autoUpdate' => true, 'sort' => true, 'order' => true),
            'name' => array('name' => 'Наименование', 'style' => 'width="25%" align="left"', 'sort' => true),
            'price' => array('name' => 'Стоимость', 'style' => ' align="left"', 'sort' => true),
            'picture' => array('name' => 'Картинка', 'function' => 'getPictureStr', 'style' => ' width="50%" nowrap '),
            '_photos' => array('name' => 'Фотографии', 'style' => ' nowrap ', 'function' => 'getPhotosStr'),
         );
         if (defined('LANG')) {
            $form = array_merge(array('lang' => array('default' => 'ru', 'form' => array('Select' => array('items' => Site::GetLangs(), 'name' => 'Язык')))), $form);
            $columns = array_merge(array('lang' => array('name' => 'Язык')), $columns);
         }
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'locations',
         'tableName'  => 'Локации'
      );
   }
   function Get($id = '', $init = false) { return new Table(Locations::Structure($init), $id); }
   function Update($rows) { $t = new Table(Locations::Structure()); return $t->UpdateRows($rows); }
   function Save($rows) { $t = new Table(Locations::Structure()); return $t->SaveRows($rows); }
   function Delete($rows) { $t = new Table(Locations::Structure()); return $t->DeleteRows($rows); }

   static function whereString($parms) {
      $where = '';
      if (isset($parms['id']) && $parms['id']) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if (isset($parms['lang']) && $parms['lang']) $where .= ($where ? " AND " : " WHERE ")."lang='".mysql_real_escape_string($parms['lang'])."'";
      if (isset($parms['active'])) $where .= ($where ? " AND " : " WHERE ")."is_active='".(int)$parms['active']."'";
      if (isset($parms['like']) && $parms['like']) $where .= ($where ? " AND " : " WHERE ")."`name` LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   static function limitString($parms) { if (!isset($parms['limit'])) $parms['limit'] = 0; if (!isset($parms['offset'])) $parms['offset'] = 0; $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   static function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY `ord` ASC, `id` ASC ';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v).($k <> 'id' ? ', id '.strtoupper($v) : '');
   }

   static function GetCountRows($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM `locations` ".Locations::whereString($parms));
   }

   static function GetRows($parms = array(), $limit = array(), $order = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT * FROM `locations` ".
                             Locations::whereString($parms).Locations::orderString($order).
                             Locations::limitString($limit), 'id');
   }

   static function GetNames($parms = array(), $limit = array(), $order = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT `id`, `name` FROM `locations` ".
                             Locations::whereString($parms).Locations::orderString($order).
                             Locations::limitString($limit), 'id');
   }

   static function GetRow($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectRow("SELECT * FROM `locations` ".Locations::whereString($parms));
   }

   static function GetIds($parms = array(), $limit = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT `id` FROM `locations` ".Locations::whereString($parms).' ORDER BY `ord` ASC, `id` ASC '.Locations::limitString($limit), 'id');
   }
}
?>
