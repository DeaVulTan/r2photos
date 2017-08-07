<?php
class Corr {
   function Corr() { }
   function &Structure($init = false) {
      if ($init) {
         $form = array(
            'is_active' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Активность'))),
            'idate' => array('default' => time(), 'form' => array('Calendar' => array('name' => 'Дата', 'style' => 'size="10"'))),
            'quest' => array('default' => '', 'form' => array('TextArea' => array('name' => 'Вопрос', 'style' => 'rows="10" cols="100" style="width: 100%;"'))),
            'answer' => array('default' => '', 'form' => array('TextArea' => array('name' => 'Ответ', 'style' => 'rows="10" cols="100" style="width: 100%;"')))
         );
         $columns = array(
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
            'idate' => array('name' => 'Дата', 'function' => 'getDataStr', 'sort' => true),
            'quest' => array('name' => 'Вопрос', 'style' => 'width="50%" align="left"'),
            'answer' => array('name' => 'Ответ', 'style' => 'width="50%" align="left"'),
         );
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'corr',
         'tableName'  => 'Переписка'
      );
   }

   function &Get($id = '', $init = false) { return new Table(self::Structure($init), $id); }
   function Update(&$rows) { $t = new Table(self::Structure()); return $t->UpdateRows($rows); }
   function Save(&$rows) { $t = new Table(self::Structure()); return $t->SaveRows($rows); }
   function Delete(&$rows) { $t = new Table(self::Structure()); return $t->DeleteRows($rows); }

   function whereString($parms) {
      $where = '';
      if ($parms['id']) $where .= ($where ? " AND " : " WHERE ")." id='".(int)$parms['id']."'";
      if ($parms['ord_id']) $where .= ($where ? " AND " : " WHERE ")." ord_id='".(int)$parms['ord_id']."'";
      if (isset($parms['active'])) $where .= ($where ? " AND " : " WHERE ")." is_active='".(int)$parms['active']."'";
      if ($parms['like']) $where .= ($where ? " AND " : " WHERE ")." CONCAT(quest,' ',answer) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   function limitString($parms) { $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY idate DESC';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v);
   }

   function &GetCountRows($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT COUNT(id) FROM corr ".self::whereString($parms));
   }

   function &GetRows($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT * FROM corr ".
                             self::whereString($parms).self::orderString($order).
                             self::limitString($limit), 'id');
   }

   function &GetRow($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT * FROM corr ".self::whereString($parms));
   }
}
?>
