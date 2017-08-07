<?php
class Bannersplaces {
   function Bannersplaces() { }
   function Structure($init = false) {
      if ($init) {
         $form = array(
            'is_slider' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Последовательный&nbsp;показ'))),
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Наименование', 'style' => 'style="width: 100%;"'))),
         );
         $columns = array(
            'is_slider' => array('name' => 'Последовательный показ', 'autoUpdate' => true),
            'name' => array('name' => 'Наименование', 'style' => 'width="100%" align="left"', 'sort' => true),
            'column1' => array('name' => 'Банеры', 'function' => 'getItemsStr', 'style' => 'nowrap="nowrap"'),
         );
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'banner_places',
         'tableName'  => 'Банерные места'
      );
   }
   function Get($id = '', $init = false) { return new Table(Bannersplaces::Structure($init), $id); }
   function Update($rows) { $t = new Table(Bannersplaces::Structure()); return $t->UpdateRows($rows); }
   function Save($rows) { $t = new Table(Bannersplaces::Structure()); return $t->SaveRows($rows); }
   function Delete($rows) { $t = new Table(Bannersplaces::Structure()); return $t->DeleteRows($rows); }

   static function whereString($parms) {
      $where = '';
      if (isset($parms['id']) && $parms['id'] > 0) $where .= ($where ? " AND " : " WHERE ")."bp.id='".(int)$parms['id']."'";
      if (isset($parms['id_in'])) $where .= ($where ? " AND " : " WHERE ")."bp.id IN (".mysql_real_escape_string($parms['id_in']).")";
      if (isset($parms['like']) && $parms['like']) $where .= ($where ? " AND " : " WHERE ")."name LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   static function limitString($parms) { if (!isset($parms['limit'])) $parms['limit'] = 0; if (!isset($parms['offset'])) $parms['offset'] = 0; $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   static function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY bp.name';
      else foreach ($parms as $k => $v) return ' ORDER BY bp.'.$k.' '.strtoupper($v);
   }

   static function GetCountRows($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectValue("SELECT COUNT(bp.id) FROM banner_places bp".Bannersplaces::whereString($parms));
   }

   static function GetRows($parms = array(), $limit = array(), $order = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT bp.*, COUNT(b.id) AS bannersCount FROM banner_places bp LEFT JOIN banners b ON b.place_id=bp.id".
                             Bannersplaces::whereString($parms)."
                             GROUP BY bp.id".Bannersplaces::orderString($order).
                             Bannersplaces::limitString($limit), 'id');
   }

   static function GetRow($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectRow("SELECT bp.* FROM banner_places bp".Bannersplaces::whereString($parms));
   }
}
?>