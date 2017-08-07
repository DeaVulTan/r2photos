<?php
include_once(Site::GetParms('tablesPath')."Certificates.php");
class Certificatesphotos {
   function Certificatesphotos() { }
   function &Structure($init = false) {
      if ($init) {
         $form = array(
            'ord' => array('default' => 0, 'form' => array('Input' => array('name' => 'Сортировка', 'style' => 'size="4"'))),
            'certificate_id' => array('default' => (int)Site::GetSession("certificatesphotos-certificate_id"), 'form' => array('Select' => array('items' => Certificates::GetRows(), 'field' => 'name', 'name' => 'Сертификат'))),
            'picture' => array('default' => '', 'form' => array('Upload' => array('text' => true, 'path' => 'data/image/certificates', 'name' => 'Картинка (мал.)', 'style' => 'style="width: 40%;"'))),
         );
         $columns = array(
            'ord' => array('name' => 'Сортировка', 'autoUpdate' => true, 'sort' => true, 'order' => true),
            'picture' => array('name' => 'Картинка', 'function' => 'getPictureStr', 'style' => ' width="100%" nowrap '),
         );
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'certificates_photos',
         'tableName'  => 'Фотографии сертификатов'
      );
   }

   function &Get($id = '', $init = false) { return new Table(Certificatesphotos::Structure($init), $id); }
   function Update(&$rows) { $t = new Table(Certificatesphotos::Structure()); return $t->UpdateRows($rows); }
   function Save(&$rows) { $t = new Table(Certificatesphotos::Structure()); return $t->SaveRows($rows); }
   function Delete(&$rows) { $t = new Table(Certificatesphotos::Structure()); return $t->DeleteRows($rows); }

   function whereString($parms) {
      $where = '';
      if ($parms['id'] > 0) $where .= ($where ? " AND " : " WHERE ")."pi.id='".(int)$parms['id']."'";
      if ($parms['certificate_id'] > 0) $where .= ($where ? " AND " : " WHERE ")."pi.certificate_id='".(int)$parms['certificate_id']."'";
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
      return $db->SelectValue("SELECT COUNT(pi.id) FROM certificates_photos pi".Certificatesphotos::whereString($parms));
   }

   function &GetRows($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT pi.*, p.name AS partName FROM certificates_photos pi LEFT JOIN certificates p ON p.id=pi.certificate_id".
                             Certificatesphotos::whereString($parms).Certificatesphotos::orderString($order).
                             Certificatesphotos::limitString($limit), 'id');
   }

   function &GetRow($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT pi.* FROM certificates_photos pi".Certificatesphotos::whereString($parms));
   }

   function &GetIds($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT pi.id FROM certificates_photos pi".Certificatesphotos::whereString($parms).Certificatesphotos::orderString($order).Certificatesphotos::limitString($limit), 'id');
   }
}
?>