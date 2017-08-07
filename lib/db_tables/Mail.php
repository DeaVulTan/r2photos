<?php
class Mail {
   function Mail() { }
   function Structure($init = false) {
      if ($init) {
         $form = array(
            'idate' => array('default' => time(), 'form' => array('Calendar' => array('name' => 'Дата', 'style' => 'size="10"'))),
            'service' => array('default' => '', 'form' => array('Input' => array('name' => 'Название услуги', 'style' => 'style="width: 100%;"'))),
            'phone' => array('default' => '', 'form' => array('Input' => array('name' => 'Телефон', 'style' => 'style="width: 100%;"'))),
            'email' => array('default' => '', 'form' => array('Input' => array('name' => 'E-mail', 'style' => 'style="width: 100%;"'))),
            'text' => array('default' => '', 'form' => array('FCKeditor' => array('ToolbarSet' => '__set2__', 'Height' => 500, 'name' => 'Отзыв'))),
         );
         $columns = array(
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
            'idate' => array('name' => 'Дата', 'function' => 'getDataStr', 'sort' => true),
            'service' => array('name' => 'Услуга', 'style' => 'width="25%" align="left"', 'sort' => true),
            'phone' => array('name' => 'Телефон', 'style' => 'width="25%" align="left"', 'sort' => true),
            'email' => array('name' => 'E-mail', 'style' => 'width="25%" align="left"', 'sort' => true),
            'text' => array('name' => 'Отзыв', 'style' => 'width="25%" align="left"', 'sort' => true),
         );
         if (defined('LANG')) {
            $form = array_merge(array('lang' => array('default' => 'ru', 'form' => array('Select' => array('items' => Site::GetLangs(), 'name' => 'Язык')))), $form);
            $columns = array_merge(array('lang' => array('name' => 'Язык')), $columns);
         }
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'mail',
         'tableName'  => 'Сообщения'
      );
   }
   function Get($id = '', $init = false) { return new Table(Mail::Structure($init), $id); }
   function Update($rows) { $t = new Table(Mail::Structure()); return $t->UpdateRows($rows); }
   function Save($rows) { $t = new Table(Mail::Structure()); return $t->SaveRows($rows); }
   function Delete($rows) { $t = new Table(Mail::Structure()); return $t->DeleteRows($rows); }

   static function whereString($parms) {
      $where = '';
      if (isset($parms['id']) && $parms['id']) $where .= ($where ? " AND " : " WHERE ")."`id`='".(int)$parms['id']."'";
      if (isset($parms['like']) && $parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(`text`, ' ', `phone`, ' ', `email`) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   static function limitString($parms) { if (!isset($parms['limit'])) $parms['limit'] = 0; if (!isset($parms['offset'])) $parms['offset'] = 0; $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   static function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY `idate` DESC, `id` DESC';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v).($k <> 'id' ? ', id '.strtoupper($v) : '');
   }

   static function GetCountRows($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM `mail` ".Mail::whereString($parms));
   }

   static function GetRows($parms = array(), $limit = array(), $order = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT * FROM `mail` ".
                             Mail::whereString($parms).Mail::orderString($order).
                             Mail::limitString($limit), 'id');
   }

   static function GetRow($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectRow("SELECT * FROM `mail` ".Mail::whereString($parms));
   }

   static function GetIds($parms = array(), $limit = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT id FROM `mail` ".Mail::whereString($parms).' ORDER BY `idate` DESC, `id` DESC '.Mail::limitString($limit), 'id');
   }
}
?>