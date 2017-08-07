<?php
include_once(Site::GetParms('tablesPath')."Bannersplaces.php");
class Banners {
   function Banners() { }
   function Structure($init = false) {
      if ($init) {
         $form = array(
            'is_active' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Активность'))),
            'place_id' => array('default' => (int)Site::GetSession("banners-place_id"), 'form' => array('Select' => array('items' => Bannersplaces::GetRows(), 'field' => 'name', 'name' => 'Место'))),
            'width' => array('default' => 0, 'form' => array('Input' => array('name' => 'Ширина', 'style' => 'size="5"'))),
            'height' => array('default' => 0, 'form' => array('Input' => array('name' => 'Высота', 'style' => 'size="5"'))),
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Наименование&nbsp;(alt)', 'style' => 'style="width: 100%;"'))),
            'file' => array('default' => '', 'form' => array('Upload' => array('text' => true, 'path' => 'data/image/pictures', 'name' => 'Картинка', 'style' => 'style="width: 40%;"'))),
            'is_flash' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Это флеш-ролик'))),
            'file_door_flash' => array('default' => '', 'form' => array('Upload' => array('text' => true, 'path' => 'data/image/pictures', 'name' => 'Заглушка для флеш', 'style' => 'style="width: 40%;"'))),
            'url' => array('default' => '', 'form' => array('Input' => array('name' => 'URL', 'style' => 'style="width: 100%;"'))),
            'is_blank' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'В отдельном окне'))),
            'text' => array('default' => '', 'form' => array('FCKeditor' => array('ToolbarSet' => '__set2__', 'Height' => 500, 'name' => 'Текстовый банер'))),
            'idate_from' => array('default' => 0, 'form' => array('Calendar' => array('name' => 'Дата&nbsp;показа&nbsp;с', 'style' => 'size="10"'))),
            'idate_to' => array('default' => 0, 'form' => array('Calendar' => array('name' => 'Дата&nbsp;показа&nbsp;по', 'style' => 'size="10"'))),
            'time_from' => array('default' => 0, 'form' => array('Select' => array('name' => 'Время&nbsp;показа&nbsp;с', 'items' => Banners::GetTimes()))),
            'time_to' => array('default' => 0, 'form' => array('Select' => array('name' => 'Время&nbsp;показа&nbsp;по', 'items' => Banners::GetTimes()))),
            'show_count' => array('default' => 0, 'form' => array('Input' => array('name' => 'Кол-во показов', 'style' => 'style="width: 20%;"'))),
            'click_count' => array('default' => 0, 'form' => array('Input' => array('name' => 'Кол-во кликов', 'style' => 'style="width: 20%;"'))),
         );
         $columns = array(
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
            'is_blank' => array('name' => 'В отдельном окне', 'autoUpdate' => true),
            'file' => array('name' => 'Картинка', 'function' => 'getPictureStr'),
            'name' => array('name' => 'Наименование', 'style' => 'width="100%" align="left"', 'sort' => true),
            'width' => array('name' => 'Ширина'),
            'height' => array('name' => 'Высота'),
            'all_shows' => array('name' => 'Показов'),
            'all_clicks' => array('name' => 'Кликов'),
            'column3' => array('name' => 'CTR,&nbsp;%', 'function' => 'getCtrStr'),
            'column4' => array('name' => '&nbsp;', 'function' => 'getClearStr'),
         );
         if (defined('LANG')) {
            $form = array_merge(array('lang' => array('default' => 'ru', 'form' => array('Select' => array('items' => Site::GetLangs(), 'name' => 'Язык')))), $form);
            $columns = array_merge(array('lang' => array('name' => 'Язык')), $columns);
         }
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'banners',
         'tableName'  => 'Банеры'
      );
   }
   function Get($id = '', $init = false) { return new Table(Banners::Structure($init), $id); }
   function Update($rows) { $t = new Table(Banners::Structure()); return $t->UpdateRows($rows); }
   function Save($rows) { $t = new Table(Banners::Structure()); return $t->SaveRows($rows); }
   function Delete($rows) { $t = new Table(Banners::Structure()); return $t->DeleteRows($rows); }

   static function GetTimes() {
      $ret = array();
      for ($i = 0; $i <= 23; $i ++) $ret[$i] = $i;
      return $ret;
   }

   static function whereString($parms) {
      $where = '';
      if (isset($parms['active'])) $where .= ($where ? " AND " : " WHERE ")."is_active='".(int)$parms['active']."'";
      if (isset($parms['place_id']) && $parms['place_id'] > 0) $where .= ($where ? " AND " : " WHERE ")."place_id='".(int)$parms['place_id']."'";
      if (isset($parms['id']) && $parms['id'] > 0) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if (isset($parms['lang']) && $parms['lang']) $where .= ($where ? " AND " : " WHERE ")."lang='".mysql_real_escape_string($parms['lang'])."'";
      if (isset($parms['check_date'])) $where .= ($where ? " AND " : " WHERE ")."((idate_from=0 AND idate_to=0) OR (idate_to=0 AND idate_from>0 AND DATE_FORMAT(FROM_UNIXTIME(idate_from), '%Y%m%d')<='".date("Ymd")."') OR (idate_from=0 AND idate_to>0 AND DATE_FORMAT(FROM_UNIXTIME(idate_to), '%Y%m%d')>='".date("Ymd")."') OR (idate_from>0 AND DATE_FORMAT(FROM_UNIXTIME(idate_from), '%Y%m%d')<='".date("Ymd")."' AND idate_to>0 AND DATE_FORMAT(FROM_UNIXTIME(idate_to), '%Y%m%d')>='".date("Ymd")."'))";
      if (isset($parms['check_time'])) $where .= ($where ? " AND " : " WHERE ")."((time_from=0 AND time_to=0) OR (time_to=0 AND time_from>0 AND time_from<='".date("G")."') OR (time_from=0 AND time_to>0 AND time_to>='".date("G")."') OR (time_from>0 AND time_from<='".date("G")."' AND time_to>0 AND time_to>='".date("G")."'))";
      if (isset($parms['check_days'])) $where .= ($where ? " AND " : " WHERE ")."(days_show='' OR (days_show<>'' AND days_show LIKE '%|".date("w")."|%'))";
      if (isset($parms['check_shows'])) $where .= ($where ? " AND " : " WHERE ")."(show_count=0 OR (show_count>0 AND show_count>=all_shows))";
      if (isset($parms['check_clicks'])) $where .= ($where ? " AND " : " WHERE ")."(click_count=0 OR (click_count>0 AND click_count>=all_clicks))";
      if (isset($parms['like']) && $parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(name, ' ', file, ' ', url, ' ', file_door_flash) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   static function limitString($parms) { if (!isset($parms['limit'])) $parms['limit'] = 0; if (!isset($parms['offset'])) $parms['offset'] = 0; $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   static function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY id';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v);
   }

   static function GetCountRows($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM banners".Banners::whereString($parms));
   }

   static function GetRows($parms = array(), $limit = array(), $order = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT * FROM banners".Banners::whereString($parms).Banners::orderString($order).Banners::limitString($limit), 'id');
   }

   static function GetRandom($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectRow("SELECT * FROM banners".Banners::whereString($parms)." ORDER BY MD5(RAND()*NOW()) LIMIT 1");
   }

   static function GetRow($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectRow("SELECT * FROM banners".Banners::whereString($parms));
   }

   static function Statistic($banners = array()) {
      if (sizeOf($banners) > 0) {
         $db = Site::GetDB();
         $whereBID = (sizeOf($banners) == 1 ? "='".$banners[0]."'" : " IN (".mysql_real_escape_string(implode(",", $banners)).")");
         $db->Query("UPDATE banners SET all_shows=1+all_shows WHERE id".$whereBID);
         $upd = $db->SelectSet("SELECT id, banner_id FROM banner_stat WHERE sdate='".date('Ymd')."' AND banner_id".$whereBID, 'id');
         if (sizeOf($upd) > 0) {
            $idsUpd = array_keys($upd);
            $whereBIDUP = (sizeOf($idsUpd) == 1 ? "='".$idsUpd[0]."'" : " IN (".mysql_real_escape_string(implode(",", $idsUpd)).")");
            $db->Query("UPDATE banner_stat SET shows=1+shows WHERE id".$whereBIDUP);
            foreach ($upd as $k => $v) $updDif[] = $v['banner_id'];
            $idsIns = array_diff($banners, $updDif);
         }
         else $idsIns = $banners;
         if (sizeOf($idsIns) > 0) {
            $ins = array();
            foreach ($idsIns as $k => $v) $ins[] = "(0, '".(int)$v."', '".date('Ymd')."', '1', '0')";
            $db->Query("INSERT INTO banner_stat (id, banner_id, sdate, shows, clicks) VALUES ".implode(", ", $ins));
         }
      }
   }
}
?>
