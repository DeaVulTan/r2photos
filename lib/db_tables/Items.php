<?php
include_once(Site::GetParms('tablesPath')."Catalog.php");
class Items {
   function Items() { }
   function &Structure($init = false) {
      if ($init) {
         $arr1 =& Items::GetList();
         $form = array(
            'is_active' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Активность'))),
            'ord' => array('default' => 0, 'form' => array('Input' => array('name' => 'Сортировка', 'style' => 'size="4"'))),
            //'is_new' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Новинка'))),
            'is_popular' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Популярная'))),
            'catalog_id' => array('default' => (int)Site::GetSession("items-catalog_id"), 'form' => array('Select' => array('items' => &$arr1, 'field' => 'name', 'name' => 'Раздел'))),
            //'fabrics_id' => array('default' => (int)Site::GetSession("items-fabrics_id"), 'form' => array('Select' => array('items' => Fabrics::GetRows(), 'field' => 'name', 'name' => 'Производитель'))),
            //'art' => array('default' => '', 'form' => array('Input' => array('name' => 'Артикул', 'style' => 'style="width: 20%;"'))),
            'name' => array('default' => '', 'form' => array('Input' => array('name' => 'Наименование', 'style' => 'style="width: 100%;"'))),
            'href' => array('default' => '', 'form' => array('Input' => array('name' => 'URL', 'style' => 'style="width: 100%;"'))),
            'price' => array('default' => 0, 'form' => array('Input' => array('name' => 'Цена', 'style' => 'style="width: 20%;"'))),
            'picture' => array('default' => '', 'form' => array('Upload' => array('text' => true, 'path' => 'data/image/catalog','name' => 'Картинка', 'style' => 'style="width: 40%;"'))),
            //'picture_big' => array('default' => '', 'form' => array('Upload' => array('text' => true, 'path' => 'data/image/catalog', 'name' => 'Картинка (бол.)', 'style' => 'style="width: 40%;"'))),
            //'picture_real' => array('default' => '', 'form' => array('Upload' => array('text' => true, 'path' => 'data/image/catalog','name' => 'Картинка (реал.)', 'style' => 'style="width: 40%;"'))),
            'announce' => array('default' => '', 'form' => array('FCKeditor' => array('ToolbarSet' => '__set2__', 'Height' => 500, 'name' => 'Краткое&nbsp;описание'))),
            'description' => array('default' => '', 'form' => array('FCKeditor' => array('ToolbarSet' => '__set2__', 'Height' => 500, 'name' => 'Полное&nbsp;описание'))),
         );
         $columns = array(
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
            //'is_new' => array('name' => 'Новинка', 'autoUpdate' => true),
            'is_popular' => array('name' => 'Популярная', 'autoUpdate' => true),
            'ord' => array('name' => 'Сортировка', 'autoUpdate' => true, 'sort' => true, 'order' => true),
            //'art' => array('name' => 'Артикул', 'sort' => true),
            'name' => array('name' => 'Наименование', 'style' => 'width="100%" align="left"', 'sort' => true),
            'price' => array('name' => 'Цена', 'autoUpdate' => true, 'addstyle' => ' class="field" size="10"', 'sort' => true),
            '_photographers' => array('name' => 'Фотографы', 'function' => 'getPhotographersStr', 'style' => ' nowrap '),
         );
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'items',
         'tableName'  => 'Позиции каталога'
      );
   }
   function &Get($id = '', $init = false) { return new Table(Items::Structure($init), $id); }
   function Update(&$rows) { $t = new Table(Items::Structure()); return $t->UpdateRows($rows); }
   function Save(&$rows) { $t = new Table(Items::Structure()); return $t->SaveRows($rows); }
   function Delete(&$rows) { $t = new Table(Items::Structure()); return $t->DeleteRows($rows); }

   function GetList($id = 0, $offset = '') {
      $temp =& Catalog::GetRows(array('parent_id' => $id));
      if (sizeOf($temp) > 0) foreach ($temp as $k => $v) {
         $v['name'] = $offset.$v['name'];
         if (!sizeOf($res)) $res[0] = array('id' => 0, 'name' => '[Нет родителя]');
         $res[$k] = $v;
         $arr = Items::GetList($k, '-'.$offset);
         if (sizeOf($arr) > 0) foreach ($arr as $k1 => $v1) $res[$k1] = $v1;
      }
      return $res;
   }

