<?php
class Sitepages {
   function Sitepages() { }
   function Structure($init = false) {
      if ($init) {
         $form = array(
            'url' => array('default' => '', 'form' => array('Input' => array('name' => 'Наименование', 'style' => 'style="width: 100%;"'))),
         );
         $columns = array(
            'url' => array('name' => 'Страница', 'style' => 'width="100%" align="left"', 'sort' => true),
         );
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'sitepages',
         'tableName'  => 'Посещаемые страницы сайта'
      );
   }
   function Get($id = '', $init = false) { return new Table(Sitepages::Structure($init), $id); }
   function Update($rows) { $t = new Table(Sitepages::Structure()); return $t->UpdateRows($rows); }
   function Save($rows) { $t = new Table(Sitepages::Structure()); return $t->SaveRows($rows); }
   function Delete($rows) { $t = new Table(Sitepages::Structure()); return $t->DeleteRows($rows); }

   static function whereString($parms) {
      $where = '';
      if (isset($parms['id']) && $parms['id']) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if (isset($parms['url']) && $parms['url']) $where .= ($where ? " AND " : " WHERE ")."url='".mysql_real_escape_string($parms['url'])."'";
      if (isset($parms['like']) && $parms['like']) $where .= ($where ? " AND " : " WHERE ")."url LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   static function limitString($parms) { if (!isset($parms['limit'])) $parms['limit'] = 0; if (!isset($parms['offset'])) $parms['offset'] = 0; $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   static function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY url';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v);
   }

   static function GetCountRows($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM sitepages".Sitepages::whereString($parms));
   }

   static function GetRows($parms = array(), $limit = array(), $order = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT * FROM sitepages".
                             Sitepages::whereString($parms).Sitepages::orderString($order).
                             Sitepages::limitString($limit), 'id');
   }

   static function GetRow($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectRow("SELECT * FROM sitepages".Sitepages::whereString($parms));
   }
}
?>