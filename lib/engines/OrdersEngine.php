<?php
include_once(Site::GetParms('tablesPath')."Mainmenu.php");
include_once(Site::GetParms('tablesPath')."Items.php");
include_once(Site::GetParms('tablesPath')."Users.php");
class OrdersEngine {
   var $name; var $parms;
   function OrdersEngine($name, $parms) {
        $this->name = $name;
        $this->parms = $parms;
        $parmsT = array('href' => 'orders');
        if (defined("LANG")) $parmsT['lang'] = LANG;
        $menu = Mainmenu::GetRow($parmsT);
        $this->menu = $menu;
        $this->menuPath = (defined('LANG') && LANG <> 'ru' ? '/'.LANG : '').($this->menu['path'] ? $this->menu['path'] : '/');
        define("MENU_ID", ($this->menu['id'] ? $this->menu['id'] : 0));
        define("MENU_NAME", ($this->menu['name'] ? $this->menu['name'] : 'Оформление заказа'));
        define("MENU_PATH", $this->menuPath);
        unset($menu);
   }

   function run() {
      /*if (!defined("UID") || (defined("UID") && !UID)) {
         if ($this->parms['action'] == 'doajax') {
            echo 'Необходимо авторизоваться!';
            return true;
         }
         else return 'login';
      }
      else */
      if (!MENU_ID) Site::SetParms('bread-crumbs', array(MENU_NAME));
      $this->user =& Users::GetRow(array('id' => UID));
      $this->nameSession = mb_strtolower(PROJECT_NAME, 'utf-8').'_order_pass';
      $this->order =& $this->GetOrder();
      $this->items = $this->order['items'];
      if (!$this->parms['action']) return $this->viewList();
      else if ($this->parms['action'] == 'form') return $this->viewForm();
      else if ($this->parms['action'] == 'close') return $this->doClose();
      else if ($this->parms['action'] == 'ok') return $this->viewOk();
      else if ($this->parms['action'] == 'print') return $this->viewPrint();
      else if ($this->parms['action'] == 'excel') return $this->viewExcel();
      else if ($this->parms['action'] == 'do') return $this->doOrders();
      else if ($this->parms['action'] == 'doajax') return $this->doOrdersAjax();
      else if ($this->parms['action'] == 'update') return $this->doUpdate();
      else if ($this->parms['action'] == 'del' && $this->parms['id']) return $this->doDel();
      else return false;
   }

   function viewList() {
      if ($this->order['id'] > 0) {
         if (sizeOf($this->items) < 1) {
            $del = array($this->order['id']);
            if (!Orders::Delete($del)) return false;
         }
      }
      include(Site::GetTemplate($this->name, 'order'));
      return true;
   }

   function viewForm() {
      if (!$this->order['id']) return Site::CreateUrl($this->menuPath.$this->name);
      else if (sizeOf($this->items) < 1) {
         $del = array($this->order['id']);
         if (!Orders::Delete($del)) return false;
         return Site::CreateUrl($this->menuPath.$this->name);
      }
      ob_start();
      include(Site::GetTemplate($this->name, 'order-form'));
      if( Site::GetParms( 'isAjax' ) ) {
        Utils::JSONResponse( array(
            'success' => true,
            'content' => ob_get_clean(),
        ) );
      }
      ob_end_flush();
      return true;
   }

   function viewOk() {
      $order =& Orders::GetRow(array('id' => $this->parms['ok_id']));
      if (session_id() !== $order['session']) {
         include(Site::GetTemplate($this->name, 'order-not'));
         return true;
      }
      $order['items'] =& Ordersitems::GetRows(array('orders_id' => $order['id']));
      include(Site::GetTemplate($this->name, 'order-ok'));
      return true;
   }

   function viewPrint() {
      $order =& Orders::GetRow(array('id' => $this->parms['ok_id']));
      if (UID <> $order['users_id']) {
         include(Site::GetTemplate($this->name, 'order-not'));
         return true;
      }
      $order['items'] =& Ordersitems::GetRows(array('orders_id' => $order['id']));
      include(Site::GetTemplate($this->name, 'order-print'));
      return true;
   }

