<?php
include_once(Site::GetParms('tablesPath')."Certificatesphotos.php");
include_once(Site::GetParms('tablesPath')."Certificatestypes.php");
class Certificates {
   function Certificates() { }
   function Structure($init = false) {
      if ($init) {
         $form = array(
            'is_active' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Активность'))),
            'ord' => array('default' => 0, 'form' => array('Input' => array('name' => 'Сортировка', 'style' => 'size="4"'))),
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Наименование', 'style' => 'style="width: 100%;"'))),
            'name_full' => array('default' => '', 'form' => array('Input' => array('name' => 'Полное&nbsp;наименование', 'style' => 'style="width: 100%;"'))),
            'price_min' => array('default' => '', 'form' => array('Input' => array('name' => 'Стоимость&nbsp;(минимальная)', 'style' => 'style="width: 100px;"'))),
            'price_max' => array('default' => '', 'form' => array('Input' => array('name' => 'Стоимость&nbsp;(максимальная)', 'style' => 'style="width: 100px;"'))),
            'picture' => array('default' => '', 'form' => array('Upload' => array('text' => true, 'path' => 'data/image/certificates','name' => 'Картинка', 'style' => 'style="width: 40%;"'))),
            'announce' => array('default' => '', 'form' => array('FCKeditor' => array('ToolbarSet' => '__set2__', 'Height' => 300, 'name' => 'Краткое&nbsp;описание'))),
            'text' => array('default' => Utils::GetDefaultCertificateDescription(), 'form' => array('FCKeditor' => array('ToolbarSet' => '__set2__', 'Height' => 500, 'name' => 'Описание&nbsp;видов&nbsp;сертификатов'))),
         );
         $columns = array(
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
            'ord' => array('name' => 'Сортировка', 'autoUpdate' => true, 'sort' => true, 'order' => true),
            'name' => array('name' => 'Наименование', 'style' => 'width="50%" align="left"', 'sort' => true),
            'picture' => array('name' => 'Картинка', 'style' => ' align="center"', 'function' => 'getImageStr'),
            'announce' => array('name' => 'Краткое описание', 'style' => 'width="50%" align="left"', 'sort' => true),
            '_types' => array('name' => 'Типы', 'style' => ' nowrap ', 'function' => 'getTypesStr'),
            '_photos' => array('name' => 'Фотографии', 'style' => ' nowrap ', 'function' => 'getPhotosStr'),
            //'_items' => array('name' => 'Фотосессии', 'function' => 'getItemsStr', 'style' => ' nowrap '),
            //'_locations' => array('name' => 'Локации', 'function' => 'getLocationsStr', 'style' => ' nowrap '),
         );
         if (defined('LANG')) {
            $form = array_merge(array('lang' => array('default' => 'ru', 'form' => array('Select' => array('items' => Site::GetLangs(), 'name' => 'Язык')))), $form);
            $columns = array_merge(array('lang' => array('name' => 'Язык')), $columns);
         }
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'certificates',
         'tableName'  => 'Сертификаты'
      );
   }
   function Get($id = '', $init = false) { return new Table(Certificates::Structure($init), $id); }
   function Update($rows) { $t = new Table(Certificates::Structure()); return $t->UpdateRows($rows); }
   function Save($rows) { $t = new Table(Certificates::Structure()); return $t->SaveRows($rows); }
   function Delete($rows) { $t = new Table(Certificates::Structure()); return $t->DeleteRows($rows); }

   static function whereString($parms) {
      $where = '';
      if (isset($parms['id']) && $parms['id']) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if (isset($parms['lang']) && $parms['lang']) $where .= ($where ? " AND " : " WHERE ")."lang='".mysql_real_escape_string($parms['lang'])."'";
      if (isset($parms['active'])) $where .= ($where ? " AND " : " WHERE ")."is_active='".(int)$parms['active']."'";
      if (isset($parms['like']) && $parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(`text`, ' ', `name`, ' ', `announce`) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   static function limitString($parms) { if (!isset($parms['limit'])) $parms['limit'] = 0; if (!isset($parms['offset'])) $parms['offset'] = 0; $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   static function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY `ord` ASC, `id` ASC ';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v).($k <> 'id' ? ', id '.strtoupper($v) : '');
   }

   static function GetCountRows($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM `certificates` ".Certificates::whereString($parms));
   }

   static function GetRows($parms = array(), $limit = array(), $order = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT * FROM `certificates` ".
                             Certificates::whereString($parms).Certificates::orderString($order).
                             Certificates::limitString($limit), 'id');
   }

   static function GetRow($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectRow("SELECT * FROM `certificates` ".Certificates::whereString($parms));
   }

   static function GetIds($parms = array(), $limit = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT `id` FROM `certificates` ".Certificates::whereString($parms).' ORDER BY `ord` ASC, `id` ASC '.Certificates::limitString($limit), 'id');
   }
}
?>