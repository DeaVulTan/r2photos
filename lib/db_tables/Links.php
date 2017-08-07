<?php
class Links {
   function Links() { }
   function Structure($init = false) {
      if ($init) {
         $form = array(
            'is_active' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Активность'))),
            'url_regexp' => array('default' => '', 'form' => array('Input' => array('name' => 'Шаблон URL', 'style' => 'style="width: 100%;"'))),
            'name' => array('default' => '', 'form' => array('TextArea' => array('name' => 'Текст', 'style' => 'style="width: 100%;" rows="10" cols="100"'))),
         );
         $columns = array(
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
            'url_regexp' => array('name' => 'Шаблон&nbsp;URL', 'style' => 'align="left"', 'addstyle' => ' class="field" size="20"', 'autoUpdate' => true, 'sort' => true),
            'name' => array('name' => 'Ссылки', 'style' => 'width="100%" align="left"', 'addstyle' => ' rows="3" cols="100" style="width: 100%;"', 'autoUpdate' => true, 'sort' => true),
         );
         if (defined('LANG')) {
            $form = array_merge(array('lang' => array('default' => 'ru', 'form' => array('Select' => array('items' => Site::GetLangs(), 'name' => 'Язык')))), $form);
            $columns = array_merge(array('lang' => array('name' => 'Язык')), $columns);
         }
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'links',
         'tableName'  => 'Ссылки (реклама)'
      );
   }
   function Get($id = '', $init = false) { return new Table(Links::Structure($init), $id); }
   function Update($rows) { $t = new Table(Links::Structure()); return $t->UpdateRows($rows); }
   function Save($rows) { $t = new Table(Links::Structure()); return $t->SaveRows($rows); }
   function Delete($rows) { $t = new Table(Links::Structure()); return $t->DeleteRows($rows); }

   static function whereString($parms) {
      $where = '';
      if (isset($parms['active']) && $parms['active'] == 1)  $where .= ($where ? " AND " : " WHERE ").'is_active=1';
      if (isset($parms['id']) && $parms['id'] > 0) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if (isset($parms['lang']) && $parms['lang']) $where .= ($where ? " AND " : " WHERE ")."lang='".mysql_real_escape_string($parms['lang'])."'";
      if (isset($parms['regexp'])) $where .= ($where ? " AND " : " WHERE ")."'".mysql_real_escape_string($parms['regexp'])."' REGEXP url_regexp";
      if (isset($parms['like']) && $parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(url_regexp, ' ', name) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   static function limitString($parms) { if (!isset($parms['limit'])) $parms['limit'] = 0; if (!isset($parms['offset'])) $parms['offset'] = 0; $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   static function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY url_regexp';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v);
   }

   static function GetCountRows($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM links".Links::whereString($parms));
   }

   static function GetRows($parms = array(), $limit = array(), $order = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT * FROM links".
                             Links::whereString($parms).Links::orderString($order).
                             Links::limitString($limit), 'id');
   }

   static function GetRow($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectRow("SELECT * FROM links".Links::whereString($parms));
   }

   static function GetDefault() {
      $db = Site::GetDB();
      return trim($db->SelectValue("SELECT name FROM links WHERE url_regexp='default' AND is_active=1".(defined("LANG") ? " AND lang='".mysql_real_escape_string(LANG)."'" : '')));
   }

   static function GetLinks($parms = array()) {
      $links = Links::GetRow($parms);
      if ($links['id'] > 0) return trim($links['name']);
      return Links::GetDefault();
   }

   static function GetLinksCheck($parms = array()) {
      $links = Links::GetRow($parms);
      if ($links['id'] > 0) return true;
      else return false;
   }
}
?>