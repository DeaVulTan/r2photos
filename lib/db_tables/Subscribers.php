<?php
class Subscribers {
   function Subscribers() { }
   function &Structure($init = false) {
      if ($init) {
         $form = array(
            'is_active' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Активность'))),
            'idate' => array('default' => time(), 'form' => array('Calendar' => array('name' => 'Дата', 'style' => 'size="10"'))),
            'email' => array('default' => '', 'form' => array('Input' => array('name' => 'E-mail', 'style' => 'style="width: 100%;"'))),
            'is_news' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'На новости'))),
         );
         $columns = array(
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
            'idate' => array('name' => 'Дата', 'function' => 'getDataStr', 'sort' => true),
            'email' => array('name' => 'E-mail', 'style' => 'align="left" width="100%"', 'sort' => true),
         );
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'subscribers',
         'tableName'  => 'Подписчики'
      );
   }

   function &Get($id = '', $init = false) { return new Table(Subscribers::Structure($init), $id); }
   function Update(&$rows) { $t = new Table(Subscribers::Structure()); return $t->UpdateRows($rows); }
   function Save(&$rows) { $t = new Table(Subscribers::Structure()); return $t->SaveRows($rows); }
   function Delete(&$rows) { $t = new Table(Subscribers::Structure()); return $t->DeleteRows($rows); }

   function whereString($parms) {
      $where = '';
      if ($parms['id']) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if ($parms['email']) $where .= ($where ? " AND " : " WHERE ")."email='".mysql_real_escape_string($parms['email'])."'";
      if (isset($parms['news'])) $where .= ($where ? " AND " : " WHERE ")."is_news='".(int)$parms['news']."'";
      if (isset($parms['active'])) $where .= ($where ? " AND " : " WHERE ")."is_active='".(int)$parms['active']."'";
      if ($parms['act']) $where .= ($where ? " AND " : " WHERE ")."is_active='1'";
      if ($parms['nact']) $where .= ($where ? " AND " : " WHERE ")."is_active='0'";
      if ($parms['password']) $where .= ($where ? " AND " : " WHERE ")."MD5(CONCAT(email,':',id))='".mysql_real_escape_string($parms['password'])."'";
      if ($parms['like']) $where .= ($where ? " AND " : " WHERE ")."email LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   function limitString($parms) { $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY email';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v);
   }

   function &GetCountRows($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM subscribers".Subscribers::whereString($parms));
   }

   function &GetRows($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT * FROM subscribers".
                             Subscribers::whereString($parms).Subscribers::orderString($order).
                             Subscribers::limitString($limit), 'id');
   }

   function &GetRow($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT * FROM subscribers".Subscribers::whereString($parms));
   }
}
?>
