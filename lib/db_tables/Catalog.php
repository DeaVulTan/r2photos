<?php
include_once(Site::GetParms('tablesPath')."Items.php");
include_once(Site::GetParms('tablesPath')."Fabrics.php");
include_once(Site::GetParms('tablesPath')."Category.php");
class Catalog {
   function Catalog() { }
   function &Structure($init = false) {
      if ($init) {
         $form = array(
            'parent_id' => array('default' => (int)Site::GetSession("catalog-parent_id"), 'form' => array('Select' => array('items' => Items::GetList(), 'field' => 'name', 'name' => 'Родитель'))),
            //'category_id' => array('default' => (int)Site::GetSession("catalog-category_id"), 'form' => array('Select' => array('items' => Category::GetList(), 'name' => 'Категория&nbsp;услуг'))),
            'is_active' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Активность'))),
            //'is_menu' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'В&nbsp;верхнем&nbsp;меню'))),
            'ord' => array('default' => 0, 'form' => array('Input' => array('name' => 'Сортировка', 'style' => 'size="4"'))),
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Наименование', 'style' => 'style="width: 100%;"'))),
            'href' => array('default' => '', 'form' => array('Input' => array('name' => 'URL', 'style' => 'style="width: 100%;"'))),
            'picture' => array('default' => '', 'form' => array('Upload' => array('text' => true, 'path' => 'data/image/catalog' ,'name' => 'Картинка', 'style' => 'style="width: 40%;"'))),
            'description' => array('default' => '', 'form' => array('FCKeditor' => array('ToolbarSet' => '__set2__', 'Height' => 500, 'name' => 'Описание')))
         );
         $columns = array(
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
            //'is_menu' => array('name' => 'В верхнем меню', 'autoUpdate' => true),
            'ord' => array('name' => 'Сортировка', 'autoUpdate' => true, 'sort' => true, 'order' => true),
            'name' => array('name' => 'Наименование', 'style' => 'width="100%" align="left"', 'sort' => true),
            'column1' => array('name' => 'Подразделы', 'function' => 'getSubPartsStr', 'style' => 'nowrap="nowrap"'),
            'column2' => array('name' => 'Позиции', 'function' => 'getItemsStr', 'style' => 'nowrap="nowrap"')
         );
         if (defined('LANG')) {
            $form = array_merge(array('lang' => array('default' => 'ru', 'form' => array('Select' => array('items' => Site::GetLangs(), 'name' => 'Язык')))), $form);
            $columns = array_merge(array('lang' => array('name' => 'Язык')), $columns);
         }
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'catalog',
         'tableName'  => 'Разделы каталога'
      );
   }
   function &Get($id = '', $init = false) { return new Table(Catalog::Structure($init), $id); }
   function Update(&$rows) { $t = new Table(Catalog::Structure()); return $t->UpdateRows($rows); }
   function Save(&$rows) { $t = new Table(Catalog::Structure()); return $t->SaveRows($rows); }
   function Delete(&$rows) { $t = new Table(Catalog::Structure()); return $t->DeleteRows($rows); }

   function whereString($parms) {
      $where = '';
      if (isset($parms['active'])) $where .= ($where ? " AND " : " WHERE ")."is_active='".(int)$parms['active']."'";
      if ($parms['lang']) $where .= ($where ? " AND " : " WHERE ")."lang='".mysql_real_escape_string($parms['lang'])."'";
      if ($parms['id'] > 0) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if (!empty($parms['id_in'])) $where .= ($where ? " AND " : " WHERE ")."`id` IN (".mysql_real_escape_string($parms['id_in']).")";
      if (isset($parms['parent_id'])) $where .= ($where ? " AND " : " WHERE ")."parent_id='".(int)$parms['parent_id']."'";
      if ($parms['category_id']) $where .= ($where ? " AND " : " WHERE ")."category_id='".(int)$parms['category_id']."'";
      if (isset($parms['href'])) $where .= ($where ? " AND " : " WHERE ")."href='".mysql_real_escape_string($parms['href'])."'";
      if ($parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(description, ' ', name, ' ', href) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   function limitString($parms) { $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY ord';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v);
   }

   function &GetCountRows($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM catalog".Catalog::whereString($parms));
   }

   function GetHref($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectValue("SELECT `href` FROM catalog".Catalog::whereString($parms));
   }

   function &GetRows($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT * FROM catalog".
                             Catalog::whereString($parms).Catalog::orderString($order).Catalog::limitString($limit), 'id');
   }

   function GetNames($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT `id`, `name`, `parent_id` FROM catalog".
                             Catalog::whereString($parms).Catalog::orderString($order).Catalog::limitString($limit), 'id');
   }

   function GetMinimums($parms = array(), $limit = array(), $order = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT `id`, `parent_id`, `is_active`, `name`, `href` FROM catalog".
                             Catalog::whereString($parms).Catalog::orderString($order).Catalog::limitString($limit), 'id');
   }//GetMinimums

   function &GetRow($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT * FROM catalog".Catalog::whereString($parms));
   }

   function &GetIds($parms = array(), $limit = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT id FROM catalog".Catalog::whereString($parms)." ORDER BY ord".Catalog::limitString($limit), 'id');
   }
}
?>