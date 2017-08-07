<?php
class Users {
   function Users() { }
   function &Structure($init = false) {
      if ($init) {
         $form = array(
            'is_active' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Активность'))),
            'idate' => array('default' => time(), 'form' => array('Calendar' => array('name' => 'Дата', 'style' => 'size="10"'))),
            'email' => array('default' => '', 'form' => array('Input' => array('regexp' => '/^[0-9A-Za-z._-]+@([0-9a-z_-]+\.)+[a-z]{2,4}$/', 'alert' => 'Укажите правильный E-mail!', 'name' => 'E-mail (логин)', 'style' => 'style="width: 100%;"'))),
            //'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Организация', 'style' => 'style="width: 100%;"'))),
            'fio' => array('default' => '', 'form' => array('Input' => array('regexp' => '/(.+)/', 'alert' => 'Укажите ФИО!', 'name' => 'ФИО', 'style' => 'style="width: 100%;"'))),
            //'address' => array('default' => '', 'form' => array('Input' => array('name' => 'Адрес', 'style' => 'style="width: 100%;"'))),
            'phones' => array('default' => '', 'form' => array('Input' => array('name' => 'Телефоны', 'style' => 'style="width: 100%;"'))),
            'birthday' => array('default' => '', 'form' => array('Input' => array('name' => 'День рождения', 'style' => 'style="width: 100%;"'))),
            //'nikname' => array('default' => '', 'form' => array('Input' => array('name' => 'Ник для форума', 'style' => 'style="width: 100%;"'))),
            //'avatar' => array('default' => '', 'form' => array('Upload' => array('text' => true, 'path' => 'data/image/users', 'name' => 'Аватар', 'style' => 'style="width: 40%;"'))),
            //'sig' => array('default' => '', 'form' => array('TextArea' => array('name' => 'Подпись для форума', 'style' => 'rows="5" cols="100" style="width: 100%;"'))),
            //'vk_id' => array('default' => '', 'form' => array('Input' => array('name' => 'ID ВКонтакте', 'style' => 'style="width: 100%;"'))),
            //'fb_id' => array('default' => '', 'form' => array('Input' => array('name' => 'ID Facebook', 'style' => 'style="width: 100%;"'))),
            //'tw_id' => array('default' => '', 'form' => array('Input' => array('name' => 'ID Twitter', 'style' => 'style="width: 100%;"'))),
            //'od_id' => array('default' => '', 'form' => array('Input' => array('name' => 'ID Twitter', 'style' => 'style="width: 100%;"'))),
         );
         $columns = array(
            'id' => array('name' => 'ID'),
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
            'idate' => array('name' => 'Дата', 'function' => 'getDataStr', 'sort' => true),
            'email' => array('name' => 'E-mail<br />(логин)', 'sort' => true),
            'name' => array('name' => 'Организация', 'style' => 'width="50%" align="left"', 'sort' => true),
            'fio' => array('name' => 'Контактное лицо', 'style' => 'width="50%" align="left"', 'sort' => true),
            'column1' => array('name' => '&nbsp;', 'function' => 'getSendStr'),
         );
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'users',
         'tableName'  => 'Пользователи'
      );
   }
   function &Get($id = '', $init = false) { return new Table(Users::Structure($init), $id); }
   function Update(&$rows) { $t = new Table(Users::Structure()); return $t->UpdateRows($rows); }
   function Save(&$rows) { $t = new Table(Users::Structure()); return $t->SaveRows($rows); }
   function Delete(&$rows) { $t = new Table(Users::Structure()); return $t->DeleteRows($rows); }

   function whereString($parms) {
      $where = '';
      if ($parms['id']) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if (isset($parms['active'])) $where .= ($where ? " AND " : " WHERE ")."is_active='".(int)$parms['active']."'";
      if ($parms['idmd5']) $where .= ($where ? " AND " : " WHERE ")."password='".mysql_real_escape_string($parms['idmd5'])."'";
      if ($parms['vk_id']) $where .= ($where ? " AND " : " WHERE ")."vk_id='".(mysql_real_escape_string($parms['vk_id']))."'";
      if ($parms['fb_id']) $where .= ($where ? " AND " : " WHERE ")."fb_id='".(mysql_real_escape_string($parms['fb_id']))."'";
      if ($parms['tw_id']) $where .= ($where ? " AND " : " WHERE ")."tw_id='".(mysql_real_escape_string($parms['tw_id']))."'";
      if ($parms['od_id']) $where .= ($where ? " AND " : " WHERE ")."od_id='".(mysql_real_escape_string($parms['od_id']))."'";
      if ($parms['email']) $where .= ($where ? " AND " : " WHERE ")."email='".mysql_real_escape_string($parms['email'])."'";
      if ($parms['nikname']) $where .= ($where ? " AND " : " WHERE ")."nikname='".mysql_real_escape_string($parms['nikname'])."'";
      if ($parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(email, ' ', password, ' ', fio, ' ', birthday, ' ', phones) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   function limitString($parms) { $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY idate DESC, email';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v);
   }

   function &GetCountRows($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM users".Users::whereString($parms));
   }

   function &GetRows($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT * FROM users".
                             Users::whereString($parms).Users::orderString($order).Users::limitString($limit), 'id');
   }

   function &GetRow($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT * FROM users".Users::whereString($parms));
   }

   function DecPosts($id, $count) {
      $db =& Site::GetDB();
      $db->Query("UPDATE users SET posts=posts-".(int)$count." WHERE id=".(int)$id);
   }
}
?>