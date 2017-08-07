<?php
include_once(Site::GetParms('tablesPath')."Votesvars.php");
class Votes {
   function Votes() { }
   function &Structure($init = false) {
      if ($init) {
         $form = array(
            'is_show' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Показ на сайте'))),
            'is_active' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Активность'))),
            'is_checkbox' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Несколько ответов'))),
            'ord' => array('default' => 0, 'form' => array('Input' => array('name' => 'Сортировка', 'style' => 'size="4"'))),
            'question' => array('default' => '', 'form' => array('Input' => array('name' => 'Вопрос', 'style' => 'style="width: 100%;"'))),
            'href' => array('default' => '', 'form' => array('Input' => array('name' => 'URL', 'style' => 'style="width: 100%;"'))),
         );
         $columns = array(
            'is_show' => array('name' => 'Показ на сайте', 'autoUpdate' => true),
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
            'is_checkbox' => array('name' => 'Несколько ответов', 'autoUpdate' => true),
            'ord' => array('name' => 'Сортировка', 'autoUpdate' => true, 'sort' => true, 'order' => true),
            'question' => array('name' => 'Вопрос', 'style' => 'width="100%" align="left"', 'sort' => true),
            'column1' => array('name' => 'Варианты', 'function' => 'getItemsStr', 'style' => 'nowrap="nowrap"'),
            'column2' => array('name' => '&nbsp;', 'function' => 'getClearStr'),
         );
         if (defined('LANG')) {
            $form = array_merge(array('lang' => array('default' => 'ru', 'form' => array('Select' => array('items' => Site::GetLangs(), 'name' => 'Язык')))), $form);
            $columns = array_merge(array('lang' => array('name' => 'Язык')), $columns);
         }
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'polls',
         'tableName'  => 'Голосование'
      );
   }

   function &Get($id = '', $init = false) { return new Table(Votes::Structure($init), $id); }
   function Update(&$rows) { $t = new Table(Votes::Structure()); return $t->UpdateRows($rows, Votes::Structure()); }
   function Save(&$rows) { $t = new Table(Votes::Structure()); return $t->SaveRows($rows, Votes::Structure()); }
   function Delete(&$rows) { $t = new Table(Votes::Structure()); return $t->DeleteRows($rows, Votes::Structure()); }

   function whereString($parms) {
      $where = '';
      if ($parms['id']) $where .= ($where ? " AND " : " WHERE ")."p.id='".(int)$parms['id']."'";
      if (isset($parms['active'])) $where .= ($where ? " AND " : " WHERE ")."p.is_active='".(int)$parms['active']."'";
      if (isset($parms['show'])) $where .= ($where ? " AND " : " WHERE ")."p.is_show='".(int)$parms['show']."'";
      if (isset($parms['href'])) $where .= ($where ? " AND " : " WHERE ")."p.href='".mysql_real_escape_string($parms['href'])."'";
      if ($parms['lang']) $where .= ($where ? " AND " : " WHERE ")."p.lang='".mysql_real_escape_string($parms['lang'])."'";
      if ($parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(p.question, ' ', p.href) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   function limitString($parms) { $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY p.ord';
      else foreach ($parms as $k => $v) return ' ORDER BY p.'.$k.' '.strtoupper($v);
   }

   function &GetCountRows($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT COUNT(p.id) FROM polls p".Votes::whereString($parms));
   }

   function &GetRows($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT p.*, COUNT(pv.id) AS varsCount FROM polls p LEFT JOIN poll_variants pv ON pv.poll_id=p.id".
                             Votes::whereString($parms)." GROUP BY p.id".Votes::orderString($order).
                             Votes::limitString($limit), 'id');
   }

   function &GetRandom($parms) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT p.* FROM polls p".Votes::whereString($parms)." ORDER BY MD5(RAND()*NOW()) LIMIT 1");
   }

   function &GetRow($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT p.* FROM polls p".Votes::whereString($parms));
   }

   function &GetIds($parms = array(), $limit = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT p.id FROM polls p".Votes::whereString($parms).Votes::limitString($limit), 'id');
   }

   function GetMansWord($i) {
      if (preg_match("/1$/", $i) && $i <> 11) return $i." человек";
      elseif (preg_match("/[2|3|4]$/", $i) && !preg_match("/1[2|3|4]$/", $i)) return $i." человека";
      else return $i." человек";
   }
}
?>
