<?php
class Ordersstatus {
    function Ordersstatus() { }
    function Structure($init = false) {
        if ($init) {
            $form = array(
                'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Наименование', 'style' => 'style="width: 100%;"'))),
                'is_default' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Статус по-умолчанию'))),
            );
            $columns = array(
                'is_default' => array('name' => 'Статус по-умолчанию', 'autoUpdate' => true),
                'name' => array('name' => 'Наименование', 'style' => 'width="100%" align="left"', 'sort' => true),
            );
            if (defined('LANG')) {
                $form = array_merge(array('lang' => array('default' => 'ru', 'form' => array('Select' => array('items' => Site::GetLangs(), 'name' => 'Язык')))), $form);
                $columns = array_merge(array('lang' => array('name' => 'Язык')), $columns);
            }
        }
        return array(
            'form' => ($form ? $form : false),
            'columns' => ($columns ? $columns : false),
            'table'  => 'orders_status',
            'tableName'  => 'Статусы заказов'
        );
    }
    function Get($id = '', $init = false) { return new Table(Ordersstatus::Structure($init), $id); }
    function Update($rows) { $t = new Table(Ordersstatus::Structure()); return $t->UpdateRows($rows); }
    function Save($rows) { $t = new Table(Ordersstatus::Structure()); return $t->SaveRows($rows); }
    function Delete($rows) { $t = new Table(Ordersstatus::Structure()); return $t->DeleteRows($rows); }

    static function whereString($parms) {
        $where = '';
        if (isset($parms['id']) && $parms['id']) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
        if (isset($parms['lang']) && $parms['lang']) $where .= ($where ? " AND " : " WHERE ")."lang='".mysql_real_escape_string($parms['lang'])."'";
        if (isset($parms['active'])) $where .= ($where ? " AND " : " WHERE ")."is_active='".(int)$parms['active']."'";
        if (isset($parms['is_default'])) $where .= ($where ? " AND " : " WHERE ")."is_default='".(int)$parms['is_default']."'";
        if (isset($parms['like']) && $parms['like']) $where .= ($where ? " AND " : " WHERE ")." `name` LIKE '%".mysql_real_escape_string($parms['like'])."%' ";
        return $where;
    }

    static function limitString($parms) { if (!isset($parms['limit'])) $parms['limit'] = 0; if (!isset($parms['offset'])) $parms['offset'] = 0; $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

    static function orderString($parms = array()) {
        if (!sizeOf($parms)) return ' ORDER BY name ASC ';
        else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v).($k <> 'id' ? ', id '.strtoupper($v) : '');
    }

    static function GetCountRows($parms = array()) {
        $db = Site::GetDB();
        return $db->SelectValue("SELECT COUNT(*) FROM orders_status".Ordersstatus::whereString($parms));
    }

    static function GetRows($parms = array(), $limit = array(), $order = array()) {
        $db = Site::GetDB();
        return $db->SelectSet("SELECT * FROM orders_status".
            Ordersstatus::whereString($parms).Ordersstatus::orderString($order).
            Ordersstatus::limitString($limit), 'id');
    }

    static function GetRow($parms = array()) {
        $db = Site::GetDB();
        return $db->SelectRow("SELECT * FROM orders_status".Ordersstatus::whereString($parms));
    }

    static function GetIds($parms = array(), $limit = array()) {
        $db = Site::GetDB();
        return $db->SelectSet("SELECT id FROM orders_status".Ordersstatus::whereString($parms).' ORDER BY name ASC '.Ordersstatus::limitString($limit), 'id');
    }
}
?>