   function viewExcel() {
      $order =& Orders::GetRow(array('id' => $this->parms['ok_id']));
      if (UID <> $order['users_id']) {
         include(Site::GetTemplate($this->name, 'order-not'));
         return true;
      }
      $order['items'] =& Ordersitems::GetRows(array('orders_id' => $order['id']));
      $fName = 'order_'.$this->parms['ok_id'].'.csv';
      $fh = fopen(Site::GetParms('absolutePath').'data/file/'.$fName, 'w');
      fwrite($fh, "№;Наименование;Цена, руб.;Кол-во, шт.;Сумма, руб.\n");
      $ind = 1;
      foreach ($order['items'] as $id => $arr) {
         fwrite($fh, ($ind ++).";".$arr['name'].";".number_format($arr['price'], 2, '.', ' ').";".$arr['count'].";".number_format($arr['count']*$arr['price'], 2, '.', ' ')."\n");
      }
      fclose($fh);
      $this->doGet(Site::GetParms('absolutePath').'data/file/'.$fName, $fName);
      unlink(Site::GetParms('absolutePath').'data/file/'.$fName);
      return true;
   }

   function doGet($file, $fileName, $content_type = "application/xls") {
      global $REMOTE_ADDR, $HTTP_SERVER_VARS;

      $fsize = filesize($file);
      $ftime = gmdate("D, d M Y H:i:s T", filemtime($file));
      $fh = fopen($file, "rb" );

      if ($HTTP_SERVER_VARS["HTTP_RANGE"]) {
         $range = $HTTP_SERVER_VARS["HTTP_RANGE"];
         $range = str_replace("bytes=", "", $range);
         $range = str_replace("-", "", $range);
         if ($range) fseek($fh, $range);
      }

      $content = fread($fh, filesize($file));
      fclose($fh);

      if ($range) header("HTTP/1.1 206 Partial Content");
      else header("HTTP/1.1 200 OK");

      header("Content-Type: ".$content_type);
      header("Expires: ");
      header("Last-Modified: ".$ftime);
      header("Accept-Ranges: bytes");
      header("Content-Length: ".($fsize - $range));
      header("Content-Range: bytes ".$range."-".($fsize -1)."/".$fsize);
      header("Content-disposition: attachment; filename=".trim($fileName));
      header("Content-Transfer-Encoding: binary");
      header("Cache-Control: public");
      header("Pragma: public");
      print $content;
   }

   function doOrders() {
      if (!$this->order['id']) {
         $newOrders =& Orders::Get();
         $newId = $newOrders->StoreRow();
         $newPass = md5(PROJECT_NAME.$newId);
         $tmp[$newId] = array('idate' => time(), 'password' => $newPass, 'session' => session_id(), 'order_id' => $newId);
         Orders::Update($tmp);
         Site::SetSession($this->nameSession, $newPass);
         $items = array();
         foreach ($_POST['items'] as $k => $v) if (preg_match("/^[1-9][0-9]*$/", trim($v)) && $item =& Items::GetRow(array('id' => $k))) {
            $items[$k]['orders_id'] = $newId;
            $items[$k]['items_id'] = $k;
            $items[$k]['art'] = $item['art'];
            $items[$k]['name'] = $item['name'];
            $items[$k]['price'] = ($item['price']);
            $items[$k]['count'] = $v;
         }
         if (sizeOf($items) > 0) Ordersitems::Save($items);
         else {
            $del = array($this->order['id']);
            if (!Orders::Delete($del)) return false;
            Site::SetSession($this->nameSession, '');
         }
         unset($newOrders);
      }
      else {
         $itemsIds = array();
         foreach ($this->items as $k => $v) $itemsIds[$v['items_id']] = $k;
         $itemsAdd = array();
         $itemsUpd = array();
         foreach ($_POST['items'] as $k => $v) {
            $v = (int)trim($v);
            if (preg_match("/^[1-9][0-9]*$/", $v) && $item =& Items::GetRow(array('id' => $k))) {
               if (!array_key_exists($k, $itemsIds)) {
                  $itemsAdd[$k]['orders_id'] = $this->order['id'];
                  $itemsAdd[$k]['items_id'] = $k;
                  $itemsAdd[$k]['art'] = $item['art'];
                  $itemsAdd[$k]['name'] = $item['name'];
                  $itemsAdd[$k]['price'] = ($item['price']);
                  $itemsAdd[$k]['count'] = $v;
               }
               else $itemsUpd[$itemsIds[$k]]['count'] = $this->items[$itemsIds[$k]]['count'] + $v;
            }
         }
         if (sizeOf($itemsAdd) > 0) Ordersitems::Save($itemsAdd);
         if (sizeOf($itemsUpd) > 0) Ordersitems::Update($itemsUpd);
      }
      if( Site::GetParms( 'isAjax' ) ) {
        $basket = $this->GetBasketInfo();
        if( $item['catalog_id'] ) {
            $part = Catalog::GetRow( array( 'active' => 1, 'id' => $item['catalog_id'] ) );
        }
        ob_start();
        include( Site::GetTemplate( $this->name, 'popup' ) );
        Utils::JSONResponse( array( 'content' => ob_get_clean(), 'basket' => $basket ) );
      }
      return Site::CreateUrl($this->menuPath.$this->name);
   }

