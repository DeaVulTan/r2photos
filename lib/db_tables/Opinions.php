<?php
class Opinions {
   function Opinions() { }
   function &Structure($init = false) {
      if ($init) {
         $form = array(
            'is_active' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Активность'))),
            'idate' => array('default' => time(), 'form' => array('Calendar' => array('name' => 'Дата', 'style' => 'size="10"'))),
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'ФИО', 'style' => 'style="width: 100%;"'))),
            'email' => array('default' => '', 'form' => array('Input' => array('name' => 'E-mail', 'style' => 'style="width: 100%;"'))),
            'text' => array('default' => '', 'form' => array('TextArea' => array('name' => 'Отзыв', 'style' => 'rows="10" cols="100" style="width: 100%;"'))),
            'href' => array('default' => '', 'form' => array('Input' => array('name' => 'URL', 'style' => 'style="width: 100%;"'))),
         );
         $columns = array(
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
            'idate' => array('name' => 'Дата', 'function' => 'getDataStr', 'sort' => true),
            'text' => array('name' => 'Отзыв', 'style' => 'width="100%" align="left"'),
         );
         if (defined('LANG')) {
            $form = array_merge(array('lang' => array('default' => 'ru', 'form' => array('Select' => array('items' => Site::GetLangs(), 'name' => 'Язык')))), $form);
            $columns = array_merge(array('lang' => array('name' => 'Язык')), $columns);
         }
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'opinions',
         'tableName'  => 'Отзывы'
      );
   }

   function &Get($id = '', $init = false) { return new Table(Opinions::Structure($init), $id); }
   function Update(&$rows) { $t = new Table(Opinions::Structure()); return $t->UpdateRows($rows); }
   function Save(&$rows) { $t = new Table(Opinions::Structure()); return $t->SaveRows($rows); }
   function Delete(&$rows) { $t = new Table(Opinions::Structure()); return $t->DeleteRows($rows); }

   function whereString($parms) {
      $where = '';
      if ($parms['id']) $where .= ($where ? " AND " : " WHERE ")."f.id='".(int)$parms['id']."'";
      if ($parms['lang']) $where .= ($where ? " AND " : " WHERE ")."f.lang='".mysql_real_escape_string($parms['lang'])."'";
      if (isset($parms['active'])) $where .= ($where ? " AND " : " WHERE ")."f.is_active='".(int)$parms['active']."'";
      if (isset($parms['href'])) $where .= ($where ? " AND " : " WHERE ")."f.href='".mysql_real_escape_string($parms['href'])."'";
      if ($parms['like']) $where .= ($where ? " AND " : " WHERE ")." CONCAT(f.text, ' ', f.name, ' ', f.email, ' ', f.href) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   function limitString($parms) { $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY f.idate DESC';
      else foreach ($parms as $k => $v) return ' ORDER BY f.'.$k.' '.strtoupper($v);
   }

   function &GetCountRows($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT COUNT(f.id) FROM opinions f".Opinions::whereString($parms));
   }

   function &GetRows($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT f.* FROM opinions f".
                             Opinions::whereString($parms).Opinions::orderString($order).
                             Opinions::limitString($limit), 'id');
   }

   function &GetRow($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT f.* FROM opinions f".Opinions::whereString($parms));
   }
}
?>