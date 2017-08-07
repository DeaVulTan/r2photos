<?php
class CatalogEngine extends AdminEngine {
   function CatalogEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['parent_id'] = (int)Site::GetSession($this->name."-parent_id");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Catalog::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['parent_id'] = (int)Site::GetSession($this->name."-parent_id");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Catalog::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         if ($_POST['fromFF']) Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         else Site::SetSession($this->name."-parent_id", $this->values[0]);
         return Site::CreateUrl($this->name."-list");
      }
   }

   function doBeforeChangeList() {
      $db =& Site::GetDB();
      if (is_array($_POST['deletedItem']) && $_POST['_del_x'] && $_POST['_del_y']) foreach ($_POST['deletedItem'] as $k => $v) {
         $items = array_keys(Items::GetIds(array('catalog_id' => $k)));
         if (sizeOf($items) > 0) $db->Query("DELETE FROM items WHERE id IN (".implode(", ", $items).")");
         $this->GetSubCat($k);
      }
      return true;
   }
   
   function doBeforeUpdateData() {
    $thisUrl = Utils::Translit($this->table->data['href']);
    if (!$thisUrl) $thisUrl = Utils::Translit($this->table->data['name']);
    $this->table->SetValue('href', $thisUrl);
    return;
   }

   function GetSubCat($id) {
      $temp =& Catalog::GetRows(array('parent_id' => $id));
      $db =& Site::GetDB();
      if (sizeOf($temp) > 0) foreach ($temp as $k => $v) {
         $items = array($k);
         Catalog::Delete($items);
         $items = array_keys(Items::GetIds(array('catalog_id' => $k)));
         if (sizeOf($items) > 0) $db->Query("DELETE FROM items WHERE id IN (".implode(", ", $items).")");
         $this->GetSubCat($k);
      }
   }

   function getSubPartsStr($name, $item) {
      $thisCount = Catalog::GetCountRows(array('parent_id' => $item['id']));
      echo $thisCount.' - <a href="'.Site::CreateUrl($this->name.'-filter', array($item['id'])).'">смотреть</a>';
   }

   function getItemsStr($name, $item) {
      $thisCountItems = Items::GetCountRows(array('catalog_id' => $item['id']));
      echo $thisCountItems.' - <a href="'.Site::CreateUrl('items-filter', array($item['id'], 0)).'">смотреть</a>';
   }

   function doBeforeViewEdit() {
      if (!$this->itemId) $this->form->set('parent_id', (int)Site::GetSession($this->name."-parent_id"));
   }

   function doBeforeRun() {
      if (IS_CATALOG !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'ord');
         Site::SetSession($this->name."-order", 'asc');
      }
   }

   function topNav() {
      return $this->GetTopNav((int)Site::GetSession($this->name."-parent_id"));
   }

   function GetTopNav($id) {
      $ret = '';
      if (!$id) $ret = 'Разделы каталога';
      else {
         $link = array();
         $forId = $id;
         $ret = '<a href="'.$this->name.'-filter_0.htm" title="Разделы каталога">Разделы каталога</a>';
         while ($forId) {
            $info =& Catalog::GetRow(array('id' => $forId));
            $link[] = ($forId <> $id ? '<a href="'.$this->name.'-filter_'.$forId.'.htm" title="'.$info['name'].'">' : '').$info['name'].($forId <> $id ? '</a>' : '');
            $forId = $info['parent_id'];
         }
         $link = array_reverse($link);
         $ret .= ' &gt; '.implode(" &gt; ", $link);
      }
      return $ret;
   }
}
?>