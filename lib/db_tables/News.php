<?php
class News {
   function News() { }
   function Structure($init = false) {
      if ($init) {
         $form = array(
            'is_active' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Активность'))),
            'idate' => array('default' => time(), 'form' => array('Calendar' => array('name' => 'Дата', 'style' => 'size="10"'))),
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Наименование', 'style' => 'style="width: 100%;"'))),
            'href' => array('default' => '', 'form' => array('Input' => array('name' => 'URL', 'style' => 'style="width: 100%;"'))),
            'announce' => array('default' => '', 'form' => array('FCKeditor' => array('ToolbarSet' => '__set2__', 'Height' => 300, 'name' => 'Анонс'))),
            'text' => array('default' => '', 'form' => array('FCKeditor' => array('ToolbarSet' => '__set2__', 'Height' => 500, 'name' => 'Текст'))),
         );
         $columns = array(
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
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
         'table'  => 'news',
         'tableName'  => 'Новости'
      );
   }
   function Get($id = '', $init = false) { return new Table(News::Structure($init), $id); }
   function Update($rows) { $t = new Table(News::Structure()); return $t->UpdateRows($rows); }
   function Save($rows) { $t = new Table(News::Structure()); return $t->SaveRows($rows); }
   function Delete($rows) { $t = new Table(News::Structure()); return $t->DeleteRows($rows); }

   static function whereString($parms) {
      $where = '';
      if (isset($parms['id']) && $parms['id']) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if (isset($parms['lang']) && $parms['lang']) $where .= ($where ? " AND " : " WHERE ")."lang='".mysql_real_escape_string($parms['lang'])."'";
      if (isset($parms['date_from']) && $parms['date_from'] && $parms['date_to']) $where .= ($where ? " AND " : " WHERE ")."(idate>='".(int)$parms['date_from']."' AND idate <='".(int)$parms['date_to']."')";
      if (isset($parms['year']) && $parms['year']) $where .= ($where ? " AND " : " WHERE ")."YEAR(FROM_UNIXTIME(idate))='".(int)$parms['year']."'";
      if (isset($parms['month']) && $parms['month']) $where .= ($where ? " AND " : " WHERE ")."MONTH(FROM_UNIXTIME(idate))='".(int)$parms['month']."'";
      if (isset($parms['day']) && $parms['day']) $where .= ($where ? " AND " : " WHERE ")."DAY(FROM_UNIXTIME(idate))='".(int)$parms['day']."'";
      if (isset($parms['dateYmd']) && $parms['dateYmd']) $where .= ($where ? " AND " : " WHERE ")."DATE_FORMAT(FROM_UNIXTIME(idate), '%Y%m%d')='".mysql_real_escape_string($parms['dateYmd'])."'";
      if (isset($parms['dateYm']) && $parms['dateYm']) $where .= ($where ? " AND " : " WHERE ")."DATE_FORMAT(FROM_UNIXTIME(idate), '%Y%m')='".mysql_real_escape_string($parms['dateYm'])."'";
      if (isset($parms['active'])) $where .= ($where ? " AND " : " WHERE ")."is_active='".(int)$parms['active']."'";
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
      return $db->SelectValue("SELECT COUNT(*) FROM news".News::whereString($parms));
   }

   static function GetRows($parms = array(), $limit = array(), $order = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT * FROM news".
                             News::whereString($parms).News::orderString($order).
                             News::limitString($limit), 'id');
   }

   static function GetRow($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectRow("SELECT * FROM news".News::whereString($parms));
   }

   static function GetIds($parms = array(), $limit = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT id FROM news".News::whereString($parms).' ORDER BY idate DESC, id DESC '.News::limitString($limit), 'id');
   }

   static function GetYears($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT DISTINCT YEAR(FROM_UNIXTIME(idate)) AS year FROM news".News::whereString($parms)." ORDER BY idate DESC", 'year');
   }

   static function GetMaxDate($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectValue("SELECT MAX(idate) FROM news".News::whereString($parms));
   }

   static function GetMinDate($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectValue("SELECT MIN(idate) FROM news".News::whereString($parms));
   }

   static function GetCountCaledar($parms = array()) {
		$db = Site::GetDB();
		$arr = $db->SelectSet("SELECT idate, COUNT(*) AS count_act FROM news".News::whereString($parms)." GROUP BY idate", 'idate');
		foreach ($arr as $k => $v) $res[(int)date("d", $k)] = $v['count_act'];
		return $res;
   }
}
?>
