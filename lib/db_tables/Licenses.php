<?php
class Licenses {
   function Licenses() { }
   function &Structure($init = false) {
      if ($init) {
         $form = array(
            'is_active' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Активность'))),
            'ord' => array('default' => 0, 'form' => array('Input' => array('name' => 'Сортировка', 'style' => 'size="4"'))),
            'image_small' => array('default' => '', 'form' => array('Upload' => array('text' => true, 'path' => 'data/image/licenses', 'name' => 'Изображение (мал.)', 'style' => 'style="width: 40%;"'))),
            'image_big' => array('default' => '', 'form' => array('Upload' => array('text' => true, 'path' => 'data/image/licenses', 'name' => 'Изображение (бол.)', 'style' => 'style="width: 40%;"'))),
         );
         $columns = array(
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
            'ord' => array('name' => 'Сортировка', 'autoUpdate' => true, 'sort' => true, 'order' => true),
            'image_small' => array('name' => 'Изображение', 'style' => 'width="100%"', 'function' => 'getPictureStr'),
         );
         if (defined('LANG')) {
            $form = array_merge(array('lang' => array('default' => 'ru', 'form' => array('Select' => array('items' => Site::GetLangs(), 'name' => 'Язык')))), $form);
            $columns = array_merge(array('lang' => array('name' => 'Язык')), $columns);
         }
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'licenses',
         'tableName'  => 'Лицензии / сертификаты'
      );
   }

   function &Get($id = '', $init = false) { return new Table(Licenses::Structure($init), $id); }
   function Update(&$rows) { $t = new Table(Licenses::Structure()); return $t->UpdateRows($rows); }
   function Save(&$rows) { $t = new Table(Licenses::Structure()); return $t->SaveRows($rows); }
   function Delete(&$rows) { $t = new Table(Licenses::Structure()); return $t->DeleteRows($rows); }

   function whereString($parms) {
      $where = '';
      if (isset($parms['active'])) $where .= ($where ? " AND " : " WHERE ")."is_active='".(int)$parms['active']."'";
      if ($parms['lang']) $where .= ($where ? " AND " : " WHERE ")."lang='".mysql_real_escape_string($parms['lang'])."'";
      if ($parms['id'] > 0) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if ($parms['like']) $where .= ($where ? " AND " : " WHERE ")."image_small LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   function limitString($parms) { $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY ord, id';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v);
   }

   function &GetCountRows($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT COUNT(id) FROM licenses".Licenses::whereString($parms));
   }

   function &GetRows($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT * FROM licenses ".
                             Licenses::whereString($parms).Licenses::orderString($order).
                             Licenses::limitString($limit), 'id');
   }

   function &GetRow($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT * FROM licenses".Licenses::whereString($parms));
   }
}
?>