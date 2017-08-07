<?php
include_once(Site::GetParms('tablesPath')."Photographers.php");
class Photoimages {
   function Photoimages() { }
   function &Structure($init = false) {
      if ($init) {
         $form = array(
            'ord' => array('default' => 0, 'form' => array('Input' => array('name' => 'Сортировка', 'style' => 'size="4"'))),
            'parts_id' => array('default' => (int)Site::GetSession("photoimages-parts_id"), 'form' => array('Select' => array('items' => Photographers::GetNames(), 'field' => 'name', 'name' => 'Фотограф'))),
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Подпись', 'style' => 'style="width: 100%;"'))),
            'picture_small' => array('default' => '', 'form' => array('Upload' => array('text' => true, 'path' => 'data/image/photo', 'name' => 'Картинка (мал.)', 'style' => 'style="width: 40%;"'))),
            'picture_big' => array('default' => '', 'form' => array('Upload' => array('text' => true, 'path' => 'data/image/photo', 'name' => 'Картинка (бол.)', 'style' => 'style="width: 40%;"'))),
         );
         $columns = array(
            'ord' => array('name' => 'Сортировка', 'autoUpdate' => true, 'sort' => true, 'order' => true),
            'picture_small' => array('name' => 'Картинка', 'function' => 'getPictureStr'),
            'name' => array('name' => 'Подпись', 'style' => 'width="100%" align="left"', 'sort' => true),
         );
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'photo_images',
         'tableName'  => 'Фотоархив (картинки)'
      );
   }

   function &Get($id = '', $init = false) { return new Table(Photoimages::Structure($init), $id); }
   function Update(&$rows) { $t = new Table(Photoimages::Structure()); return $t->UpdateRows($rows); }
   function Save(&$rows) { $t = new Table(Photoimages::Structure()); return $t->SaveRows($rows); }
   function Delete(&$rows) { $t = new Table(Photoimages::Structure()); return $t->DeleteRows($rows); }

   function whereString($parms) {
      $where = '';
      if ($parms['id'] > 0) $where .= ($where ? " AND " : " WHERE ")."pi.id='".(int)$parms['id']."'";
      if ($parms['parts_id'] > 0) $where .= ($where ? " AND " : " WHERE ")."pi.parts_id='".(int)$parms['parts_id']."'";
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
      return $db->SelectValue("SELECT COUNT(pi.id) FROM photo_images pi".Photoimages::whereString($parms));
   }

   function &GetRows($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT pi.*, p.name AS partName FROM photo_images pi LEFT JOIN photo_archiv p ON p.id=pi.parts_id".
                             Photoimages::whereString($parms).Photoimages::orderString($order).
                             Photoimages::limitString($limit), 'id');
   }

   function &GetRow($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT pi.* FROM photo_images pi".Photoimages::whereString($parms));
   }

   function &GetIds($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT pi.id FROM photo_images pi".Photoimages::whereString($parms).Photoimages::orderString($order).Photoimages::limitString($limit), 'id');
   }
}
?>