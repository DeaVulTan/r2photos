<?php
class Mainmenu {
   function Mainmenu() { }
   function Structure($init = false) {
      if ($init) {
         $form = array(
            'is_active' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Активность'))),
            'ord' => array('default' => 0, 'form' => array('Input' => array('name' => 'Сортировка', 'style' => 'size="4"'))),
            'parent_id' => array('default' => (int)Site::GetSession("mainmenu-parent_id"), 'form' => array('Select' => array('items' => Mainmenu::GetList(), 'field' => 'name', 'name' => 'Родитель'))),
            'is_top' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'В&nbsp;верхнем&nbsp;меню'))),
            'is_slider' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'В&nbsp;слайдере'))),
            'is_bottom' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'В&nbsp;нижнем&nbsp;меню'))),
            'href' => array('default' => '', 'form' => array('Input' => array('name' => 'URL', 'style' => 'style="width: 100%;"'))),
            'path' => array('default' => '', 'form' => array('Input' => array('name' => 'Путь', 'style' => 'style="width: 100%;"'))),
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Наименование', 'style' => 'style="width: 100%;"'))),
            'picture' => array('default' => '', 'form' => array('Upload' => array('text' => true, 'path' => 'data/image/pages' ,'name' => 'Картинка', 'style' => 'style="width: 40%;"'))),
            'content' => array('default' => '', 'form' => array('FCKeditor' => array('ToolbarSet' => '__set2__', 'Height' => 500, 'name' => 'Содержание')))
         );
         $columns = array(
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
            'ord' => array('name' => 'Сортировка', 'autoUpdate' => true, 'sort' => true, 'order' => true),
            'is_top' => array('name' => 'В&nbsp;верхнем меню', 'autoUpdate' => true),
            'is_slider' => array('name' => 'В&nbsp;слайдере', 'autoUpdate' => true),
            'is_bottom' => array('name' => 'В&nbsp;нижнем меню', 'autoUpdate' => true),
            'href' => array('name' => 'URL', 'style' => 'align="left"', 'sort' => true),
            'name' => array('name' => 'Наименование', 'style' => 'width="100%" align="left"', 'sort' => true),
            'column1' => array('name' => 'Подменю', 'function' => 'getSubStr', 'style' => 'nowrap="nowrap"'),
         );
         if (defined('LANG')) {
            $form = array_merge(array('lang' => array('default' => 'ru', 'form' => array('Select' => array('items' => Site::GetLangs(), 'name' => 'Язык')))), $form);
            $columns = array_merge(array('lang' => array('name' => 'Язык')), $columns);
         }
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'main_menu',
         'tableName'  => 'Навигация'
      );
   }
   function Get($id = '', $init = false) { return new Table(Mainmenu::Structure($init), $id); }
   function Update($rows) { $t = new Table(Mainmenu::Structure()); return $t->UpdateRows($rows); }
   function Save($rows) { $t = new Table(Mainmenu::Structure()); return $t->SaveRows($rows); }
   function Delete($rows) { $t = new Table(Mainmenu::Structure()); return $t->DeleteRows($rows); }

   function GetList($id = 0, $offset = '') {
      $res = array();
      $temp = Mainmenu::GetRows(array('parent_id' => $id));
      if (sizeOf($temp) > 0) foreach ($temp as $k => $v) {
         $v['name'] = $offset.$v['name'];
         if (!sizeOf($res)) $res[0] = array('id' => 0, 'name' => '[Нет родителя]');
         $res[$k] = $v;
         $arr = Mainmenu::GetList($k, '-'.$offset);
         if (sizeOf($arr) > 0) foreach ($arr as $k1 => $v1) $res[$k1] = $v1;
      }
      return $res;
   }

   static function whereString($parms) {
      $where = '';
      if (isset($parms['id']) && $parms['id']) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if (isset($parms['lang']) && $parms['lang']) $where .= ($where ? " AND " : " WHERE ")."lang='".mysql_real_escape_string($parms['lang'])."'";
      if (isset($parms['parent_id'])) $where .= ($where ? " AND " : " WHERE ")."parent_id='".(int)$parms['parent_id']."'";
      if (isset($parms['active'])) $where .= ($where ? " AND " : " WHERE ")."is_active='".(int)$parms['active']."'";
      if (isset($parms['top'])) $where .= ($where ? " AND " : " WHERE ")."is_top='".(int)$parms['top']."'";
      if (isset($parms['slider'])) $where .= ($where ? " AND " : " WHERE ")."is_slider='".(int)$parms['slider']."'";
      if (isset($parms['bottom'])) $where .= ($where ? " AND " : " WHERE ")."is_bottom='".(int)$parms['bottom']."'";
      if ($parms['somewhere']) $where .= ($where ? " AND " : " WHERE ")."`is_bottom` + `is_top` + `is_slider` > 0";
      if (isset($parms['href'])) $where .= ($where ? " AND " : " WHERE ")."href='".mysql_real_escape_string($parms['href'])."'";
      if (isset($parms['path'])) $where .= ($where ? " AND " : " WHERE ")."path='".mysql_real_escape_string($parms['path'])."'";
      if (isset($parms['like']) && $parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(name, ' ', content, ' ', href, ' ', path) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   static function limitString($parms) { if (!isset($parms['limit'])) $parms['limit'] = 0; if (!isset($parms['offset'])) $parms['offset'] = 0; $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   static function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY ord';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v);
   }

   static function GetCountRows($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM main_menu".Mainmenu::whereString($parms));
   }

   static function GetRows($parms = array(), $limit = array(), $order = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT * FROM main_menu".
                             Mainmenu::whereString($parms).Mainmenu::orderString($order).
                             Mainmenu::limitString($limit), 'id');
   }

   static function GetRow($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectRow("SELECT * FROM main_menu".Mainmenu::whereString($parms));
   }
}
?>