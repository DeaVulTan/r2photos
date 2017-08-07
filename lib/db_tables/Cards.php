<?php
include_once(Site::GetParms('tablesPath')."City.php");
class Cards {
   function Cards() { }
   function Structure($init = false) {
      if ($init) {
         $form = array(
            'is_active' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Активность'))),
            'cardnum' => array('default' => '', 'form' => array('Input' => array('name' => 'Номер&nbsp;карты', 'style' => 'style="width: 100%;"'))),
            'city' => array('default' => (int)Site::GetSession('cards-city'), 'form' => array('Select' => array('name' => 'Город', 'items' => City::GetRows(), 'field' => 'name'))),
            'idate' => array('default' => time(), 'form' => array('Calendar' => array('name' => 'Дата&nbsp;окончания&nbsp;действия', 'style' => 'size="10"'))),
            'status' => array('default' => 1, 'form' => array('Select' => array('name' => 'Статус', 'items' => self::GetStatusList()))),
            'fio' => array('default' => '', 'form' => array('Input' => array('name' => 'ФИО', 'style' => 'style="width: 100%;"'))),
            'email' => array('default' => '', 'form' => array('Input' => array('name' => 'E-mail', 'style' => 'style="width: 100%;"'))),
         );
         $columns = array(
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
            'city' => array('name' => 'Город', 'function' => 'getCityStr', 'sort' => true),
            'status' => array('name' => 'Статус', 'function' => 'getStatusStr', 'sort' => true),
            'idate' => array('name' => 'Дата&nbsp;окончания&nbsp;действия', 'function' => 'getDataStr', 'sort' => true),
            'cardnum' => array('name' => 'Номер', 'style' => 'width="25%" align="left"', 'sort' => true),
            'fio' => array('name' => 'ФИО', 'style' => 'width="25%" align="left"', 'sort' => true),
            'email' => array('name' => 'E-mail', 'style' => 'width="25%" align="left"', 'sort' => true),
         );
         if (defined('LANG')) {
            $form = array_merge(array('lang' => array('default' => 'ru', 'form' => array('Select' => array('items' => Site::GetLangs(), 'name' => 'Язык')))), $form);
            $columns = array_merge(array('lang' => array('name' => 'Язык')), $columns);
         }
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'cards',
         'tableName'  => 'Сертификаты'
      );
   }
   function Get($id = '', $init = false) { return new Table(Cards::Structure($init), $id); }
   function Update($rows) { $t = new Table(Cards::Structure()); return $t->UpdateRows($rows); }
   function Save($rows) { $t = new Table(Cards::Structure()); return $t->SaveRows($rows); }
   function Delete($rows) { $t = new Table(Cards::Structure()); return $t->DeleteRows($rows); }

   static function whereString($parms) {
      $where = '';
      if (isset($parms['id']) && $parms['id']) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if (isset($parms['active'])) $where .= ($where ? " AND " : " WHERE ")."is_active='".(int)$parms['active']."'";
      if (isset($parms['like']) && $parms['like']) $where .= ($where ? " AND " : " WHERE ")."`cardnum` LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      if (isset($parms['cardnum']) && $parms['cardnum']) $where .= ($where ? " AND " : " WHERE ")."`cardnum` = '".mysql_real_escape_string($parms['cardnum'])."'";
      return $where;
   }

   static function limitString($parms) { if (!isset($parms['limit'])) $parms['limit'] = 0; if (!isset($parms['offset'])) $parms['offset'] = 0; $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   static function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY `id` DESC';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v).($k <> 'id' ? ', id '.strtoupper($v) : '');
   }

   static function GetCountRows($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM `cards` ".Cards::whereString($parms));
   }

   static function GetRows($parms = array(), $limit = array(), $order = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT * FROM `cards` ".
                             Cards::whereString($parms).Cards::orderString($order).
                             Cards::limitString($limit), 'id');
   }

   static function GetRow($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectRow("SELECT * FROM `cards` ".Cards::whereString($parms));
   }

   static function GetIds($parms = array(), $limit = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT `id` FROM `cards` ".Cards::whereString($parms).' ORDER BY `id` DESC '.Cards::limitString($limit), 'id');
   }

   const STATUS_SOLD = 1;
   const STATUS_ACTIVE = 2;
   const STATUS_OFFLINE = 3;
   static function GetStatusList() {
       return array(
        self::STATUS_SOLD => 'Продан',
        self::STATUS_ACTIVE => 'Активирован',
        self::STATUS_OFFLINE => 'Оффлайн',
       );
   }//GetStatusList
}
?>