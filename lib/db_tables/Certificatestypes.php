<?php
include_once(Site::GetParms('tablesPath')."Certificates.php");
class Certificatestypes {
   function Certificatestypes() { }
   function &Structure($init = false) {
      if ($init) {
         $form = array(
            'is_active' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Активность'))),
            'certificate_id' => array('default' => (int)Site::GetSession("certificatestypes-certificate_id"), 'form' => array('Select' => array('items' => Certificates::GetRows(), 'field' => 'name', 'name' => 'Сертификат'))),
            'ord' => array('default' => 0, 'form' => array('Input' => array('name' => 'Сортировка', 'style' => 'size="4"'))),
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Наименование', 'style' => 'style="width: 100%;"'))),
            //'price' => array('default' => 0, 'form' => array('Input' => array('name' => 'Цена', 'style' => 'style="width: 100%;"'))),
         );
         $columns = array(
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
            'ord' => array('name' => 'Сортировка', 'autoUpdate' => true, 'sort' => true, 'order' => true),
            'name' => array('name' => 'Наименование', 'style' => 'width="100%" align="left"', 'sort' => true),
         );
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'certificates_types',
         'tableName'  => 'Типы сертификата'
      );
   }

   function &Get($id = '', $init = false) { return new Table(Certificatestypes::Structure($init), $id); }
   function Update(&$rows) { $t = new Table(Certificatestypes::Structure()); return $t->UpdateRows($rows); }
   function Save(&$rows) { $t = new Table(Certificatestypes::Structure()); return $t->SaveRows($rows); }
   function Delete(&$rows) { $t = new Table(Certificatestypes::Structure()); return $t->DeleteRows($rows); }

   function whereString($parms) {
      $where = '';
      if ($parms['id'] > 0) $where .= ($where ? " AND " : " WHERE ")."`id`='".(int)$parms['id']."'";
      if ($parms['certificate_id'] > 0) $where .= ($where ? " AND " : " WHERE ")."`certificate_id`='".(int)$parms['certificate_id']."'";
      if (isset($parms['active'])) $where .= ($where ? " AND " : " WHERE ")."`is_active`='".(int)$parms['active']."'";
      if ($parms['like']) $where .= ($where ? " AND " : " WHERE ")."`name` LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   function limitString($parms) { $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY `ord` ASC, `id` ASC ';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v);
   }

   function &GetCountRows($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM `certificates_types` ".Certificatestypes::whereString($parms));
   }

   function &GetRows($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT * FROM `certificates_types` ".
                             Certificatestypes::whereString($parms).Certificatestypes::orderString($order).
                             Certificatestypes::limitString($limit), 'id');
   }

   function &GetRow($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT * FROM `certificates_types` ".Certificatestypes::whereString($parms));
   }

   function &GetIds($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT `id` FROM `certificates_types` ".Certificatestypes::whereString($parms).Certificatestypes::orderString($order).Certificatestypes::limitString($limit), 'id');
   }
}
?>