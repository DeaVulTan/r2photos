<?php
class Configs {
   function Configs() { }
   function Structure($init = false) {
      if ($init) {
         $form = array(
            'parts_id' => array('default' => (int)Site::GetSession("configs-parts_id"), 'form' => array('Select' => array('items' => Configs::GetParts(), 'name' => 'Раздел'))),
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Наименование', 'style' => 'style="width: 100%;"'))),
            'description' => array('default' => '', 'form' => array('Input' => array('name' => 'Описание', 'style' => 'style="width: 100%;"'))),
            'value' => array('default' => '', 'form' => array('TextArea' => array('name' => 'Значение', 'style' => 'style="width: 100%;" rows="20" cols="100"'))),
         );
         $columns = array(
            'name' => array('name' => 'Наименование', 'style' => 'align="left"', 'sort' => true),
            'description' => array('name' => 'Описание', 'style' => 'width="100%" align="left"', 'sort' => true),
         );
         if (defined('LANG')) {
            $tmp = array('all' => 'Для всех языков') + Site::GetLangs();
            $form = array_merge(array('lang' => array('default' => 'ru', 'form' => array('Select' => array('items' => $tmp, 'name' => 'Язык')))), $form);
            $columns = array_merge(array('lang' => array('name' => 'Язык')), $columns);
         }
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'config',
         'tableName'  => 'Настройки'
      );
   }
   function Get($id = '', $init = false) { return new Table(Configs::Structure($init), $id); }
   function Update($rows) { $t = new Table(Configs::Structure()); return $t->UpdateRows($rows); }
   function Save($rows) { $t = new Table(Configs::Structure()); return $t->SaveRows($rows); }
   function Delete($rows) { $t = new Table(Configs::Structure()); return $t->DeleteRows($rows); }

   static function whereString($parms) {
      $where = '';
      if (isset($parms['id']) && $parms['id'] > 0) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if (isset($parms['name']) && $parms['name']) $where .= ($where ? " AND " : " WHERE ")."name='".mysql_real_escape_string($parms['name'])."'";
      if (isset($parms['only_robots']) && $parms['only_robots']) $where .= ($where ? " AND " : " WHERE ")."name='robots_txt'";
      if (isset($parms['not_robots']) && $parms['not_robots']) $where .= ($where ? " AND " : " WHERE ")."name<>'robots_txt'";
      if (isset($parms['only_counters']) && $parms['only_counters']) $where .= ($where ? " AND " : " WHERE ")."name='counters_txt'";
      if (isset($parms['not_counters']) && $parms['not_counters']) $where .= ($where ? " AND " : " WHERE ")."name<>'counters_txt'";
      if (isset($parms['lang']) && $parms['lang']) $where .= ($where ? " AND " : " WHERE ")."(lang='".mysql_real_escape_string($parms['lang'])."'".($parms['lang'] <> 'all' ? " OR lang='all'" : '').")";
      if (isset($parms['parts_id']) && $parms['parts_id'] > 0) $where .= ($where ? " AND " : " WHERE ")."parts_id='".$parms['parts_id']."'";
      if (isset($parms['like']) && $parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(name, ' ', description, ' ', value) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   static function limitString($parms) { if (!isset($parms['limit'])) $parms['limit'] = 0; if (!isset($parms['offset'])) $parms['offset'] = 0; $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   static function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY name';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v);
   }

   static function GetParts() {
      return array(
          0 => 'Все',
          1 => 'Общие переменные и настройки',
          2 => 'Темы и тексты почтовых сообщений',
          3 => 'Различные E-mail\'ы',
          4 => 'Текстовая информация',
          5 => '"Ответы" сайта пользователю'
      );
   }

   static function GetValue($name) {
      $db = Site::GetDB();
      return trim($db->SelectValue("SELECT value FROM config WHERE name='".mysql_real_escape_string($name)."'".(defined("LANG") ? " AND (lang='".mysql_real_escape_string(LANG)."' OR lang='all')" : '')));
   }

   static function GetCountRows($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM config".Configs::whereString($parms));
   }

   static function GetRows($parms = array(), $limit = array(), $order = array()) {
      $db = Site::GetDB();
      return $db->SelectSet("SELECT * FROM config".
                             Configs::whereString($parms).Configs::orderString($order).
                             Configs::limitString($limit), 'id');
   }
}
?>