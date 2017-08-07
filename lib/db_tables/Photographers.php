<?php
include_once(Site::GetParms('tablesPath')."Photoimages.php");
class Photographers {
   function Photographers() { }
   function Structure($init = false) {
      if ($init) {
         $form = array(
            'is_active' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Активность'))),
            'in_slider' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'В&nbsp;слайдере на&nbsp;главной&nbsp;странице'))),
            'ord' => array('default' => 0, 'form' => array('Input' => array('name' => 'Сортировка', 'style' => 'size="4"'))),
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'ФИО', 'style' => 'style="width: 100%;"'))),
            'picture' => array('default' => '', 'form' => array('Upload' => array('text' => true, 'path' => 'data/image/photographers','name' => 'Фотография', 'style' => 'style="width: 40%;"'))),
            'announce' => array('default' => '', 'form' => array('FCKeditor' => array('ToolbarSet' => '__set2__', 'Height' => 300, 'name' => 'Краткое описание'))),
            'text' => array('default' => '', 'form' => array('FCKeditor' => array('ToolbarSet' => '__set2__', 'Height' => 500, 'name' => 'Полное описание'))),
         );
         $columns = array(
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
            'in_slider' => array('name' => 'В слайдере', 'autoUpdate' => true),
            'ord' => array('name' => 'Сортировка', 'autoUpdate' => true, 'sort' => true, 'order' => true),
            'name' => array('name' => 'Наименование', 'style' => 'width="50%" align="left"', 'sort' => true),
            'announce' => array('name' => 'Краткое описание', 'style' => 'width="50%" align="left"', 'sort' => true),
            '_items' => array('name' => 'Фотосессии', 'function' => 'getItemsStr', 'style' => ' nowrap '),
            '_photos' => array('name' => 'Фотографии', 'function' => 'getPhotosStr', 'style' => ' nowrap '),
            '_works' => array('name' => 'Виды работ', 'function' => 'getWorksStr', 'style' => ' nowrap '),
         );
         if (defined('LANG')) {
            $form = array_merge(array('lang' => array('default' => 'ru', 'form' => array('Select' => array('items' => Site::GetLangs(), 'name' => 'Язык')))), $form);
            $columns = array_merge(array('lang' => array('name' => 'Язык')), $columns);
         }
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'photographers',
         'tableName'  => 'Фотографы'
      );
   }
   function Get($id = '', $init = false) { return new Table(Photographers::Structure($init), $id); }
   function Update($rows) { $t = new Table(Photographers::Structure()); return $t->UpdateRows($rows); }
   function Save($rows) { $t = new Table(Photographers::Structure()); return $t->SaveRows($rows); }
   function Delete($rows) { $t = new Table(Photographers::Structure()); return $t->DeleteRows($rows); }

   static function whereString($parms) {
      $where = '';
      if (isset($parms['id']) && $parms['id']) $where .= ($where ? " AND " : " WHERE ")."`id`='".(int)$parms['id']."'";
      if (!empty($parms['id_in'])) $where .= ($where ? " AND " : " WHERE ")."`id` IN (".( mysql_real_escape_string( $parms['id_in'] ) ).")";
      if (isset($parms['lang']) && $parms['lang']) $where .= ($where ? " AND " : " WHERE ")."`lang`='".mysql_real_escape_string($parms['lang'])."'";
      if (isset($parms['active'])) $where .= ($where ? " AND " : " WHERE ")."`is_active`='".(int)$parms['active']."'";
      if (isset($parms['in_slider'])) $where .= ($where ? " AND " : " WHERE ")."`in_slider`='".(int)$parms['in_slider']."'";
      if (isset($parms['href'])) $where .= ($where ? " AND " : " WHERE ")."`href`='".mysql_real_escape_string($parms['href'])."'";
      if (isset($parms['like']) && $parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(`name`, ' ', `text`, ' ', `announce`, ' ', `href`) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   static function limitString($parms) { if (!isset($parms['limit'])) $parms['limit'] = 0; if (!isset($parms['offset'])) $parms['offset'] = 0; $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   static function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY `ord` ASC, `name` ASC ';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v).($k <> 'id' ? ', id '.strtoupper($v) : '');
   }

   static function GetCountRows($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM `photographers` ".Photographers::whereString($parms));
   }

   static function GetRows($parms = array(), $limit = array(), $order = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT * FROM `photographers` ".
                             Photographers::whereString($parms).Photographers::orderString($order).
                             Photographers::limitString($limit), 'id');
   }

   static function GetNames($parms = array(), $limit = array(), $order = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT `id`, `name` FROM `photographers` ".
                             Photographers::whereString($parms).Photographers::orderString($order).
                             Photographers::limitString($limit), 'id');
   }

   static function GetRow($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectRow("SELECT * FROM `photographers` ".Photographers::whereString($parms));
   }

   static function GetIds($parms = array(), $limit = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT id FROM `photographers` ".Photographers::whereString($parms).' ORDER BY `ord` ASC, `name` ASC'.Photographers::limitString($limit), 'id');
   }
}
?>