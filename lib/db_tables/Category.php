<?php
class Category {
   function Category() { }
   function Structure($init = false) {
      if ($init) {
         $form = array(
            'is_active' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Активность'))),
            'in_menu' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'В&nbsp;меню'))),
            'ord' => array('default' => 0, 'form' => array('Input' => array('name' => 'Сортировка', 'style' => 'size="4"'))),
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Наименование', 'style' => 'style="width: 100%;"'))),
            'href' => array('default' => '', 'form' => array('Input' => array('name' => 'URL', 'style' => 'style="width: 100%;"'))),
            'picture' => array('default' => '', 'form' => array('Upload' => array('text' => true, 'path' => 'data/image/catalog' ,'name' => 'Картинка', 'style' => 'style="width: 40%;"'))),
         );
         $columns = array(
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
            'in_menu' => array('name' => 'В&nbsp;меню', 'autoUpdate' => true),
            'ord' => array('name' => 'Сортировка', 'autoUpdate' => true, 'sort' => true, 'order' => true),
            'picture' => array('name' => 'Картинка', 'function' => 'getImageStr'),
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
         'table'  => 'category',
         'tableName'  => 'Категории услуг'
      );
   }
   function Get($id = '', $init = false) { return new Table(Category::Structure($init), $id); }
   function Update($rows) { $t = new Table(Category::Structure()); return $t->UpdateRows($rows); }
   function Save($rows) { $t = new Table(Category::Structure()); return $t->SaveRows($rows); }
   function Delete($rows) { $t = new Table(Category::Structure()); return $t->DeleteRows($rows); }

   static function whereString($parms) {
      $where = '';
      if (isset($parms['id']) && $parms['id']) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if (isset($parms['lang']) && $parms['lang']) $where .= ($where ? " AND " : " WHERE ")."lang='".mysql_real_escape_string($parms['lang'])."'";
      if (isset($parms['active'])) $where .= ($where ? " AND " : " WHERE ")."is_active='".(int)$parms['active']."'";
      if (isset($parms['in_menu'])) $where .= ($where ? " AND " : " WHERE ")."in_menu='".(int)$parms['in_menu']."'";
      if (isset($parms['href'])) $where .= ($where ? " AND " : " WHERE ")."href='".mysql_real_escape_string($parms['href'])."'";
      if (isset($parms['like']) && $parms['like']) $where .= ($where ? " AND " : " WHERE ")." `name` LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   static function limitString($parms) { if (!isset($parms['limit'])) $parms['limit'] = 0; if (!isset($parms['offset'])) $parms['offset'] = 0; $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   static function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY `ord` ASC, `name` ASC, `id` DESC';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v).($k <> 'id' ? ', id '.strtoupper($v) : '');
   }

   static function GetCountRows($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM `category` ".Category::whereString($parms));
   }

   static function GetRows($parms = array(), $limit = array(), $order = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT * FROM `category` ".
                             Category::whereString($parms).Category::orderString($order).
                             Category::limitString($limit), 'id');
   }

   static function GetList($parms = array(), $limit = array(), $order = array('name' => 'ASC')) {
      $db = Site::GetDB();
      $itemsList = $db->SelectSet("SELECT `id`, `name` FROM `category` ".
                             Category::whereString($parms).Category::orderString($order).
                             Category::limitString($limit), 'id');
      $result = array(
        0 => '[Нет]',
      );
      foreach( $itemsList as $item ) {
        $result[ $item['id'] ] = $item['name'];
      }
      return $result;
   }

   static function GetRow($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectRow("SELECT * FROM `category` ".Category::whereString($parms));
   }

   static function GetIds($parms = array(), $limit = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT id FROM `category` ".Category::whereString($parms).' ORDER BY `ord ASC, `name` ASC, `id` DESC '.Category::limitString($limit), 'id');
   }
}
?>