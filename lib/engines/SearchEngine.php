<?php
include_once(Site::GetParms('tablesPath')."Mainmenu.php");
include_once(Site::GetParms('tablesPath')."Articles.php");
include_once(Site::GetParms('tablesPath')."News.php");
include_once(Site::GetParms('tablesPath')."Catalog.php");
class SearchEngine {
   var $name; var $parms;
   function SearchEngine($name, $parms) {
        $this->name = $name;
        $this->parms = $parms;
        $parmsT = array('href' => 'search');
        if (defined("LANG")) $parmsT['lang'] = LANG;
        $menu = Mainmenu::GetRow($parmsT);
        $this->menu = $menu;
        $this->menuPath = (defined('LANG') && LANG <> 'ru' ? '/'.LANG : '').($this->menu['path'] ? $this->menu['path'] : '/');
        define("MENU_ID", ($this->menu['id'] ? $this->menu['id'] : 0));
        define("MENU_NAME", ($this->menu['name'] ? $this->menu['name'] : 'Результаты поиска'));
        define("MENU_PATH", $this->menuPath);
        unset($menu);
   }

   function run() {
    //Если пришёл POST из формы поиска, то делаем редирект на урл с GET запросом
    $str = trim(strip_tags($_POST['search']));
    if ($str) return $this->menuPath.'search?search='.$str;
    //---------------//
    if (!MENU_ID) Site::SetParms('bread-crumbs', array(MENU_NAME));
    if (Site::GetParms('search') == 'simple') return $this->simpleSearch();
    else if (Site::GetParms('search') == 'sphinx') return $this->sphinxSearch();
    else return false;
   }
   
   function simpleSearch() {
      $str = trim(strip_tags($_GET['search']));
      if ($str) {
         $parms = array('active' => 1, 'like' => $str);
         if (defined("LANG")) $parms['lang'] = LANG;
         $mains =& Mainmenu::GetRows($parms);
         if (sizeOf($mains) > 0) foreach ($mains as $id => $item) {
            if (!$item['parent_id']) {
               $parmsT = array('active' => 1, 'parent_id' => $id);
               if (defined("LANG")) $parmsT['lang'] = LANG;
               $temp =& Mainmenu::GetRows($parmsT);
               if (sizeOf($temp) > 0) unset($mains[$id]);
            }
         }
            if (sizeOf($mains) > 0) foreach ($mains as $id => $item) {
               $mains[$id]['content'] = strip_tags($item['content'], '<table><ul><li><ol><img>');
               if (!preg_match('/'.$str.'/i', $mains[$id]['href'].' '.$mains[$id]['name'].' '.$mains[$id]['content'])) unset($mains[$id]);
            }
         $news =& News::GetRows(array('active' => 1, 'like' => $str));
         $parmsN = array('href' => 'news');
         if (defined("LANG")) $parmsN['lang'] = LANG;
         $menuN = Mainmenu::GetRow($parmsN);
         $newsMenuPath = ($menuN['path'] ? $menuN['path'] : '/');
         unset($parms['active']);
         $articles =& Articles::GetRows($parms);
         $parmsA = array('href' => 'news');
         if (defined("LANG")) $parmsA['lang'] = LANG;
         $menuA = Mainmenu::GetRow($parmsA);
         $articlesMenuPath = ($menuA['path'] ? $menuA['path'] : '/');
         $true = ((sizeOf($news) || sizeOf($articles) || sizeOf($mains)) ? true : false);
      }
      include(Site::GetTemplate('layout', 'search'));
      return true;
   }
   
   function sphinxSearch() {
      $numOnPage = 20;
      $str = trim(strip_tags($_GET['search']));
      $page = isset($_GET['page']) ? abs((int)$_GET['page']) : 1;
      $pagerUrl = $this->menuPath.'search?search='.htmlspecialchars($str).'&page=%';
      $newsIds = $pagesIds = $catalogIds = $itemsIds = array();
      $news = $pages = array();
      if ($str) {
         $tableSph = 'r2photos_search';
         $dbSph = new DataBaseMysql('127.0.0.1:9306', '', '');
         $countArr = $dbSph->SelectRow("select *, count(*) as c FROM ".$tableSph." WHERE match ('".Utils::escapeSphinx($str)."')");
         $count = $countArr['c'];
         if ($count > 0) {
            $numPages = ceil($count / $numOnPage);
            $limit = array('limit' => $numOnPage, 'offset' => (($page - 1) * $numOnPage));
            $items = $dbSph->SelectSet("select *, weight() w from ".$tableSph." WHERE match ('".Utils::escapeSphinx($str)."') ORDER BY type_id ASC, w DESC, idate DESC".$this->limitString($limit));
            if (sizeOf($items) > 0) foreach ($items as $k => $v) {
                switch( $v['type_id'] ) {
                    case 0: $pagesIds[] = $v['content_id']; break;
                    case 1: $newsIds[] = $v['content_id']; break;
                    case 2: $catalogIds[] = $v['content_id']; break;
                    case 3: $itemsIds[] = $v['content_id']; break;
                }
            }
         }
         if (sizeOf($newsIds) > 0) $news = News::GetRows(array('id_in' => implode(", ", $newsIds)));
         if (sizeOf($pagesIds) > 0) $pages = Mainmenu::GetRows(array('id_in' => implode(", ", $pagesIds)));
         if (sizeOf($catalogIds) > 0) $catalogs = Catalog::GetRows(array('id_in' => implode(", ", $catalogIds)));
         if (sizeOf($itemsIds) > 0) $catalogItems = Items::GetRows(array('id_in' => implode(", ", $itemsIds)));
         if( !empty( $catalogItems ) ) {
            $itemsParts = array();
            foreach( $catalogItems as $item ) {
                if( $item['id'] ) {
                    $itemsParts[ $item['catalog_id'] ] = $item['catalog_id'];
                }
            }
            if( !empty( $itemsParts ) ) {
                $itemsParts = Catalog::GetMinimums( array( 'active' => 1, 'id_in' => implode( ',', $itemsParts ) ) );
            }
         }
         $true = ((sizeOf($news) || sizeOf($pages) || sizeOf($catalogs) || sizeOf($catalogItems)) ? true : false);
      }
      include(Site::GetTemplate('layout', 'search'));
      return true;
   }
   
   function limitString($parms) { if (!isset($parms['limit'])) $parms['limit'] = 0; if (!isset($parms['offset'])) $parms['offset'] = 0; $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }
}
?>
