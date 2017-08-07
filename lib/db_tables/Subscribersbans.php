<?php
class Subscribersbans {
   function Subscribersbans() { }
   function &Structure() {
      if ($init) {
         $form = array();
         $columns = array();
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'subscribers_bans',
         'tableName'  => 'Банеры для рассылки'
      );
   }

   function Get($id = '') { return new Table(Subscribersbans::Structure(), $id); }
   function Update($rows) { $t = new Table(Subscribersbans::Structure()); return $t->UpdateRows($rows); }
   function Save($rows) { $t = new Table(Subscribersbans::Structure()); return $t->SaveRows($rows); }
   function Delete($rows) { $t = new Table(Subscribersbans::Structure()); return $t->DeleteRows($rows); }

   function whereString($parms) {
      $where = '';
      if ($parms['id']) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if ($parms['subscribers_main_id']) $where .= ($where ? " AND " : " WHERE ")."subscribers_main_id='".(int)$parms['subscribers_main_id']."'";
      if (isset($parms['ord'])) $where .= ($where ? " AND " : " WHERE ")."ord='".(int)$parms['ord']."'";
      return $where;
   }

   function limitString($parms) { $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY ord';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v);
   }

   function GetCountRows($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM subscribers_bans".Subscribersbans::whereString($parms));
   }

   function GetRows($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT * FROM subscribers_bans".
                             Subscribersbans::whereString($parms).Subscribersbans::orderString($order).
                             Subscribersbans::limitString($limit), 'id');
   }

   function GetRow($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT * FROM subscribers_bans".Subscribersbans::whereString($parms));
   }
}