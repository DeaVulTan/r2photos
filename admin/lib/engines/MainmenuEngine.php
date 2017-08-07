<?php
class MainmenuEngine extends AdminEngine {
   function MainmenuEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['parent_id'] = (int)Site::GetSession($this->name."-parent_id");
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Mainmenu::GetCountRows($parms);
   }

   function GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['parent_id'] = (int)Site::GetSession($this->name."-parent_id");
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Mainmenu::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         if ($_POST['fromFF']) {
            Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
            if (defined('LANG')) Site::SetSession($this->name.'-lang', $_POST['lang']);
         }
         else Site::SetSession($this->name."-parent_id", $this->values[0]);
         return Site::CreateUrl($this->name."-list");
      }
   }

   function getSubStr($name, $item) {
      $thisCountItems = Mainmenu::GetCountRows(array('parent_id' => $item['id']));
      echo $thisCountItems.' - <a href="'.Site::CreateUrl('mainmenu-filter', array($item['id'])).'">смотреть</a>';
   }
   
   function doBeforeUpdateData() {
    $db = Site::GetDB();
    //Проверяем URL
    $thisUrl = Utils::Translit($this->table->data['href']);
    if (!$thisUrl) $thisUrl = Utils::Translit($this->table->data['name']);
    //Пока такой есть в данном уровне дерева добавляем в начало 'new-'
    while ($check = $db->SelectValue("SELECT id FROM main_menu WHERE".($this->itemId ? ' id<>'.$this->itemId.' AND' : '')." href='".mysql_real_escape_string($thisUrl)."'".($this->table->data['lang'] ? " AND lang='".$this->table->data['lang']."'" : '')." AND parent_id='".(int)$this->table->data['parent_id']."'")) {
        $thisUrl = 'new-'.$thisUrl;
    }
    $this->table->SetValue('href', $thisUrl);
    //-----------//
    //Проверяем путь
    $thisPath = $this->table->data['path'];
    if (!$thisPath) {
        //Если не указан собираем из урла и пути родителя
        $thisPath = '/';
        if ($this->table->data['parent_id']) {
            $prefixPath = $db->SelectValue("SELECT CONCAT(path, href) FROM main_menu WHERE id='".(int)$this->table->data['parent_id']."'");
            $thisPath = $prefixPath.$thisPath;
        }
    }
    //Заменяем "левые символы"
    if (!preg_match('/^\/([0-9a-z\-\/]+\/)?$/i', $thisPath)) $thisPath = preg_replace('/[^0-9a-zA-Z\-\/]/', '', $thisPath);
    $thisPath = mb_strtolower($thisPath, 'utf-8');
    //Проверяем слеши в начале и конце
    if ($thisPath <> '/') {
        if (!preg_match('/^\/.+/', $thisPath)) $thisPath = '/'.$thisPath;
        if (!preg_match('/.+\/$/', $thisPath)) $thisPath = $thisPath.'/';
    }
    //Заменяем повторы тире и слешей
    $thisPath = preg_replace(array('/\-+/', '/\/+/'), array('-', '/'), $thisPath);
    $this->table->SetValue('path', $thisPath);
    return;
   }

   function doBeforeRun() {
      if (IS_MENU !== true && IS_MENU_ !== true) return '404.htm';
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
      if (!$id) $ret = 'Навигация';
      else {
         $link = array();
         $forId = $id;
         $ret = '<a href="'.$this->name.'-filter_0.htm" title="Навигация">Навигация</a>';
         while ($forId) {
            $info = Mainmenu::GetRow(array('id' => $forId));
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