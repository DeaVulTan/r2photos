<?php
class Articles {
   function Articles() { }
   function Structure($init = false) {
      if ($init) {
         $form = array(
            'idate' => array('default' => time(), 'form' => array('Calendar' => array('name' => 'Дата', 'style' => 'size="10"'))),
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Наименование', 'style' => 'style="width: 100%;"'))),
            'href' => array('default' => '', 'form' => array('Input' => array('name' => 'URL', 'style' => 'style="width: 100%;"'))),
            'announce' => array('default' => '', 'form' => array('FCKeditor' => array('ToolbarSet' => '__set2__', 'Height' => 300, 'name' => 'Анонс'))),
            'text' => array('default' => '', 'form' => array('FCKeditor' => array('ToolbarSet' => '__set2__', 'Height' => 500, 'name' => 'Текст'))),
         );
         $columns = array(
            'idate' => array('name' => 'Дата', 'function' => 'getDataStr', 'sort' => true),
            'name' => array('name' => 'Наименование', 'style' => 'width="50%" align="left"', 'sort' => true),
            'announce' => array('name' => 'Анонс', 'style' => 'width="50%" align="left"', 'sort' => true),
         );
         if (defined('LANG')) {
            $form = array_merge(array('lang' => array('default' => 'ru', 'form' => array('Select' => array('items' => Site::GetLangs(), 'name' => 'Язык')))), $form);
            $columns = array_merge(array('lang' => array('name' => 'Язык')), $columns);
         }
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'articles',
         'tableName'  => 'Статьи'
      );
   }
   function Get($id = '', $init = false) { return new Table(Articles::Structure($init), $id); }
   function Update($rows) { $t = new Table(Articles::Structure()); return $t->UpdateRows($rows); }
   function Save($rows) { $t = new Table(Articles::Structure()); return $t->SaveRows($rows); }
   function Delete($rows) { $t = new Table(Articles::Structure()); return $t->DeleteRows($rows); }

   static function whereString($parms) {
      $where = '';
      if (isset($parms['id']) && $parms['id']) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if (isset($parms['lang']) && $parms['lang']) $where .= ($where ? " AND " : " WHERE ")."lang='".mysql_real_escape_string($parms['lang'])."'";
      if (isset($parms['year']) && $parms['year']) $where .= ($where ? " AND " : " WHERE ")."YEAR(from_unixtime(idate))='".(int)$parms['year']."'";
      if (isset($parms['href'])) $where .= ($where ? " AND " : " WHERE ")."href='".mysql_real_escape_string($parms['href'])."'";
      if (isset($parms['like']) && $parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(text, ' ', name, ' ', announce, ' ', href) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   static function limitString($parms) { if (!isset($parms['limit'])) $parms['limit'] = 0; if (!isset($parms['offset'])) $parms['offset'] = 0; $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   static function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY idate DESC, id DESC';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v).($k <> 'id' ? ', id '.strtoupper($v) : '');
   }

   static function GetCountRows($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM articles".Articles::whereString($parms));
   }

   static function GetRows($parms = array(), $limit = array(), $order = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT * FROM articles".
                             Articles::whereString($parms).Articles::orderString($order).
                             Articles::limitString($limit), 'id');
   }

   static function GetRow($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectRow("SELECT * FROM articles".Articles::whereString($parms));
   }

   static function GetIds($parms = array(), $limit = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT id FROM articles".Articles::whereString($parms)." ORDER BY idate DESC, id DESC".Articles::limitString($limit), 'id');
   }
}
?>