   function GetBasketInfo() {
    $this->nameSession = ($this->nameSession ? $this->nameSession : strtolower(PROJECT_NAME).'_order_pass');
    $this->order = $this->GetOrder();
    $this->items = $this->order['items'];
    $price = $count = 0;
    foreach( $this->items as $item ) {
        $count += $item['count'];
        $price += $item['count'] * $item['price'];
    }
    ob_start();
    include( Site::GetTemplate( $this->name, 'basket' ) );
    return ob_get_clean();
   }//GetBasketInfo

   function doOrdersAjax() {
      foreach ($_POST as $k => $v) $_POST[$k] = (int)addslashes(stripslashes(strip_tags($v)));
      if (!$_POST['id'] || !$_POST['count']) {
         echo 'Bad data';
         return true;
      }
      if (!$this->order['id']) {
         $newOrders =& Orders::Get();
         $newId = $newOrders->StoreRow();
         $newPass = md5(PROJECT_NAME.$newId);
         $tmp[$newId] = array('idate' => time(), 'password' => $newPass);
         Orders::Update($tmp);
         Site::SetSession($this->nameSession, $newPass);
         $items = array();
         $k = $_POST['id'];
         $v = $_POST['count'];
         if (preg_match("/^[1-9][0-9]*$/", trim($v)) && $item =& Items::GetRow(array('id' => $k))) {
            $items[$k]['orders_id'] = $newId;
            $items[$k]['items_id'] = $k;
            $items[$k]['art'] = $item['art'];
            $items[$k]['name'] = $item['name'];
            $items[$k]['price'] = ($item['price']);
            $items[$k]['count'] = $v;
         }
         if (sizeOf($items) > 0) {
            Ordersitems::Save($items);
            echo 'Позиция "'.$item['name'].'" была добавлена в корзину в количестве '.$v.' шт.';
         }
         else {
            $del = array($this->order['id']);
            if (!Orders::Delete($del)) return false;
            Site::SetSession($this->nameSession, '');
            echo 'Bad data';
         }
         unset($newOrders);
      }
      else {
         $itemsIds = array();
         foreach ($this->items as $k => $v) $itemsIds[$v['items_id']] = $k;
         $itemsAdd = array();
         $itemsUpd = array();
         $k = $_POST['id'];
         $v = $_POST['count'];
         $v = (int)trim($v);
         if (preg_match("/^[1-9][0-9]*$/", $v) && $item =& Items::GetRow(array('id' => $k))) {
            if (!array_key_exists($k, $itemsIds)) {
               $itemsAdd[$k]['orders_id'] = $this->order['id'];
               $itemsAdd[$k]['items_id'] = $k;
               $itemsAdd[$k]['art'] = $item['art'];
               $itemsAdd[$k]['name'] = $item['name'];
               $itemsAdd[$k]['price'] = ($item['price']);
               $itemsAdd[$k]['count'] = $v;
            }
            else $itemsUpd[$itemsIds[$k]]['count'] = $this->items[$itemsIds[$k]]['count'] + $v;
            echo 'Позиция "'.$item['name'].'" была добавлена в корзину в количестве '.$v.' шт.';
         }
         if (sizeOf($itemsAdd) > 0) Ordersitems::Save($itemsAdd);
         if (sizeOf($itemsUpd) > 0) Ordersitems::Update($itemsUpd);
      }
      return true;
   }

