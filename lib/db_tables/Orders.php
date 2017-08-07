<?php
include_once(Site::GetParms('tablesPath')."Ordersitems.php");
include_once(Site::GetParms('tablesPath')."Ordersstatus.php");
include_once(Site::GetParms('tablesPath')."Users.php");
class Orders {

   const DEFAULT_INSTUDIO_USER = 888;  // id пользователя, от которого через Админку добавляются новые заказы

   function Orders() { }
   function &Structure($init = false) {
      if ($init) {
         $form = array(
             'order_id' => array('default' => '', 'form' => array('Input' => array('name' => 'Номер заказа. Если не введён, то будет назначен автоматически', 'style' => 'style="width: 20%;" '))),
            'users_id' => array('default' => (int)Site::GetSession("orders-users_id"), 'form' => array('Select' => array('items' => Users::GetRows(), 'field' => 'fio', 'name' => 'Пользователь'))),
            'code' => array('default' => '', 'form' => array('Input' => array('name' => 'Код скидки', 'style' => 'style="width: 20%;" '))),
            'status' => array('default' => 0, 'form' => array('Select' => array('name' => 'Статус', 'items' => array( 0 => array( 'name' => '---' ) ) + Ordersstatus::GetRows(), 'field' => 'name'))),
            'info' => array('default' => '', 'form' => array('TextArea' => array('name' => 'Дополнительная информация&nbsp;по&nbsp;доставке', 'style' => 'style="width: 50%;" rows="10" cols="100"'))),
             'is_ok' => array('default'=>1, 'form'=> array( 'Input'=> array('type' => 'hidden'))),
             'idate' => array('default'=>1, 'form'=> array( 'Input'=> array('type' => 'hidden'))),
             'id' => array('default'=>0, 'form'=> array( 'Input'=> array('type' => 'hidden'))),

         );
         $columns = array(
            'order_id' => array('name' => 'Номер заказа', 'function' => 'getOrderNum', 'sort' => true),
            'idate' => array('name' => 'Дата', 'function' => 'getDataStr', 'sort' => true),

            /*
             * В текущей версии отображаем СтатусЗаказа через выпадающий список. Других версий пока нет.
             *
             * */
            // выпадающий список со статусом заказа
            'status' => array('name' => 'Статус', 'sort' => true, 'autoUpdate' => true),

            // статическое отображение статуса заказа
            //'status' => array('name' => 'Статус', 'function' => 'getStatusStr', 'style' => 'width="25%" nowrap align="center"', 'sort' => true),

            'code' => array('name' => 'Код на скидку', 'style' => ' nowrap '),
            'column1' => array('name' => 'Заказчик', 'function' => 'getUserStr', 'style' => 'width="50%" align="left"'),
            'column3' => array('name' => 'Доставка', 'function' => 'getDeliveryStr', 'style' => 'width="50%" align="left"'),
            'summa' => array('name' => 'Сумма', 'function' => 'getSummaStr', 'sort' => true),
            'column2' => array('name' => 'Заказ', 'function' => 'getOrderStr', 'style' => 'nowrap="nowrap"'),
            'column4' => array('name' => 'Переписка', 'function' => 'getCorrStr', 'style' => 'nowrap="nowrap"'),
         );
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'orders',
         'tableName'  => 'Заказы'
      );
   }
   function &Get($id = '', $init = false) { return new Table(Orders::Structure($init), $id); }
   function Update(&$rows) { $t = new Table(Orders::Structure()); return $t->UpdateRows($rows); }
   function Save(&$rows) { $t = new Table(Orders::Structure()); return $t->SaveRows($rows); }
   function Delete(&$rows) { $t = new Table(Orders::Structure()); return $t->DeleteRows($rows); }

   function whereString($parms) {
      $where = '';
      if ($parms['id']) $where .= ($where ? " AND " : " WHERE ")."o.id='".(int)$parms['id']."'";
      if ($parms['users_id']) $where .= ($where ? " AND " : " WHERE ")."o.users_id='".(int)$parms['users_id']."'";
      if ($parms['password']) $where .= ($where ? " AND " : " WHERE ")."o.password='".mysql_real_escape_string($parms['password'])."'";
      if ($parms['is_fio']) $where .= ($where ? " AND " : " WHERE ")."o.is_ok>0";
      if ($parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(o.id, ' ', o.info, ' ', oi.art, ' ', oi.name, ' ', oi.items_id, ' ', o.code) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      if ($parms['checkstatus']) $where .= ($where ? " AND " : " WHERE ")."o.id='".(int)$parms['checkstatus']."' or o.order_id='".(int)$parms['checkstatus']."'";
      return $where;
   }

   function limitString($parms) { $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY o.idate DESC, o.id DESC';
      else foreach ($parms as $k => $v) return ' ORDER BY '.($k <> 'summa' ? 'o.' : '').$k.' '.strtoupper($v);
   }

   function &GetCountRows($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT COUNT(o.id) FROM orders o LEFT JOIN orders_items oi ON oi.orders_id=o.id".Orders::whereString($parms));
   }

   function &GetRows($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT o.*, SUM(oi.price*oi.count) AS summa FROM orders o LEFT JOIN orders_items oi ON oi.orders_id=o.id".
                             Orders::whereString($parms)."
                             GROUP BY o.id".Orders::orderString($order).
                             Orders::limitString($limit), 'id');
   }

   /**
    * @param array $parms
    * @param array $limit
    * @param array $order
    * @return mixed
    */
   function &GetRowsByUser($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT o.*, SUM(oi.price*oi.count) AS summa FROM orders o LEFT JOIN orders_items oi ON oi.orders_id=o.id".
          Orders::whereString($parms)."
                             GROUP BY o.id".Orders::orderString($order).
          Orders::limitString($limit), 'id');
   }

   function &GetNumbers($parms = array(), $limit = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT o.id, o.order_id, o.idate, CONCAT('Заказ №', o.id, ' от ', DATE_FORMAT(FROM_UNIXTIME(o.idate), \"%d.%m.%y\")) AS name FROM orders o".
                             Orders::whereString($parms)."
                             ORDER BY o.idate DESC, o.id DESC".
                             Orders::limitString($limit), 'id');
   }

   function &GetRow($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT o.* FROM orders o".Orders::whereString($parms));
   }

   /**
    * @param $id
    */
   function getStatusStrById($id)
   {
      static $statusList = false;

      if ($id) {
         $status = Ordersstatus::GetRow(array('id' => $id));
         $statusStr = $status['name'];
      } else {
         $statusStr = 'Неизвестно';
      }
      echo $statusStr;
   }

   function GetPaymentList() {
    return array(
        1 => 'Наличными курьеру',
        2 => 'Банковской картой',
    );
   }//GetPaymentList
}
?>