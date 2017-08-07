<?php
class OrdersitemsEngine extends AdminEngine {
   function OrdersitemsEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      $parms['orders_id'] = Site::GetSession($this->name."-orders_id");
      $parms['is_orders'] = true;
      return Ordersitems::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      $parms['orders_id'] = Site::GetSession($this->name."-orders_id");
      $parms['is_orders'] = true;
      return Ordersitems::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         if ($_POST['fromFF']) {
            Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
            Site::SetSession($this->name.'-orders_id', $_POST['orders_id']);
         }
         else Site::SetSession($this->name.'-orders_id', $this->values[0]);
         return Site::CreateUrl($this->name.'-list');
      }
   }

   function getPriceStr($name, $item) {
      echo number_format($item['price'], 2, '.', '');
   }

   function getSummaStr($name, $item) {
      echo number_format(($item['price'] * $item['count']), 2, '.', '');
   }

   function getNameStr($name, $item) {
      $db =& Site::GetDB();
      $href = $db->SelectValue("SELECT href FROM items WHERE id='".$item['items_id']."'");
      echo '<a href="/item/'.$item['items_id'].'-'.$href.'" target="_blank">'.$item['name'].'</a>';
   }

   function doBeforeRun() {
      if (IS_ORDERS !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'name');
         Site::SetSession($this->name."-order", 'asc');
      }
   }

   function topNav() {
      return '<a href="orders-list.htm" title="Заказы">Заказы</a> &gt; Позиции заказов';
   }
}
?>