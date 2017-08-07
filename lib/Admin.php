<?php
include_once(Site::GetParms('libPath').'Form.php');
class AdminEngine {
   var $name; var $parms; var $values; var $form; var $itemId; var $table; var $nameParent;
   var $pager = array(20 => 20, 30 => 30, 50 => 50, 100 => 100, 'all' => 'Все');
   function AdminEngine($name, $parms) { $this->name = $name; if ($parms['parms']) { $this->values = explode(Site::GetParms('urlDelimeter'), $parms['parms']); unset($parms['parms']); } $this->parms = $parms; $this->itemId = (int)(($this->parms['action'] == 'edit' || $this->parms['action'] == 'change') ? ($this->values[0] ? $this->values[0] : 0) : 0); Site::SetParms( 'admin-current-item', $this->itemId ); if (is_object($this->parms['tableObject'])) { $this->table = $this->parms['tableObject']->Get(($this->itemId > 0 ? $this->itemId : ''), true); unset($this->parms['tableObject']); } $this->nameParent = ucfirst($this->name)."Engine"; }
   function selfRun() { return; }
   function doBeforeRun() { return; }
   function run() { $res = $this->doBeforeRun(); if ($res) return $res; if ($this->parms['action'] == 'pager') { return $this->setPager(); } if ($this->parms['action'] == 'sort') { return $this->doSort(); } if (($this->parms['action'] == 'edit' || $this->parms['action'] == 'change') && is_object($this->table)) { if (is_object($this->table->GetFormObject())) { $this->form = $this->table->GetFormObject(); $this->form->setParm('action', Site::CreateUrl($this->name.'-change', $this->values)); } }
   if (($this->parms['action'] == 'clon') && is_object($this->table) && $this->values[0]) {
      $this->table->SetObjectData($this->values[0]);
      $this->values[0] = 0;
      if (is_object($this->table->GetFormObject())) {
         $this->form = $this->table->GetFormObject();
         $this->form->setParm('action', Site::CreateUrl($this->name.'-change', $this->values));
   } }
   if ($this->parms['action'] == 'order') {
      if ($_POST['ud'] && $_POST['field'] && $_POST['id'] && $_POST['idA'] && $_POST['page']) {
         foreach ($_POST as $k => $v) $_POST[$k] = addslashes(stripslashes($v));
         $db = Site::GetDB();
         $idOrd = $db->SelectValue("SELECT ".$_POST['field']." FROM ".$this->table->name." WHERE id='".$_POST['id']."'");
         $idAOrd = $db->SelectValue("SELECT ".$_POST['field']." FROM ".$this->table->name." WHERE id='".$_POST['idA']."'");
         $db->Query("UPDATE ".$this->table->name." SET ".$_POST['field']."='".$idAOrd."' WHERE id='".$_POST['id']."'");
         $db->Query("UPDATE ".$this->table->name." SET ".$_POST['field']."='".$idOrd."' WHERE id='".$_POST['idA']."'");
         $page = $_POST['page'];
         $numOfItems = $this->GetCountItems($this->values);
         $numItemsOnPage = (int)(Site::GetSession($this->name."-pager") ? (Site::GetSession($this->name."-pager") == 'all' ? $numOfItems : Site::GetSession($this->name."-pager")) : (isset($this->parms['per_page']) && $this->parms['per_page'] ? $this->parms['per_page'] : 20));
         $numPages = ceil($numOfItems / $numItemsOnPage);
         $order = array();
         if (Site::GetSession($this->name."-sort") && Site::GetSession($this->name."-order")) {
            $sort = preg_replace('/777/', '_', Site::GetSession($this->name."-sort"));
            $order[$sort] = Site::GetSession($this->name."-order");
         }
         $items = $this->GetItems($this->values, array('limit' => $numItemsOnPage, 'offset' => (($page - 1) * $numItemsOnPage)), $order);
         if (file_exists(Site::GetParms('absolutePath').'admin/templates/'.$this->name.'/list-table.phtml')) include(Site::GetParms('absolutePath').'admin/templates/'.$this->name.'/list-table.phtml');
         else include(Site::GetTemplate('layout', 'list-table'));
      }
      return true;
   }
   if ($this->parms['action'] == 'list') return $this->viewList(); else if ($this->parms['action'] == 'edit' || $this->parms['action'] == 'clon') return $this->viewEdit(); else if ($this->parms['action'] == 'change') return $this->doChange(); else if ($this->parms['action'] == 'changelist') return $this->doChangeList(); return $this->selfRun(); return false; }
   function doBeforeViewList() { return; }
   function viewList() { $this->doBeforeViewList(); $page = (int)($this->values[0] ? $this->values[0] : 1); $numOfItems = $this->GetCountItems($this->values); $numItemsOnPage = (int)(Site::GetSession($this->name."-pager") ? (Site::GetSession($this->name."-pager") == 'all' ? $numOfItems : Site::GetSession($this->name."-pager")) : (isset($this->parms['per_page']) && $this->parms['per_page'] ? $this->parms['per_page'] : 20)); $numPages = ceil($numOfItems / $numItemsOnPage); $pageParms = array('%'); if (sizeOf($this->values) > 1) foreach ($this->values as $k => $v) { if ($k == 0) continue; $pageParms[] = $v; } if ($numPages > 1) $pageNavigator = Site::GetPageNavigator($page, $numPages, Site::CreateUrl($this->name.'-list', $pageParms), 100); $order = array(); if (Site::GetSession($this->name."-sort") && Site::GetSession($this->name."-order")) { $sort = preg_replace('/777/', '_', Site::GetSession($this->name."-sort")); $order[$sort] = Site::GetSession($this->name."-order"); } $items = $this->GetItems($this->values, array('limit' => $numItemsOnPage, 'offset' => (($page - 1) * $numItemsOnPage)), $order); if (file_exists(Site::GetParms('absolutePath').'admin/templates/'.$this->name.'/list.phtml')) include(Site::GetParms('absolutePath').'admin/templates/'.$this->name.'/list.phtml'); else include(Site::GetTemplate('layout', 'list')); return true; }
   function doBeforeViewEdit() { return; }
   function viewEdit() { $res = $this->doBeforeViewEdit(); if (isset($res)) return $res; $this->form->assign($this->table->GetData()); unset($this->values[0]); if (file_exists(Site::GetParms('absolutePath').'admin/templates/'.$this->name.'/edit.phtml')) include(Site::GetParms('absolutePath').'admin/templates/'.$this->name.'/edit.phtml'); else include(Site::GetTemplate('layout', 'edit')); return true; }
   function doBeforeUpdateData() { return; }
   function doAfterUpdateData($id) { return; }
   function doChange() { if ($_SERVER['REQUEST_METHOD'] <> 'POST') { unset($this->values[0]); return Site::CreateUrl($this->name.'-list', $this->values); } $form = $this->form; if (!$form->process()) return false; $this->table->SetData($form->get()); $this->doBeforeUpdateData(); $lastId = $this->table->StoreRow(); $this->doAfterUpdateData(($this->itemId ? $this->itemId : $lastId)); unset($this->values[0]); return Site::CreateUrl($this->name.'-list', $this->values); }
   function doBeforeChangeList() { return; }
   function doChangeList() {
    if ($_SERVER['REQUEST_METHOD'] <> 'POST') {
        return Site::CreateUrl($this->name.'-list', $this->values);
    }
    if (is_object($this->table)) {
        $this->doBeforeChangeList();
        if (is_array($_POST['deletedItem']) && isset($_POST['_del_x']) && isset($_POST['_del_y'])) {
            $delItems = array_keys($_POST['deletedItem']);
            foreach ($delItems as $k => $v) {
                unset($_POST['itemIds'][$v]);
            }
            $this->table->DeleteRows($delItems);
            include_once( Site::GetParms( 'libPath' ).'Cataloglinksmgr.php' );
            $linksObj = new Cataloglinksmgr();
            $linksObj->DeleteLinks( $this->table->name, $delItems );
            unset( $linksObj );
        }
        if (sizeOf($this->table->autoUpdate) && sizeOf($_POST['itemIds']) > 0 && isset($_POST['_change_x']) && isset($_POST['_change_y'])) {
            $rows = array();
            foreach ($_POST['itemIds'] as $id => $one) {
                foreach ($this->table->autoUpdate as $k => $name) {
                    if ($this->table->fields[$name]['name'] == 'CheckBox') {
                        $rows[$id][$name] = ($_POST['itemChange'][$id][$name] ? 1 : 0);
                    } else {
                        $rows[$id][$name] = $_POST['itemChange'][$id][$name];
                    }
                }
            }
            if (sizeOf($rows) > 0) {
                $this->table->UpdateRows($rows);
            }
        }
    }
    return Site::CreateUrl($this->name.'-list', $this->values);
   }
   function doSort() { Site::SetSession($this->name."-sort", $this->values[0]); Site::SetSession($this->name."-order", $this->values[1]); return Site::CreateUrl($this->name."-list"); }
   function setPager() { Site::SetSession($this->name."-pager", $this->values[0]); return Site::CreateUrl($this->name."-list"); }
   function echoAutoUpdate($name, $type, $item, $addstyle) {
      if ($type == 'CheckBox') Action::CheckBox('itemChange['.$item['id'].']['.$name.']', $item[$name]);
      else if ($type == 'Input') Action::Input('itemChange['.$item['id'].']['.$name.']', $item[$name], ($addstyle ? $addstyle : ' class="field" size="4"'));
      else if ($type == 'Select') Action::Select('itemChange['.$item['id'].']['.$name.']', $item[$name], $item['itemsSelect'], ($addstyle ? $addstyle : ' class="field"'), $item['parmsSelect']);
      else if ($type == 'TextArea') Action::TextArea('itemChange['.$item['id'].']['.$name.']', $item[$name], ($addstyle ? $addstyle : ' rows="10" cols="100" style="width: 100%;"'));
      //echo '<div class="autoUpdate" id="'.$name.$item['id'].'" onclick="showInput('.$item['id'].', \''.$name.'\', \''.($addstyle ? $addstyle : ' class="field" size="4"').'\');">'.$item[$name].'</div>';
   }
   function checkFilterTemplate() { if (file_exists(Site::GetParms('absolutePath').'admin/templates/'.$this->name.'/filter.phtml')) include(Site::GetParms('absolutePath').'admin/templates/layout/filter.phtml'); }
   function buttonsAddTop($items = array()) { return; }
   function buttonsAddBottom($items = array()) { return; }
   function topNav() { return $this->table->tableName; }
   function echoAddTr($name) { return true; }
}
?>
