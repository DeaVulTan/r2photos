<?php
include_once(Site::GetParms('tablesPath')."Users.php");
class Activation {
   function Activation() { }
   function Structure($init = false) {
      if ($init) {
         $form = array(
            'idate' => array('default' => time(), 'form' => array('Calendar' => array('name' => 'Дата&nbsp;активации', 'style' => 'size="10"'))),
            //'idate_visit' => array('default' => time(), 'form' => array('Calendar' => array('name' => 'Желаемая&nbsp;дата&nbsp;посещения', 'style' => 'size="10"'))),
            'fio' => array('default' => '', 'form' => array('Input' => array('name' => 'Имя', 'style' => 'style="width: 100%;"'))),
            /* 'idate_birthday' => array('default' => time(), 'form' => array('Calendar' => array('name' => 'День рождения', 'style' => 'size="10"'))),
            'users_id' => array('default' => 0, 'form' => array('Select' => array('name' => 'Пользователь', 'items' => self::GetUsersList()))), */
            'city' => array('default' => '', 'form' => array('Input' => array('name' => 'Город', 'style' => 'style="width: 100%;"'))),
            'cardnum' => array('default' => '', 'form' => array('Input' => array('name' => 'Номер&nbsp;карты', 'style' => 'style="width: 100%;"'))),
            'phone' => array('default' => '', 'form' => array('Input' => array('name' => 'Телефон', 'style' => 'style="width: 100%;"'))),
            'email' => array('default' => '', 'form' => array('Input' => array('name' => 'E-mail', 'style' => 'style="width: 100%;"'))),
         );
         $columns = array(
            'idate' => array('name' => 'Дата', 'function' => 'getDataStr', 'sort' => true),
            //'idate_visit' => array('name' => 'Дата посещения', 'function' => 'getDataStr', 'sort' => true),
            //'idate_birthday' => array('name' => 'День рождения', 'function' => 'getDataStr', 'sort' => true),
            'city' => array('name' => 'Город', 'style' => 'width="20%" align="left"', 'sort' => true),
            'cardnum' => array('name' => 'Карта', 'style' => 'width="20%" align="left"', 'sort' => true),
            'fio' => array('name' => 'ФИО', 'style' => 'width="20%" align="left"', 'sort' => true),
            'phone' => array('name' => 'Телефон', 'style' => 'width="20%" align="left"', 'sort' => true),
            'email' => array('name' => 'E-mail', 'style' => 'width="20%" align="left"', 'sort' => true),
         );
         if (defined('LANG')) {
            $form = array_merge(array('lang' => array('default' => 'ru', 'form' => array('Select' => array('items' => Site::GetLangs(), 'name' => 'Язык')))), $form);
            $columns = array_merge(array('lang' => array('name' => 'Язык')), $columns);
         }
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'activation',
         'tableName'  => 'Активация подарков'
      );
   }
   function Get($id = '', $init = false) { return new Table(Activation::Structure($init), $id); }
   function Update($rows) { $t = new Table(Activation::Structure()); return $t->UpdateRows($rows); }
   function Save($rows) { $t = new Table(Activation::Structure()); return $t->SaveRows($rows); }
   function Delete($rows) { $t = new Table(Activation::Structure()); return $t->DeleteRows($rows); }

   static function whereString($parms) {
      $where = '';
      if (isset($parms['id']) && $parms['id']) $where .= ($where ? " AND " : " WHERE ")."`id`='".(int)$parms['id']."'";
      if (isset($parms['active'])) $where .= ($where ? " AND " : " WHERE ")."`is_active`='".(int)$parms['active']."'";
      if (isset($parms['like']) && $parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(`cardnum`, ' ', `city`, ' ', `phone`) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   static function limitString($parms) { if (!isset($parms['limit'])) $parms['limit'] = 0; if (!isset($parms['offset'])) $parms['offset'] = 0; $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   static function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY `idate` DESC, `id` DESC';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v).($k <> 'id' ? ', id '.strtoupper($v) : '');
   }

   static function GetCountRows($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM `activation` ".Activation::whereString($parms));
   }

   static function GetRows($parms = array(), $limit = array(), $order = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT * FROM `activation` ".
                             Activation::whereString($parms).Activation::orderString($order).
                             Activation::limitString($limit), 'id');
   }

   static function GetRow($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectRow("SELECT * FROM `activation` ".Activation::whereString($parms));
   }

   static function GetIds($parms = array(), $limit = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT `id` FROM `activation` ".Activation::whereString($parms).' ORDER BY `idate` DESC, `id` DESC '.Activation::limitString($limit), 'id');
   }

   static function GetUsersList() {
    $result = array(
        0 => '[Нет]',
    );
    $usersList = Users::GetRows();
    foreach( $usersList as $user ) {
        $result[ $user['id'] ] = $user['fio'].( empty( $user['phones'] ) ? '' : ' - '.$user['phones'] );
    }
    return $result;
   }//GetUsersList
}
?>