<?php
class Langs{
   function Langs() { }
   function &Structure($init = false) {
      if ($init) {
         $form = array(
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Код', 'style' => 'style="width: 100%;"'))),
            'description' => array('default' => '', 'form' => array('Input' => array('name' => 'Описание', 'style' => 'style="width: 100%;"'))),
            'value_ru' => array('default' => '', 'form' => array('Input' => array('name' => 'Значение (рус.)', 'style' => 'style="width: 100%;"'))),
            'value_en' => array('default' => '', 'form' => array('Input' => array('name' => 'Значение (eng.)', 'style' => 'style="width: 100%;"'))),
         );
         $columns = array(
            'name' => array('name' => 'Код', 'sort' => true),
            'description' => array('name' => 'Описание', 'style' => 'width="30%" align="left"'),
            'value_ru' => array('name' => 'Значение (рус.)', 'style' => 'width="30%" align="left"'),
            'value_en' => array('name' => 'Значение (eng.)', 'style' => 'width="30%" align="left"'),
         );
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'langs',
         'tableName'  => 'Перевод фраз'
      );
   }

   function &Get($id = '', $init = false) { return new Table(Langs::Structure($init), $id); }
   function Update(&$rows) { $t = new Table(Langs::Structure()); return $t->UpdateRows($rows); }
   function Save(&$rows) { $t = new Table(Langs::Structure()); return $t->SaveRows($rows); }
   function Delete(&$rows) { $t = new Table(Langs::Structure()); return $t->DeleteRows($rows); }

   function whereString($parms) {
      $where = '';
      if ($parms['id'] > 0) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if ($parms['name']) $where .= ($where ? " AND " : " WHERE ")."name='".mysql_real_escape_string($parms['name'])."'";
      if ($parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(name, ' ', description, ' ', value_ru, ' ', value_en) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   function limitString($parms) { $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY name';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v);
   }

   function &GetCountRows($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM langs".Langs::whereString($parms));
   }

   function &GetRows($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT * FROM langs".
                             Langs::whereString($parms).Langs::orderString($order).
                             Langs::limitString($limit), 'id');
   }

   function &GetLangs($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT name, value_".mysql_real_escape_string(LANG)." FROM langs".
                             Langs::whereString($parms).Langs::orderString($order).
                             Langs::limitString($limit), 'name');
   }
}
?>