   function doUpdate() {
      $delArr = array();
      if (sizeOf($this->items) > 0) foreach ($this->items as $k => $v) {
         unset($this->items[$k]['users_id']);
         $nameReal = 'countOrd_'.$k;
         if (!trim($_POST[$nameReal])) {
            unset($this->items[$k]);
            $delArr[] = $k;
         }
         else $this->items[$k]['count'] = trim($_POST[$nameReal]);
      }
      if (sizeOf($delArr) > 0) Ordersitems::Delete($delArr);
      if (sizeOf($this->items) > 0) Ordersitems::Update($this->items);
      else {
         $delArr = array($this->order['id']);
         if (!Orders::Delete($delArr)) return false;
         Site::SetSession($this->nameSession, '');
      }
      $this->order = $this->GetOrder();
      $this->items = $this->order['items'];
      if( Site::GetParms( 'isAjax' ) ) {
        ob_start();
        include( Site::GetTemplate( $this->name, 'order' ) );
        Utils::JSONResponse( array(
            'success' => true,
            'basket' => Utils::GetBasketInfo(),
            'content' => ob_get_clean(),
        ) );
      }
      return $_SERVER['HTTP_REFERER'];
   }

   function doDel() {
      $itemsId = $this->parms['id'];
      if (array_key_exists($itemsId, $this->items)) {
         unset($this->items[$itemsId]);
         $delArr = array($itemsId);
         if (!Ordersitems::Delete($delArr)) return false;
      }
      if (sizeOf($this->items) < 1) {
         $delArr = array($this->order['id']);
         if (!Orders::Delete($delArr)) return false;
         Site::SetSession($this->nameSession, '');
      }
      return $_SERVER['HTTP_REFERER'];
   }

   function ReturnFormJSONError( $error ) {
    $result = array(
        'success' => false,
        'error' => $error,
    );
    Utils::JSONResponse( $result );
   }//ReturnFormJSONError

