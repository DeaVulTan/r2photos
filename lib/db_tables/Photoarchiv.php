<?php
include_once(Site::GetParms('tablesPath')."Photoimages.php");
class Photoarchiv {
   function Photoarchiv() { }
   function &Structure($init = false) {
      if ($init) {
         $form = array(
            'ord' => array('default' => 0, 'form' => array('Input' => array('name' => 'Сортировка', 'style' => 'size="4"'))),
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Наименование', 'style' => 'style="width: 100%;"'))),
            'picture' => array('default' => '', 'form' => array('Upload' => array('text' => true, 'path' => 'data/image/photo', 'name' => 'Превью', 'style' => 'style="width: 40%;"'))),
            'text' => array('default' => '', 'form' => array('FCKeditor' => array('ToolbarSet' => '__set2__', 'Height' => 500, 'name' => 'Описание'))),
            'href' => array('default' => '', 'form' => array('Input' => array('name' => 'URL', 'style' => 'style="width: 100%;"'))),
         );
         $columns = array(
            'ord' => array('name' => 'Сортировка', 'autoUpdate' => true, 'sort' => true, 'order' => true),
            'name' => array('name' => 'Наименование', 'style' => 'width="100%" align="left"', 'sort' => true),
            'column1' => array('name' => 'Альбом', 'function' => 'getItemsStr', 'style' => 'nowrap="nowrap"'),
         );
         if (defined('LANG')) {
            $form = array_merge(array('lang' => array('default' => 'ru', 'form' => array('Select' => array('items' => Site::GetLangs(), 'name' => 'Язык')))), $form);
            $columns = array_merge(array('lang' => array('name' => 'Язык')), $columns);
         }
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'photo_archiv',
         'tableName'  => 'Фотоархив'
      );
   }

   function &Get($id = '', $init = false) { return new Table(Photoarchiv::Structure($init), $id); }
   function Update(&$rows) { $t = new Table(Photoarchiv::Structure()); return $t->UpdateRows($rows); }
   function Save(&$rows) { $t = new Table(Photoarchiv::Structure()); return $t->SaveRows($rows); }
   function Delete(&$rows) { $t = new Table(Photoarchiv::Structure()); return $t->DeleteRows($rows); }

   function whereString($parms) {
      $where = '';
      if ($parms['id'] > 0) $where .= ($where ? " AND " : " WHERE ")."p.id='".(int)$parms['id']."'";
      if (isset($parms['href'])) $where .= ($where ? " AND " : " WHERE ")."p.href='".mysql_real_escape_string($parms['href'])."'";
      if ($parms['lang']) $where .= ($where ? " AND " : " WHERE ")."p.lang='".mysql_real_escape_string($parms['lang'])."'";
      if ($parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(p.name, ' ', p.text) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   function limitString($parms) { $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY p.ord';
      else foreach ($parms as $k => $v) return ' ORDER BY p.'.$k.' '.strtoupper($v);
   }

   function &GetCountRows($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT COUNT(p.id) FROM photo_archiv p".Photoarchiv::whereString($parms));
   }

   function &GetRows($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT p.*, COUNT(pi.id) AS countImages FROM photo_archiv p LEFT JOIN photo_images pi ON pi.parts_id=p.id".
                             Photoarchiv::whereString($parms)." GROUP BY p.id".Photoarchiv::orderString($order).
                             Photoarchiv::limitString($limit), 'id');
   }

   function &GetRow($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT p.* FROM photo_archiv p".Photoarchiv::whereString($parms));
   }

   function &GetIds($parms = array(), $limit = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT p.id FROM photo_archiv p".Photoarchiv::whereString($parms).Photoarchiv::limitString($limit), 'id');
   }
}
?>