<?php
class Vacs {
   function Vacs() { }
   function &Structure($init = false) {
      if ($init) {
         $form = array(
            'ord' => array('default' => 0, 'form' => array('Input' => array('name' => 'Сортировка', 'style' => 'size="4"'))),
            'idate' => array('default' => time(), 'form' => array('Calendar' => array('name' => 'Дата публикации'))),
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Наименование', 'style' => 'style="width: 100%;"'))),
            'prof' => array('default' => '', 'form' => array('Input' => array('name' => 'Профессиональная область', 'style' => 'style="width: 100%;"'))),
            'city' => array('default' => '', 'form' => array('Input' => array('name' => 'Город', 'style' => 'style="width: 100%;"'))),
            'oklad' => array('default' => '', 'form' => array('Input' => array('name' => 'Оклад', 'style' => 'style="width: 100%;"'))),
            'text' => array('default' => '', 'form' => array('FCKeditor' => array('ToolbarSet' => '__set2__', 'Height' => 500, 'name' => 'Информация'))),
            'href' => array('default' => '', 'form' => array('Input' => array('name' => 'URL', 'style' => 'style="width: 100%;"'))),
         );
         $columns = array(
            'ord' => array('name' => 'Сортировка', 'autoUpdate' => true, 'sort' => true, 'order' => true),
            'city' => array('name' => 'Город', 'style' => 'align="left"', 'sort' => true),
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
         'table'  => 'vacs',
         'tableName'  => 'Вакансии'
      );
   }

   function &Get($id = '', $init = false) { return new Table(Vacs::Structure($init), $id); }
   function Update(&$rows) { $t = new Table(Vacs::Structure()); return $t->UpdateRows($rows); }
   function Save(&$rows) { $t = new Table(Vacs::Structure()); return $t->SaveRows($rows); }
   function Delete(&$rows) { $t = new Table(Vacs::Structure()); return $t->DeleteRows($rows); }

   function whereString($parms) {
      $where = '';
      if ($parms['id']) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if ($parms['lang']) $where .= ($where ? " AND " : " WHERE ")."lang='".mysql_real_escape_string($parms['lang'])."'";
      if ($parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(`name`, ' ', `text`, ' ', `oklad`, ' ', `city`, ' ', `prof`, ' ', `href`) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      if ($parms['city']) $where .= ($where ? " AND " : " WHERE ")."`city` = '".mysql_real_escape_string($parms['city'])."'";
      if ($parms['name']) $where .= ($where ? " AND " : " WHERE ")."`name` = '".mysql_real_escape_string($parms['name'])."'";
      if ($parms['prof']) $where .= ($where ? " AND " : " WHERE ")."`prof` = '".mysql_real_escape_string($parms['prof'])."'";
      if (isset($parms['href'])) $where .= ($where ? " AND " : " WHERE ")."href='".mysql_real_escape_string($parms['href'])."'";
      return $where;
   }

   function limitString($parms) { $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY ord';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v);
   }

   function &GetCountRows($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM vacs".Vacs::whereString($parms));
   }

   function &GetRows($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT * FROM vacs".
                             Vacs::whereString($parms).Vacs::orderString($order).
                             Vacs::limitString($limit), 'id');
   }

   function &GetRow($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT * FROM vacs".Vacs::whereString($parms));
   }

   function &GetCityes($parms = array(), $limit = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT DISTINCT(city) FROM vacs".
                             Vacs::whereString($parms).
                             ' ORDER BY `city` '.
                             Vacs::limitString($limit), 'city');
   }

   function &GetProfs($parms = array(), $limit = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT DISTINCT(prof) FROM vacs".
                             Vacs::whereString($parms).
                             ' ORDER BY `prof` '.
                             Vacs::limitString($limit), 'prof');
   }
}
?>