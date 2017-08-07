<?php
include_once(Site::GetParms('tablesPath')."Mainmenu.php");
class SitemapEngine {
   var $name; var $parms;
   function SitemapEngine($name, $parms) {
        $this->name = $name;
        $this->parms = $parms;
        $parmsT = array('href' => 'sitemap');
        if (defined("LANG")) $parmsT['lang'] = LANG;
        $menu = Mainmenu::GetRow($parmsT);
        $this->menu = $menu;
        $this->menuPath = (defined('LANG') && LANG <> 'ru' ? '/'.LANG : '').($this->menu['path'] ? $this->menu['path'] : '/');
        define("MENU_ID", ($this->menu['id'] ? $this->menu['id'] : 0));
        define("MENU_NAME", ($this->menu['name'] ? $this->menu['name'] : 'Карта сайта'));
        define("MENU_PATH", $this->menuPath);
        unset($menu);
   }

   function run() {
      if (!MENU_ID) Site::SetParms('bread-crumbs', array(MENU_NAME));
      $parms = array('active' => 1, 'parent_id' => 0, 'somewhere' => true);
      if (defined("LANG")) $parms['lang'] = LANG;
      $menus =& Mainmenu::GetRows($parms);
      $this->html = '';
      $this->html .= '<ul>';
      $this->html .= '<li><a href="/" title="Главная страница">Главная страница</a></li>';
      if (sizeOf($menus) > 0) {
         foreach ($menus as $id => $menu) {
            $this->html .= '<li><a href="'.($menu['href'] ? $menu['path'].$menu['href'] : Site::GetBaseRef()).'" title="'.htmlspecialchars($menu['name']).'">'.htmlspecialchars($menu['name']).'</a>';
            $this->GetList($id);
            $this->html .= '</li>';
         }
      }
      $this->html .= '</ul>';
      include(Site::GetTemplate('layout', 'sitemap'));
      return true;
   }

   function GetList($id) {
      $parms = array('active' => 1, 'parent_id' => $id);
      if (defined("LANG")) $parms['lang'] = LANG;
      $temp =& Mainmenu::GetRows($parms);
      if (sizeOf($temp) > 0) {
         $this->html .= '<ul>';
         foreach ($temp as $k => $v) {
            $this->html .= '<li><a href="'.($v['href'] ? $v['path'].$v['href'] : Site::GetBaseRef()).'" title="'.htmlspecialchars($v['name']).'">'.htmlspecialchars($v['name']).'</a>';
            $this->GetList($k);
            $this->html .= '</li>';
         }
         $this->html .= '</ul>';
      }
   }
}
?>