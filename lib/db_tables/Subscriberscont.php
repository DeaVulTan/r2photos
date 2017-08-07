<?php
class Subscriberscont {
   function Subscriberscont() { }
   function Structure() {
      if ($init) {
         $form = array();
         $columns = array();
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'subscribers_cont',
         'tableName'  => 'Содержимое для рассылки'
      );
   }

   function Get($id = '') { return new Table(Subscriberscont::Structure(), $id); }
   function Update($rows) { $t = new Table(Subscriberscont::Structure()); return $t->UpdateRows($rows); }
   function Save($rows) { $t = new Table(Subscriberscont::Structure()); return $t->SaveRows($rows); }
   function Delete($rows) { $t = new Table(Subscriberscont::Structure()); return $t->DeleteRows($rows); }

   function whereString($parms) {
      $where = '';
      if ($parms['subscribers_main_id']) $where .= ($where ? " AND " : " WHERE ")."subscribers_main_id='".(int)$parms['subscribers_main_id']."'";
      if ($parms['subs_id']) $where .= ($where ? " AND " : " WHERE ")."subs_id='".(int)$parms['subs_id']."'";
      if ($parms['subs_type']) $where .= ($where ? " AND " : " WHERE ")."subs_type='".mysql_real_escape_string($parms['subs_type'])."'";
      if ($parms['id']) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      return $where;
   }

   function limitString($parms) { $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY subscribers_main_id, ord';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v);
   }

   function GetCountRows($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM subscribers_cont".Subscriberscont::whereString($parms));
   }

   function GetRows($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT * FROM subscribers_cont".
                             Subscriberscont::whereString($parms).Subscriberscont::orderString($order).
                             Subscriberscont::limitString($limit), 'id');
   }

   function GetSubsIds($parms = array()) {
      $db =& Site::GetDB();
      $ret = array();
      $items =& Subscriberscont::GetRows($parms);
      if (sizeOf($items) > 0) foreach ($items as $id => $item) $ret[$id] = $item['subs_id'];
      return $ret;
   }

   function GetRow($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT * FROM subscribers_cont".Subscriberscont::whereString($parms));
   }
}