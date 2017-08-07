<?php
include_once(Site::GetParms('tablesPath')."Catalog.php");
class Fabrics {
   function Fabrics() { }
   function &Structure($init = false) {
      if ($init) {
         $form = array(
            'ord' => array('default' => 0, 'form' => array('Input' => array('name' => 'Сортировка', 'style' => 'size="4"'))),
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Наименование', 'style' => 'style="width: 100%;"'))),
            'href' => array('default' => '', 'form' => array('Input' => array('name' => 'URL', 'style' => 'style="width: 100%;"'))),
            'picture' => array('default' => '', 'form' => array('Upload' => array('text' => true, 'path' => 'data/image/catalog', 'name' => 'Логотип', 'style' => 'style="width: 40%;"'))),
            'description' => array('default' => '', 'form' => array('FCKeditor' => array('ToolbarSet' => '__set2__', 'Height' => 500, 'name' => 'Описание'))),
         );
         $columns = array(
            'ord' => array('name' => 'Сортировка', 'autoUpdate' => true, 'sort' => true, 'order' => true),
            'name' => array('name' => 'Наименование', 'style' => 'width="100%" align="left"', 'sort' => true),
            'column1' => array('name' => 'Позиции', 'function' => 'getItemsStr', 'style' => 'nowrap="nowrap"'),
         );
         if (defined('LANG')) {
            $form = array_merge(array('lang' => array('default' => 'ru', 'form' => array('Select' => array('items' => Site::GetLangs(), 'name' => 'Язык')))), $form);
            $columns = array_merge(array('lang' => array('name' => 'Язык')), $columns);
         }
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'fabrics',
         'tableName'  => 'Производители'
      );
   }
   function &Get($id = '', $init = false) { return new Table(Fabrics::Structure($init), $id); }
   function Update(&$rows) { $t = new Table(Fabrics::Structure()); return $t->UpdateRows($rows); }
   function Save(&$rows) { $t = new Table(Fabrics::Structure()); return $t->SaveRows($rows); }
   function Delete(&$rows) { $t = new Table(Fabrics::Structure()); return $t->DeleteRows($rows); }

   function whereString($parms) {
      $where = '';
      if ($parms['id'] > 0) $where .= ($where ? " AND " : " WHERE ")."f.id='".(int)$parms['id']."'";
      if ($parms['lang']) $where .= ($where ? " AND " : " WHERE ")."f.lang='".mysql_real_escape_string($parms['lang'])."'";
      if ($parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(f.name, ' ', f.description, ' ', f.href) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      if (isset($parms['href'])) $where .= ($where ? " AND " : " WHERE ")."f.href='".mysql_real_escape_string($parms['href'])."'";
      if ($parms['letter']) $where .= ($where ? " AND " : " WHERE ")."ASCII(LEFT(UPPER(f.name), 1))='".mysql_real_escape_string($parms['letter'])."'";
      return $where;
   }

   function limitString($parms) { $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY f.ord';
      else foreach ($parms as $k => $v) return ' ORDER BY f.'.$k.' '.strtoupper($v);
   }

   function &GetCountRows($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT COUNT(f.id) FROM fabrics f".Fabrics::whereString($parms));
   }

   function &GetRows($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT f.*, COUNT(i.id) AS countItems FROM fabrics f LEFT JOIN items i ON i.fabrics_id=f.id".
                             Fabrics::whereString($parms)."
                             GROUP BY f.id".Fabrics::orderString($order).
                             Fabrics::limitString($limit), 'id');
   }

   function &GetRow($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT f.* FROM fabrics f".Fabrics::whereString($parms));
   }

   function GetLetters() {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT DISTINCT ASCII(LEFT(UPPER(name), 1)) AS letter FROM fabrics ORDER BY 1");
   }

   function &GetIds($parms = array(), $limit = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT f.id FROM fabrics f".Fabrics::whereString($parms)." ORDER BY f.ord".Fabrics::limitString($limit), 'id');
   }
}
?>