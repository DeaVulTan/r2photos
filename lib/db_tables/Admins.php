<?php
class Admins {
   function Admins() { }
   function Structure($init = false) {
      if ($init) {
         $form = array(
            'is_active' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'fio' => array('default' => '', 'form' => array('Input' => array())),
            'login' => array('default' => '', 'form' => array('Input' => array())),
            'is_admins' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_config' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_meta' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_menu' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_news' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_articles' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_catalog' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_users' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_faq' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_opinions' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_forum' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_ban' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_vacs' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_subscribe' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_orders' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_photo' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_votes' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_licence' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_partners' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_callback' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_actions' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_photographers' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_activation' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_locations' => array('default' => 0, 'form' => array('CheckBox' => array())),
            'is_retouches' => array('default' => 0, 'form' => array('CheckBox' => array())),
         );
         $columns = array(
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
            'login' => array('name' => 'Логин', 'addstyle' => ' class="field" size="10"'),
            'fio' => array('name' => 'ФИО', 'style' => 'width="100%" align="left"'),
         );
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'admins',
         'tableName'  => 'Права доступа'
      );
   }
   function Get($id = '', $init = false) { return new Table(Admins::Structure($init), $id); }
   function Update($rows) { $t = new Table(Admins::Structure()); return $t->UpdateRows($rows); }
   function Save($rows) { $t = new Table(Admins::Structure()); return $t->SaveRows($rows); }
   function Delete($rows) { $t = new Table(Admins::Structure()); return $t->DeleteRows($rows); }

   function whereString($parms) {
      $where = '';
      if (isset($parms['menu']) && $parms['menu']) $where .= ($where ? " AND " : " WHERE ")."menus LIKE '%|".mysql_real_escape_string($parms['menu'])."|%'";
      if (isset($parms['id']) && $parms['id']) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if (isset($parms['active']) && $parms['active']) $where .= ($where ? " AND " : " WHERE ")."is_active='".(int)$parms['active']."'";
      if (isset($parms['admin']) && $parms['admin']) $where .= ($where ? " AND " : " WHERE ")."is_admins='".(int)$parms['admin']."'";
      if (isset($parms['login']) && $parms['login']) $where .= ($where ? " AND " : " WHERE ")."login='".mysql_real_escape_string($parms['login'])."'";
      if (isset($parms['password']) && $parms['password']) $where .= ($where ? " AND " : " WHERE ")."password='".mysql_real_escape_string($parms['password'])."'";
      if (isset($parms['idmd5']) && $parms['idmd5']) $where .= ($where ? " AND " : " WHERE ")."MD5(CONCAT(login, ':', password))='".mysql_real_escape_string($parms['idmd5'])."'";
      if (isset($parms['like']) && $parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(fio, ' ', login, ' ', password) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   function limitString($parms) { if (!isset($parms['limit'])) $parms['limit'] = 0; if (!isset($parms['offset'])) $parms['offset'] = 0; $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   function GetCountRows($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM admins".Admins::whereString($parms));
   }

   function GetRows($parms = array(), $limit = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT * FROM admins".
                             Admins::whereString($parms)."
                             ORDER BY fio".Admins::limitString($limit), 'id');
   }

   function GetRow($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectRow("SELECT * FROM admins".Admins::whereString($parms));
   }
}
?>