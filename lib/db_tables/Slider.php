<?php
class Slider {
   function Slider() { }
   function Structure($init = false) {
      if ($init) {
         $form = array(
            'ord' => array('default' => 0, 'form' => array('Input' => array('name' => 'Сортировка', 'style' => 'size="4"'))),
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Наименование', 'style' => 'style="width: 100%;"'))),
            'href' => array('default' => '', 'form' => array('Input' => array('name' => 'Ссылка', 'style' => 'style="width: 100%;"'))),
            'picture' => array('default' => '', 'form' => array('Upload' => array('text' => true, 'path' => 'data/image/pages', 'name' => 'Картинка', 'style' => 'style="width: 40%;"'))),
            'show_text' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Отображать&nbsp;текст'))),
            'text' => array('default' => '', 'form' => array('FCKeditor' => array('ToolbarSet' => '__set2__', 'Height' => 500, 'name' => 'Содержание')))
         );
         $columns = array(
            'ord' => array('name' => 'Сортировка', 'autoUpdate' => true, 'sort' => true, 'order' => true),
            'show_text' => array('name' => 'Отображать текст', 'autoUpdate' => true),
            'picture' => array('name' => 'Картинка', 'function' => 'getPictureStr'),
            'name' => array('name' => 'Наименование', 'style' => 'width="33%" align="left"', 'sort' => true),
            'text' => array('name' => 'Текст', 'style' => 'width="33%" align="left"', 'sort' => true),
            'href' => array('name' => 'Ссылка', 'style' => 'width="33%" align="left"', 'sort' => true),
         );
         if (defined('LANG')) {
            $form = array_merge(array('lang' => array('default' => 'ru', 'form' => array('Select' => array('items' => Site::GetLangs(), 'name' => 'Язык')))), $form);
            $columns = array_merge(array('lang' => array('name' => 'Язык')), $columns);
         }
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'slider',
         'tableName'  => 'Слайдер'
      );
   }
   function Get($id = '', $init = false) { return new Table(Slider::Structure($init), $id); }
   function Update($rows) { $t = new Table(Slider::Structure()); return $t->UpdateRows($rows); }
   function Save($rows) { $t = new Table(Slider::Structure()); return $t->SaveRows($rows); }
   function Delete($rows) { $t = new Table(Slider::Structure()); return $t->DeleteRows($rows); }

   static function whereString($parms) {
      $where = '';
      if (isset($parms['id']) && $parms['id']) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if (isset($parms['lang']) && $parms['lang']) $where .= ($where ? " AND " : " WHERE ")."lang='".mysql_real_escape_string($parms['lang'])."'";
      if (isset($parms['like']) && $parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(text, ' ', name, ' ', announce, ' ', href) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   static function limitString($parms) { if (!isset($parms['limit'])) $parms['limit'] = 0; if (!isset($parms['offset'])) $parms['offset'] = 0; $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   static function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY ord ASC ';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v);
   }

   static function GetCountRows($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM slider".Slider::whereString($parms));
   }

   static function GetRows($parms = array(), $limit = array(), $order = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT * FROM slider".
                             Slider::whereString($parms).Slider::orderString($order).
                             Slider::limitString($limit), 'id');
   }

   static function GetRow($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectRow("SELECT * FROM slider".Slider::whereString($parms));
   }

   static function GetIds($parms = array(), $limit = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT id FROM slider".Slider::whereString($parms)." ORDER BY ord ASC ".Slider::limitString($limit), 'id');
   }
}
?>
