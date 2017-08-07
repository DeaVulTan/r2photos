<?php
include_once(Site::GetParms('tablesPath')."Corr.php");
class OrdersEngine extends AdminEngine {
   function OrdersEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      $parms['is_fio'] = true;
      return Orders::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      $parms['is_fio'] = true;
      return Orders::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         return Site::CreateUrl($this->name.'-list');
      }

       if ($this->parms['action'] == 'newblank') {

           $db = Site::GetDB();
           $newPass = md5(Utils::RandString(8));
           $idate = time();

           $db->Query("insert into orders (password, idate, users_id, is_ok) VALUES ('{$newPass}', {$idate}, ".Orders::DEFAULT_INSTUDIO_USER.", true )");
           $newId = $db->SelectLastInsertId();
           $db->Query("update orders set order_id={$newId} where id={$newId}");

           return Site::CreateUrl('orders-edit_'.$newId);
       }
      return;
   }

   function doBeforeChangeList() {
      if (is_array($_POST['deletedItem']) && sizeOf($_POST['deletedItem']) > 0 && $_POST['_del_x'] && $_POST['_del_y']) {
         $db =& Site::GetDB();
         foreach ($_POST['deletedItem'] as $k => $v) $db->Query("DELETE FROM orders_items WHERE orders_id='".$k."'");
      }


   }

    function doBeforeViewEdit() {

        if (!$this->itemId) {

            $this->table->data['is_ok'] = 1;
            $this->table->data['idate'] = time();
        }
    }


    function doBeforeUpdateData()
    {
        Site::SetSession('invalid-order-id', '');

        $order_id = intval($_POST['order_id']);
        $id = intval($_POST['id']);

        if ($_POST['x'] != '' && $_POST['y'] != '') {

            $db = Site::GetDB();

            // редактирование существующей записи
            if ($id > 0) {

                $res = $db->SelectValue("SELECT order_id from orders where id={$id}");

                if ($order_id != $res) {

                    $check_order_id = $db->SelectValue("Select id from orders where id={$order_id} or order_id={$order_id}");

                    // ошибка: ввели уже существующий id
                    if ($check_order_id['id'] > 0) {
                        Site::SetSession('invalid-order-id', $order_id);

                        $location = Site::CreateUrl('orders-edit_'.$id);
                        header('location: /admin/'.$location);
                        die;
                    }

                    else {
                        $db->Query("update orders set order_id={$order_id} where id={$id}");
                    }
                }
            }
            // добавление новой записи
            else {

                $check_order_id = $db->SelectValue("Select id from orders where id={$order_id} or order_id={$order_id}");

                // ошибка: ввели уже существующий id
                if ($check_order_id['id'] > 0) {
                    Site::SetSession('invalid-order-id', $order_id);

                    $location = Site::CreateUrl('orders-edit_'.$id);
                    header('location: /admin/'.$location);
                    die;
                }

                else {
                    $db->Query("update orders set order_id={$order_id} where id={$id}");
                }


            }
        }
    }


    function doAfterUpdateData($id)
    {
        $db = Site::GetDB();
        $data = $db->SelectValue("Select order_id from orders where id={$id}");

        // для случая, когда ручной-номер-заказа ещё не вводили
        if ($data['order_id'] < 1) {
            $db->Query("update orders set order_id={$id} where id={$id}");

        }
    }

   function getDataStr($name, $item) {
      echo date("d.m.Y", $item[$name]);
   }

    function getOrderNum($name, $item) {
        if ($item['order_id']>0) {
            echo $item['order_id'];
        }
        else  {
            echo $item['id'];

        }

    }
    function getUserStr($name, $item)
    {
        if ($item['users_id'] == Orders::DEFAULT_INSTUDIO_USER) {
            echo ''
            .'Менеждер в студии';

        } else {
            echo ''
                . 'Ф.И.О.: ' . ($item['fio']) . '<br />'
                . 'Телефон: ' . ($item['phones']) . '<br />'
                . 'E-mail: ' . ($item['email']) . '<br />'
                . 'Код на скидку: ' . ($item['code']) . '<br />';
        }
    }

   function getDeliveryStr($name, $item) {
       if( $item['delivery_info'] ) {
        echo ''
            .'Дата: '.( date( 'd.m.Y H:i', $item['delivery_idate'] ) ).'<br />'
            .'Город: '.( $item['city'] ).'<br />'
            .'Улица: '.( $item['delivery_street'] ).'<br />'
            .'Дом: '.( $item['delivery_building'] ).'<br />'
            .'Квартира: '.( $item['delivery_flat'] ).'<br />'
            .'Код/домофон: '.( $item['delivery_code'] ).'<br />'
            ;
       } else {
        echo 'Информация не указана<br />';
       }
       if( strlen( trim( $item['info'] ) ) ) {
        echo '<strong>Дополнительная информация:</strong> '.( nl2br( trim( $item['info'] ) ) ).'<br />';
       }
   }

   function getSummaStr($name, $item) {
      echo number_format($item[$name], 2, '.' ,'');
   }

   function getOrderStr($name, $item) {
      echo '[<a href="'.Site::CreateUrl('ordersitems-filter', array($item['id'])).'">позиции</a>]';
   }

   function getCorrStr($name, $item) {
      echo '[<a href="'.Site::CreateUrl('corr-filter', array($item['id'])).'">'.Corr::GetCountRows(array('ord_id'=>$item['id'])).'-смотреть</a>]';
   }
    function getStatusStr( $name, $item ) {
        static $statusList = false;
        if( $statusList === false ) {
            $statusList = array( 0 => array( 'name' => 'Неизвестно' ) ) + Ordersstatus::GetRows();
        }

        echo $statusList[ $item[ $name ] ]['name'];
    }

   function doBeforeRun() {
      if (IS_ORDERS !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'idate');
         Site::SetSession($this->name."-order", 'desc');
      }
   }
}
?>