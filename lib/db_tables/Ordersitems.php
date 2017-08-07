<?php
include_once(Site::GetParms('tablesPath')."Orders.php");
class Ordersitems {
   function Ordersitems() { }
   function &Structure($init = false) {
      if ($init) {
         $form = array(
            'orders_id' => array('default' => Site::GetSession("ordersitems-orders_id"), 'form' => array('Select' => array('items' => Orders::GetNumbers(), 'field' => 'name', 'name' => 'Заказ'))),
            'items_id' => array('default' => 0, 'form' => array('Input' => array('name' => 'ID позиции', 'style' => 'style="width: 20%;"'))),
            'art' => array('default' => 0, 'form' => array('Input' => array('name' => 'Артикул', 'style' => 'style="width: 20%;"'))),
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Наименование позиции', 'style' => 'style="width: 100%;"'))),
            'price' => array('default' => 0, 'form' => array('Input' => array('name' => 'Цена', 'style' => 'style="width: 20%;"'))),
            'count' => array('default' => 0, 'form' => array('Input' => array('name' => 'Количество', 'style' => 'style="width: 20%;"'))),
         );
         $columns = array(
            'art' => array('name' => 'Артикул', 'sort' => true),
            'name' => array('name' => 'Наименование', 'function' => 'getNameStr', 'style' => 'width="100%" align="left"'),
            'price' => array('name' => 'Цена', 'function' => 'getPriceStr'),
            'count' => array('name' => 'Кол-во'),
            'column1' => array('name' => 'Сумма', 'function' => 'getSummaStr'),
         );
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'orders_items',
         'tableName'  => 'Позиции заказов'
      );
   }
   function &Get($id = '', $init = false) { return new Table(Ordersitems::Structure($init), $id); }
   function Update(&$rows) { $t = new Table(Ordersitems::Structure()); return $t->UpdateRows($rows); }
   function Save(&$rows) { $t = new Table(Ordersitems::Structure()); return $t->SaveRows($rows); }
   function Delete(&$rows) { $t = new Table(Ordersitems::Structure()); return $t->DeleteRows($rows); }

   function whereString($parms) {
      $where = '';
      if ($parms['id']) $where .= ($where ? " AND " : " WHERE ")."oi.id='".(int)$parms['id']."'";
      if ($parms['orders_id']) $where .= ($where ? " AND " : " WHERE ")."oi.orders_id='".(int)$parms['orders_id']."'";
      if ($parms['is_orders']) $where .= ($where ? " AND " : " WHERE ")."(o.id=oi.orders_id AND o.is_ok>0)";
      if ($parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(oi.items_id, ' ', oi.art, ' ', oi.name) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   function limitString($parms) { $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY oi.name';
      else foreach ($parms as $k => $v) return ' ORDER BY oi.'.$k.' '.strtoupper($v);
   }

   function &GetCountRows($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT COUNT(oi.id) FROM orders_items oi LEFT JOIN orders o ON o.id=oi.orders_id AND o.is_ok>0".Ordersitems::whereString($parms));
   }

   function &GetRows($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT oi.*, o.users_id FROM orders_items oi, orders o".
                             Ordersitems::whereString($parms).Ordersitems::orderString($order).
                             Ordersitems::limitString($limit), 'id');
   }

   function &GetRow($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT oi.* FROM orders_items oi LEFT JOIN orders o ON o.id=oi.orders_id AND o.is_ok>0".Ordersitems::whereString($parms));
   }
}
?>