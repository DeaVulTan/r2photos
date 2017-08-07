<?php
class Works {
   function Works() { }
   function Structure($init = false) {
      if ($init) {
         $form = array(
            'is_active' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Активность'))),
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Наименование', 'style' => 'style="width: 100%;"'))),
            'description' => array('default' => '', 'form' => array('FCKeditor' => array('ToolbarSet' => '__set2__', 'Height' => 500, 'name' => 'Краткое описание')))
         );
         $columns = array(
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
            'name' => array('name' => 'Наименование', 'style' => 'width="50%" align="left"', 'sort' => true),
            'description' => array('name' => 'Описание', 'style' => 'width="50%" align="left"', 'sort' => true),
            '_photographers' => array('name' => 'Фотографы', 'function' => 'getPhotographersStr', 'style' => ' nowrap '),
         );
         if (defined('LANG')) {
            $form = array_merge(array('lang' => array('default' => 'ru', 'form' => array('Select' => array('items' => Site::GetLangs(), 'name' => 'Язык')))), $form);
            $columns = array_merge(array('lang' => array('name' => 'Язык')), $columns);
         }
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'works',
         'tableName'  => 'Виды работ фотографов'
      );
   }
   function Get($id = '', $init = false) { return new Table(Works::Structure($init), $id); }
   function Update($rows) { $t = new Table(Works::Structure()); return $t->UpdateRows($rows); }
   function Save($rows) { $t = new Table(Works::Structure()); return $t->SaveRows($rows); }
   function Delete($rows) { $t = new Table(Works::Structure()); return $t->DeleteRows($rows); }

   static function whereString($parms) {
      $where = '';
      if (isset($parms['id']) && $parms['id']) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if (isset($parms['active'])) $where .= ($where ? " AND " : " WHERE ")."is_active='".(int)$parms['active']."'";
      if (isset($parms['like']) && $parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(`name`, ' ', `description`) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   static function limitString($parms) { if (!isset($parms['limit'])) $parms['limit'] = 0; if (!isset($parms['offset'])) $parms['offset'] = 0; $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   static function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY `name` ASC ';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v).($k <> 'id' ? ', id '.strtoupper($v) : '');
   }

   static function GetCountRows($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM `works` ".Works::whereString($parms));
   }

   static function GetRows($parms = array(), $limit = array(), $order = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT * FROM `works` ".
                             Works::whereString($parms).Works::orderString($order).
                             Works::limitString($limit), 'id');
   }

   static function GetNames($parms = array(), $limit = array(), $order = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT `id`, `name` FROM `works` ".
                             Works::whereString($parms).Works::orderString($order).
                             Works::limitString($limit), 'id');
   }

   static function GetRow($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectRow("SELECT * FROM `works` ".Works::whereString($parms));
   }

   static function GetIds($parms = array(), $limit = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT `id` FROM `works` ".Works::whereString($parms).' ORDER BY `name` ASC '.Works::limitString($limit), 'id');
   }
}
?>