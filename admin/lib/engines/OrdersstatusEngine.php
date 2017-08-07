<?php
class OrdersstatusEngine extends AdminEngine {
    function OrdersstatusEngine($name, $parms) { $this->AdminEngine($name, $parms); }

    function GetCountItems($parms = array()) {
        $parms['lang'] = Site::GetSession($this->name."-lang");
        $parms['like'] = Site::GetSession($this->name."-like");
        return Ordersstatus::GetCountRows($parms);
    }

    function &GetItems($parms = array(), $limit = array(), $order = array()) {
        $parms['lang'] = Site::GetSession($this->name."-lang");
        $parms['like'] = Site::GetSession($this->name."-like");
        return Ordersstatus::GetRows($parms, $limit, $order);
    }

    function selfRun() {
        if ($this->parms['action'] == 'filter') {
            Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
            if (defined('LANG')) Site::SetSession($this->name.'-lang', $_POST['lang']);
            return Site::CreateUrl($this->name.'-list');
        }
        return;
    }

    function getImageStr($name, $item) {
        $file = $item['picture'];
        echo $file && file_exists(Site::GetParms('absolutePath').$file) ? '<img src="../'.$file.'" />' : 'Картинки нет.';
    }

    function getDataStr($name, $item) {
        echo date("d.m.Y", $item[$name]);
    }

    function doBeforeRun() {
        if (IS_ORDERS !== true) return '404.htm';
        if (!Site::GetSession($this->name."-sort")) {
            Site::SetSession($this->name."-sort", 'name');
            Site::SetSession($this->name."-order", 'asc');
        }
    }
}
?>