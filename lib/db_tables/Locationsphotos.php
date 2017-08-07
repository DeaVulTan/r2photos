<?php
include_once(Site::GetParms('tablesPath')."Locations.php");
class Locationsphotos {
   function Locationsphotos() { }
   function &Structure($init = false) {
      if ($init) {
         $form = array(
            'is_active' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Активность'))),
            'ord' => array('default' => 0, 'form' => array('Input' => array('name' => 'Сортировка', 'style' => 'size="4"'))),
            'location_id' => array('default' => (int)Site::GetSession("locationsphotos-location_id"), 'form' => array('Select' => array('items' => Locations::GetRows(), 'field' => 'name', 'name' => 'Локация'))),
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Наименование', 'style' => 'style="width: 100%;"'))),
            'picture' => array('default' => '', 'form' => array('Upload' => array('text' => true, 'path' => 'data/image/locations', 'name' => 'Картинка', 'style' => 'style="width: 40%;"'))),
         );
         $columns = array(
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
            'ord' => array('name' => 'Сортировка', 'autoUpdate' => true, 'sort' => true, 'order' => true),
            'name' => array('name' => 'Наименование', 'style' => 'width="50%" align="left"', 'sort' => true),
            'picture' => array('name' => 'Картинка', 'function' => 'getPictureStr', 'style' => ' width="50%" nowrap '),
         );
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'locations_photos',
         'tableName'  => 'Фотографии локаций'
      );
   }

   function &Get($id = '', $init = false) { return new Table(Locationsphotos::Structure($init), $id); }
   function Update(&$rows) { $t = new Table(Locationsphotos::Structure()); return $t->UpdateRows($rows); }
   function Save(&$rows) { $t = new Table(Locationsphotos::Structure()); return $t->SaveRows($rows); }
   function Delete(&$rows) { $t = new Table(Locationsphotos::Structure()); return $t->DeleteRows($rows); }

   function whereString($parms) {
      $where = '';
      if ($parms['id'] > 0) $where .= ($where ? " AND " : " WHERE ")."pi.id='".(int)$parms['id']."'";
      if (isset($parms['active'])) $where .= ($where ? " AND " : " WHERE ")."pi.is_active='".(int)$parms['active']."'";
      if ($parms['location_id'] > 0) $where .= ($where ? " AND " : " WHERE ")."pi.location_id='".(int)$parms['location_id']."'";
      if ($parms['location_in']) $where .= ($where ? " AND " : " WHERE ")."pi.location_id IN (".(mysql_real_escape_string($parms['location_in'])).")";
      if ($parms['like']) $where .= ($where ? " AND " : " WHERE ")."pi.name LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   function limitString($parms) { $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY pi.ord';
      else foreach ($parms as $k => $v) return ' ORDER BY pi.'.$k.' '.strtoupper($v);
   }

   function &GetCountRows($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT COUNT(pi.id) FROM `locations_photos` pi ".Locationsphotos::whereString($parms));
   }

   function &GetRows($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT pi.*, p.name AS partName FROM `locations_photos` pi LEFT JOIN locations p ON p.id=pi.location_id".
                             Locationsphotos::whereString($parms).Locationsphotos::orderString($order).
                             Locationsphotos::limitString($limit), 'id');
   }

   function &GetRow($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT pi.* FROM `locations_photos` pi".Locationsphotos::whereString($parms));
   }

   function &GetIds($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT pi.id FROM `locations_photos` pi".Locationsphotos::whereString($parms).Locationsphotos::orderString($order).Locationsphotos::limitString($limit), 'id');
   }
}
?>