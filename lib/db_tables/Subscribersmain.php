<?php
include_once(Site::GetParms('tablesPath')."Subscriberscont.php");
include_once(Site::GetParms('tablesPath')."Subscribersbans.php");
class Subscribersmain {
   function Subscribersmain() { }
   function Structure($init = false) {
      if ($init) {
         $form = array(
            'theme' => array('default' => '', 'form' => array('Input' => array('name' => 'Тема рассылки', 'style' => 'style="width: 100%;"'))),
            'test_emails' => array('default' => '', 'form' => array('Input' => array('name' => 'Адреса тестовой рассылки', 'style' => 'style="width: 100%;"'))),
            'text_custom' => array('default' => '', 'form' => array('FCKeditor' => array('ToolbarSet' => '__set2__', 'Height' => 500, 'name' => 'Текст'))),
            'time_to_send' => array('default' => ceil(time()/3600) * 3600, 'form' => array('Datetimepicker' => array('name' => 'Дата для рассылки', 'style' => 'style="width: 100%;"'))),
         );
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'subscribers_main',
         'tableName'  => 'Рассылка'
      );
   }

   function Get($id = '', $init = false) { return new Table(Subscribersmain::Structure($init), $id); }
   function Update($rows) { $t = new Table(Subscribersmain::Structure()); return $t->UpdateRows($rows); }
   function Save($rows) { $t = new Table(Subscribersmain::Structure()); return $t->SaveRows($rows); }
   function Delete($rows) { $t = new Table(Subscribersmain::Structure()); return $t->DeleteRows($rows); }

   function whereString($parms) {
      $where = '';
      if (isset($parms['send'])) $where .= ($where ? " AND " : " WHERE ")."is_send='".(int)$parms['send']."'";
      if ($parms['id']) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if ($parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(theme, ' ', text) LIKE '%".addslashes($parms['like'])."%'";
      return $where;
   }

   function limitString($parms) { $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY idate DESC';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v);
   }

   function GetCountRows($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM subscribers_main".Subscribersmain::whereString($parms));
   }

   function GetRows($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT * FROM subscribers_main".
                             Subscribersmain::whereString($parms).Subscribersmain::orderString($order).
                             Subscribersmain::limitString($limit), 'id');
   }

   function GetRow($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT * FROM subscribers_main".Subscribersmain::whereString($parms));
   }
}