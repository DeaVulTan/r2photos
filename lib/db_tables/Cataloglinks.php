<?php
class Cataloglinks {
   function Cataloglinks() { }
   function &Structure($init = false) {
      return array(
         'fields' => array(
            'id' => array('default' => 0, 'form' => ''),
            'ord' => array('default' => '', 'form' => ($init ? array('Input' => array()) : '')),
            'item_type' => array('default' => '', 'form' => ($init ? array('Input' => array()) : '')),
            'item_id' => array('default' => '', 'form' => ($init ? array('Input' => array()) : '')),
            'target_type' => array('default' => '', 'form' => ($init ? array('Input' => array()) : '')),
            'target_id' => array('default' => '', 'form' => ($init ? array('Input' => array()) : '')),
         ),
         'table'  => 'catalog_links'
      );
   }
   function &Get($id = '', $init = false) { return new Table(Cataloglinks::Structure($init), $id); }
   function Update(&$rows) { $t = new Table(Cataloglinks::Structure()); return $t->UpdateRows($rows); }
   function Save(&$rows) { $t = new Table(Cataloglinks::Structure()); return $t->SaveRows($rows); }
   function Delete(&$rows) { $t = new Table(Cataloglinks::Structure()); return $t->DeleteRows($rows); }

   function whereString($parms) {
      $where = '';
      if ($parms['id']) $where .= ($where ? " AND " : " WHERE ")."id='".$parms['id']."'";
      if ($parms['item_type']) $where .= ($where ? " AND " : " WHERE ")."item_type='".$parms['item_type']."'";
      if ($parms['item_id']) $where .= ($where ? " AND " : " WHERE ")."item_id='".$parms['item_id']."'";
      if ($parms['target_type']) $where .= ($where ? " AND " : " WHERE ")."target_type='".$parms['target_type']."'";
      if ($parms['target_id']) $where .= ($where ? " AND " : " WHERE ")."target_id='".$parms['target_id']."'";
      return $where;
   }

   function limitString($parms) { $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".$parms['offset'].", ".$parms['limit']; else $limit = " LIMIT ".$parms['limit']; } return $limit; }

   function &GetCountRows($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM catalog_links".Cataloglinks::whereString($parms));
   }

   function &GetRows($parms = array(), $limit = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT * FROM catalog_links".
                             Cataloglinks::whereString($parms).
                             Cataloglinks::limitString($limit), 'id');
   }

   function &GetRow($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT * FROM catalog_links".Cataloglinks::whereString($parms));
   }

   function &GetIds($parms = array(), $limit = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT id FROM catalog_links".Cataloglinks::whereString($parms).Cataloglinks::limitString($limit), 'id');
   }
}
?>