   function doClose() {
      if (sizeOf($this->items) > 0 && preg_match("/".strtolower(PROJECT_NAME)."/", $_SERVER['HTTP_REFERER'])) {
         foreach ($_POST as $k => $v) if (!is_array($v)) $parms[$k] = trim(strip_tags($v));

         if( !preg_match( '/.+/', $parms['fio'] ) ) {
            $this->ReturnFormJSONError( 'Укажите Ваше имя!' );
         }
         if( !preg_match( '/[0-9]{3,}/', $parms['phones'] ) ) {
            $this->ReturnFormJSONError( 'Укажите номер телефона!' );
         }
         if( !preg_match( '/.+@.+\..+/', $parms['email'] ) ) {
            $this->ReturnFormJSONError( 'Укажите Ваш e-mail!' );
         }
         if( $parms['delivery_info'] ) {
            if( !preg_match( '/[0-9]{2}\.[0-9]{2}\.[0-9]{4}(\s+[0-9]{1,2}:[0-9]{2})?/', $parms['delivery_date'] ) ) {
                $this->ReturnFormJSONError( 'Укажите время доставки!' );
            }
            if( !preg_match( '/.+/', $parms['delivery_street'] ) ) {
                $this->ReturnFormJSONError( 'Укажите улицу!' );
            }
            if( !preg_match( '/.+/', $parms['delivery_building'] ) ) {
                $this->ReturnFormJSONError( 'Укажите номер дома!' );
            }
         }

         $parms['subject'] = Utils::GetValue('subject_mail_after_order');
         $mailFiles = array(
            'logo' => Site::GetParms('absolutePath').'image/logo.png',
            'pixel' => Site::GetParms('absolutePath').'image/0.gif'
         );
         foreach ($mailFiles as $k => $v) $parms[$k] = "cid:".md5($v);
         $parms['iwh'] = getimagesize($mailFiles['logo']);
         $parms['bottom_address'] = Utils::GetValue('address_bottom_mail');
         $parms['bottom_line'] = Utils::GetValue('line_bottom_mail');
         ob_start();
          include(Site::GetTemplate('layout', 'mail-common'));
          $contentMail = ob_get_contents();
         ob_end_clean();
         include_once(Site::GetParms('libPath').'Mail.php');
         $userEmail = ($this->user['email'] ? $this->user['email'] : $parms['email']);
         $message = new MailMessage(array(
                                          'FROM' => Utils::GetValue('mailer_email'),
                                          'TO' => $userEmail,
                                          'CONTENT-TYPE' => 'text/html',
                                          'SUBJECT' => $parms['subject']),
                                    $contentMail, array_values($mailFiles));
         $message->send();
         $toEmail = Utils::GetValue('orders_email');
         $message2 = new MailMessage(array(
                                          'FROM' => $userEmail,
                                          'TO' => $toEmail,
                                          'CONTENT-TYPE' => 'text/html',
                                          'SUBJECT' => $parms['subject']),
                                    $contentMail, array_values($mailFiles));
         $message2->send();
         $order['users_id'] = ( int ) UID;
         $order['is_ok'] = 1;
         $order['city'] = Utils::CheckCityByIP();
         foreach( array( 'info', 'fio', 'phones', 'email', 'payment', 'code' ) as $name ) {
            $order[ $name ] = $parms[ $name ];
         }
         if( $parms['delivery_info'] ) {
            foreach( array( 'delivery_street', 'delivery_building', 'delivery_flat', 'delivery_code' ) as $name ) {
                $order[ $name ] = $parms[ $name ];
            }
            $order['delivery_info'] = 1;
            $order['delivery_idate'] = strtotime( $parms['delivery_date'] );
         }
        
         $rows[$this->order['id']] = $order;
         if (!Orders::Update($rows)) return false;
      }
      else {
         $del = array($this->order['id']);
         if (!Orders::Delete($del)) return false;
      }
      Site::SetSession($this->nameSession, '');
      if( Site::GetParms( 'isAjax' ) ) {
        $this->parms['ok_id'] = $this->order['id'];
        ob_start();
        $this->viewOk();
        Utils::JSONResponse( array(
            'success' => true,
            'content' => ob_get_clean(),
            'basket' => $this->GetBasketInfo(),
        ));
      }
      return Site::CreateUrl($this->menuPath.'orders/ok/'.$this->order['id']);
   }

   function GetBasketString() {
      $this->nameSession = ($this->nameSession ? $this->nameSession : strtolower(PROJECT_NAME).'_order_pass');
      $str = nl2br(Utils::GetValue('basket_string'));
      $countAll = 0;
      $priceAll = 0;
      $order =& OrdersEngine::GetOrder();
      if (sizeOf($order['items']) > 0) foreach ($order['items'] as $item) {
         $countAll += $item['count'];
         $priceAll += $item['count'] * $item['price'];
      }
      $str = preg_replace('/COUNT/', $countAll, $str);
      $str = preg_replace('/POS/', Utils::GetWordPos($countAll), $str);
      $str = preg_replace('/PRICE/', number_format($priceAll, 2, '.', ' '), $str);
      return $str;
   }

   function &GetOrder() {
      $pass = (Site::GetSession($this->nameSession) ? Site::GetSession($this->nameSession) : '');
      if (!$pass) return false;
      $order =& Orders::GetRow(array('password' => $pass));
      if ($order['id'] > 0) {
         $order['items'] =& Ordersitems::GetRows(array('orders_id' => $order['id']));
         return $order;
      }
      else {
         setcookie($this->nameSession, '');
         return false;
      }
   }
}
?>
