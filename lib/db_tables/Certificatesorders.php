<?php
include_once(Site::GetParms('tablesPath')."Certificates.php");
include_once(Site::GetParms('tablesPath')."Locations.php");
class Certificatesorders {
   function Certificatesorders() { }
   function Structure($init = false) {
      if ($init) {
         $order = ( Site::GetParms( 'admin-current-item' ) ? Certificatesorders::GetRow( array( 'id' => Site::GetParms( 'admin-current-item' ) ) ) : array() );
         $form = array(
            'idate' => array('default' => time(), 'form' => array('Calendar' => array('name' => 'Дата', 'style' => 'size="10"'))),
            'certificate_id' => array('default' => 0, 'form' => array('Select' => array('name' => 'Сертификат', 'items' => Certificates::GetRows(), 'field' => 'name_full'))),
            'certificate_type_id' => array('default' => 0, 'form' => array('Select' => array('name' => 'Тип сертификата', 'items' => Certificatestypes::GetRows( array( 'certificate_id' => ( int ) $order['certificate_id'] ) ), 'field' => 'name'))),
            'fio' => array('default' => '', 'form' => array('Input' => array('name' => 'ФИО', 'style' => 'style="width: 100%;"'))),
            'phone' => array('default' => '', 'form' => array('Input' => array('name' => 'Телефон', 'style' => 'style="width: 100%;"'))),
            'email' => array('default' => '', 'form' => array('Input' => array('name' => 'E-mail', 'style' => 'style="width: 100%;"'))),
            'delivery' => array('default' => 0, 'form' => array('Select' => array('name' => 'Доставка', 'items' => Utils::GetDeliveryList()))),
            'delivery_address' => array('default' => '', 'form' => array('Input' => array('name' => 'Адрес&nbsp;доставки', 'style' => 'style="width: 100%;"'))),
            'location' => array('default' => 0, 'form' => array('Select' => array('name' => 'Дополнительная локация', 'items' => Utils::GetLocationList()))),
            'makeup' => array('default' => 0, 'form' => array('Checkbox' => array('name' => 'Макияж и укладка'))),
            'photobook' => array('default' => 0, 'form' => array('Checkbox' => array('name' => 'Фотокнига'))),
            'photopicture' => array('default' => 0, 'form' => array('Checkbox' => array('name' => 'Фотокартина'))),
            'code' => array('default' => '', 'form' => array('Input' => array('name' => 'Код на скидку', 'style' => 'style="width: 100px;"'))),
         );
         $columns = array(
            'idate' => array('name' => 'Дата', 'function' => 'getDataStr', 'sort' => true),
            'certificate_id' => array('name' => 'Сертификат', 'style' => ' nowrap ', 'function' => 'getCertificateStr'),
            'location' => array('name' => 'Локация', 'style' => ' nowrap ', 'function' => 'getLocationStr'),
            'fio' => array('name' => 'ФИО', 'style' => 'width="33%" align="left"', 'sort' => true),
            'phone' => array('name' => 'Телефон', 'style' => 'width="33%" align="left"', 'sort' => true),
            'email' => array('name' => 'E-mail', 'style' => 'width="33%" align="left"', 'sort' => true),
            'code' => array('name' => 'Код на скидку', 'style' => ' nowrap ', 'sort' => true),
         );
         if (defined('LANG')) {
            $form = array_merge(array('lang' => array('default' => 'ru', 'form' => array('Select' => array('items' => Site::GetLangs(), 'name' => 'Язык')))), $form);
            $columns = array_merge(array('lang' => array('name' => 'Язык')), $columns);
         }
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'certificates_orders',
         'tableName'  => 'Заказы сертификатов'
      );
   }
   function Get($id = '', $init = false) { return new Table(Certificatesorders::Structure($init), $id); }
   function Update($rows) { $t = new Table(Certificatesorders::Structure()); return $t->UpdateRows($rows); }
   function Save($rows) { $t = new Table(Certificatesorders::Structure()); return $t->SaveRows($rows); }
   function Delete($rows) { $t = new Table(Certificatesorders::Structure()); return $t->DeleteRows($rows); }

   static function whereString($parms) {
      $where = '';
      if (isset($parms['id']) && $parms['id']) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if (isset($parms['lang']) && $parms['lang']) $where .= ($where ? " AND " : " WHERE ")."lang='".mysql_real_escape_string($parms['lang'])."'";
      if (isset($parms['like']) && $parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(`fio`, ' ', `phone`, ' ', `email`, ' ', `delivery_address`) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   static function limitString($parms) { if (!isset($parms['limit'])) $parms['limit'] = 0; if (!isset($parms['offset'])) $parms['offset'] = 0; $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   static function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY `idate` DESC, `id` DESC';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v).($k <> 'id' ? ', id '.strtoupper($v) : '');
   }

   static function GetCountRows($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM `certificates_orders` ".Certificatesorders::whereString($parms));
   }

   static function GetRows($parms = array(), $limit = array(), $order = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT * FROM `certificates_orders` ".
                             Certificatesorders::whereString($parms).Certificatesorders::orderString($order).
                             Certificatesorders::limitString($limit), 'id');
   }

   static function GetRow($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectRow("SELECT * FROM `certificates_orders` ".Certificatesorders::whereString($parms));
   }

   static function GetIds($parms = array(), $limit = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT `id` FROM `certificates_orders` ".Certificatesorders::whereString($parms).' ORDER BY `idate` DESC, `id` DESC '.Certificatesorders::limitString($limit), 'id');
   }
}
?>