   function whereString($parms) {
      $where = '';
      if (isset($parms['active'])) $where .= ($where ? " AND " : " WHERE ")."i.is_active='".(int)$parms['active']."'";
      if ($parms['new']) $where .= ($where ? " AND " : " WHERE ")."i.is_new='".(int)$parms['new']."'";
      if ($parms['popular']) $where .= ($where ? " AND " : " WHERE ")."i.is_popular='".(int)$parms['popular']."'";
      if ($parms['id'] > 0) $where .= ($where ? " AND " : " WHERE ")."i.id='".(int)$parms['id']."'";
      if ($parms['id_in']) $where .= ($where ? " AND " : " WHERE ")."i.id IN (".mysql_real_escape_string($parms['id_in']).")";
      if ($parms['catalog_in']) $where .= ($where ? " AND " : " WHERE ")."i.catalog_id IN (".mysql_real_escape_string($parms['catalog_in']).")";
      if ($parms['url']) $where .= ($where ? " AND " : " WHERE ")."i.url='".mysql_real_escape_string($parms['url'])."'";
      if (isset($parms['parent_id'])) $where .= ($where ? " AND " : " WHERE ")."c.parent_id='".(int)$parms['parent_id']."'";
      if ($parms['price_min']) $where .= ($where ? " AND " : " WHERE ")."i.price >= ".( ( int ) $parms['price_min'] );
      if ($parms['price_max']) $where .= ($where ? " AND " : " WHERE ")."i.price <= ".( ( int ) $parms['price_max'] );
      if ($parms['catalog_id']) $where .= ($where ? " AND " : " WHERE ")."i.catalog_id='".(int)$parms['catalog_id']."'";
      if ($parms['fabrics_id']) $where .= ($where ? " AND " : " WHERE ")."i.fabrics_id='".(int)$parms['fabrics_id']."'";
      if (isset($parms['href'])) $where .= ($where ? " AND " : " WHERE ")."i.href='".mysql_real_escape_string($parms['href'])."'";
      if ($parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(i.description, ' ', i.name, ' ', i.art, ' ', i.href) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      return $where;
   }

   function limitString($parms) { $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY i.ord';
      else foreach ($parms as $k => $v) return ' ORDER BY i.'.$k.' '.strtoupper($v);
   }

   function &GetCountRows($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT COUNT(i.id) FROM items i".Items::whereString($parms));
   }

   function GetPriceInfo($parms = array()) {
      $db = Site::GetDB();
      return $db->SelectRow("SELECT MIN(i.price) as `price_min`, MAX(i.price) as `price_max` FROM items i".Items::whereString($parms));
   }

   function &GetRows($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT i.*, f.name AS nameF, c.name AS nameC FROM items i
                                 LEFT JOIN catalog c ON c.id=i.catalog_id
                                 LEFT JOIN fabrics f ON f.id=i.fabrics_id".
                             Items::whereString($parms)."
                             GROUP BY i.id".
                             Items::orderString($order).
                             Items::limitString($limit), 'id');
   }

   function GetMinimums($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT i.id, i.name, i.catalog_id, i.price FROM items i ".
                             Items::whereString($parms).
                             Items::orderString($order).
                             Items::limitString($limit), 'id');
   }

   function GetNames($parms = array(), $limit = array(), $order = array()) {
      $db = Site::GetDB();
      $catalogList = Catalog::GetNames();
      $itemsList = $db->SelectSet("SELECT i.id, i.name, i.catalog_id FROM items i
                                 LEFT JOIN catalog c ON c.id=i.catalog_id
                                 LEFT JOIN fabrics f ON f.id=i.fabrics_id".
                             Items::whereString($parms)."
                             GROUP BY i.id".
                             Items::orderString($order).
                             Items::limitString($limit), 'id');
      foreach( $itemsList as $id => $item ) {
        if( $item['catalog_id'] && isset( $catalogList[ $item['catalog_id'] ] ) ) {
            $itemsList[ $id ]['name'] = '<strong>'.$catalogList[ $item['catalog_id'] ]['name'].'</strong>: '.$item['name'];
        }
      }
      return $itemsList;
   }

   function &GetRow($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT i.* FROM items i".Items::whereString($parms));
   }

   function &GetIds($parms = array(), $limit = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT i.id FROM items i".Items::whereString($parms)." ORDER BY i.ord".Items::limitString($limit), 'id');
   }

   function &GetFabrics($parms = array(), $limit = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT DISTINCT i.fabrics_id, f.name AS name
                                 FROM items i
                                 LEFT JOIN catalog c ON c.id=i.catalog_id
                                 LEFT JOIN fabrics f ON f.id=i.fabrics_id".
                             Items::whereString($parms)."
                             ORDER BY f.ord".
                             Items::limitString($limit), 'fabrics_id');
   }

   function &GetCatalogs($parms = array(), $limit = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT DISTINCT i.catalog_id, c.name AS name
                                 FROM items i
                                 LEFT JOIN catalog c ON c.id=i.catalog_id
                                 LEFT JOIN fabrics f ON f.id=i.fabrics_id".
                             Items::whereString($parms)."
                             ORDER BY c.ord".
                             Items::limitString($limit), 'catalog_id');
   }
}
?>