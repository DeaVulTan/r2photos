<?php
class AdminsEngine extends AdminEngine {
   function AdminsEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      return Admins::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      return Admins::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         return Site::CreateUrl($this->name.'-list');
      }
      return;
   }

   function doBeforeViewEdit() {
      include_once(Site::GetParms('tablesPath')."Mainmenu.php");
      $this->parms['menusAll'] =& Mainmenu::GetRows(array('parent_id' => 0));
      $this->parms['menus'] = array();
      $piece = explode("|", $this->table->GetValue('menus'));
      if (sizeOf($piece) > 0) foreach ($piece as $k => $v) if (trim($v)) $this->parms['menus'][] = trim($v);
   }

   function GetList($id, $offset) {
      $temp =& Mainmenu::GetRows(array('parent_id' => $id));
      if (sizeOf($temp) > 0) {
         echo '<div style="margin-left: '.$offset.'px;">';
         foreach ($temp as $k => $v) {
            Action::CheckBox('menus['.$k.']', (in_array($k, $this->parms['menus']) ? 1 : ''));
            echo ' - '.$v['name']." (".$v['lang'].")<br />";
            $this->GetList($k, ($offset + 20));
         }
         echo '</div>';
      }
   }

   function doBeforeUpdateData() {
      $temp = array();
      if (sizeOf($_POST['menus']) > 0) foreach ($_POST['menus'] as $k => $v) if (trim($v) == 1) $temp[] = $k;
      $menus = (sizeOf($temp) > 0 ? "|".implode("|", $temp)."|" : '');
      $this->table->SetValue('menus', $menus);
      if ($_POST['password']) {
         $this->table->SetValue('password', md5(trim(strip_tags('-'.$_POST['password'].'-'))));
         $nameHash = mb_strtolower(PROJECT_NAME, 'utf-8').'_admin_hash-'.md5(date("Y").'-'.Site::GetParms('randomHash'));
         setcookie($nameHash, '');
      }
      return;
   }

   function doBeforeRun() {
      if (IS_ADMINS !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'fio');
         Site::SetSession($this->name."-order", 'asc');
      }
   }
}
?>
