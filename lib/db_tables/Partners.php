<?php
class Partners {
   function Partners() { }
   function &Structure($init = false) {
      if ($init) {
         $form = array(
            'is_active' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Активность'))),
            'ord' => array('default' => 0, 'form' => array('Input' => array('name' => 'Сортировка', 'style' => 'size="4"'))),
            'logo' => array('default' => '', 'form' => array('Upload' => array('text' => true, 'path' => 'data/image/partners', 'name' => 'Логотип', 'style' => 'style="width: 40%;"'))),
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Наименование', 'style' => 'style="width: 100%;"'))),
            'href' => array('default' => '', 'form' => array('Input' => array('name' => 'URL', 'style' => 'style="width: 100%;"'))),
            'description' => array('default' => '', 'form' => array('FCKeditor' => array('ToolbarSet' => '__set2__', 'Height' => 500, 'name' => 'Описание'))),
         );
         $columns = array(
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
            'ord' => array('name' => 'Сортировка', 'autoUpdate' => true, 'sort' => true, 'order' => true),
            'logo' => array('name' => 'Логотип', 'function' => 'getPictureStr'),
            'name' => array('name' => 'Наименование', 'style' => 'width="100%" align="left"', 'sort' => true),
         );
         if (defined('LANG')) {
            $form = array_merge(array('lang' => array('default' => 'ru', 'form' => array('Select' => array('items' => Site::GetLangs(), 'name' => 'Язык')))), $form);
            $columns = array_merge(array('lang' => array('name' => 'Язык')), $columns);
         }
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'partners',
         'tableName'  => 'Партнеры'
      );
   }

   function &Get($id = '', $init = false) { return new Table(Partners::Structure($init), $id); }
   function Update(&$rows) { $t = new Table(Partners::Structure()); return $t->UpdateRows($rows); }
   function Save(&$rows) { $t = new Table(Partners::Structure()); return $t->SaveRows($rows); }
   function Delete(&$rows) { $t = new Table(Partners::Structure()); return $t->DeleteRows($rows); }

   function whereString($parms) {
      $where = '';
      if (isset($parms['active'])) $where .= ($where ? " AND " : " WHERE ")."is_active='".(int)$parms['active']."'";
      if ($parms['id'] > 0) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if ($parms['lang']) $where .= ($where ? " AND " : " WHERE ")."lang='".mysql_real_escape_string($parms['lang'])."'";
      if (isset($parms['href'])) $where .= ($where ? " AND " : " WHERE ")."href='".mysql_real_escape_string($parms['href'])."'";
      if ($parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(`name`, ' ', `description`, ' ', `href`) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   function limitString($parms) { $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY ord, id';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v);
   }

   function &GetCountRows($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT COUNT(id) FROM partners".Partners::whereString($parms));
   }

   function &GetRows($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT * FROM partners ".
                             Partners::whereString($parms).Partners::orderString($order).
                             Partners::limitString($limit), 'id');
   }

   function &GetRow($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT * FROM partners".Partners::whereString($parms));
   }
}
?>
