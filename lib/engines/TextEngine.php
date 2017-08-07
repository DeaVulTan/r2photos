<?php
include_once(Site::GetParms('tablesPath')."Mainmenu.php");
class TextEngine {
   var $name; var $parms;
   function TextEngine($name, $parms) { $this->name = $name; $this->parms = $parms; }

   function run() {
      $allPath = '/'.$this->parms['page'];
      preg_match('/^(.*)?\/([^\/]+)$/', $allPath, $pieceUrl);
      if (!$pieceUrl[1] && !$pieceUrl[2]) return $this->send404();
      $thisPath = $pieceUrl[1].'/';
      $thisHref = $pieceUrl[2];
      $parms = array('href' => $thisHref, 'active' => 1);
      if (defined("LANG")) $parms['lang'] = LANG;
      $fromMenu = Mainmenu::GetRow($parms);
      if ($fromMenu) {
         if ($fromMenu['path'] <> $thisPath) return $this->send404();
         $textContent = trim(preg_replace('/&nbsp;/', ' ', strip_tags($fromMenu['content'], '<table><img><ul><li><object><div>')));
         unset($parms['href']);
         $parms['parent_id'] = $fromMenu['id'];
         $submenus = Mainmenu::GetRows($parms);
         if (!$textContent && sizeOf($submenus) < 1) return $this->send404();
         $content = trim($fromMenu['content']);
         define("MENU_ID", ($fromMenu['id'] ? $fromMenu['id'] : 0));
         define("MENU_NAME", ($fromMenu['name'] ? $fromMenu['name'] : 'Статьи'));
         define("MENU_PATH", ($fromMenu['path'] ? $fromMenu['path'] : '/'));
         define("MENU_CRUMBS_LASTLINK", false);
         include(Site::GetTemplate('layout', 'content'));
         return true;
      }
      return $this->send404();
   }

   function send404() {
      $pageFile = Site::GetParms('absoluteOffsetPath').'pages/404.htm';
      if (file_exists($pageFile)) { include($pageFile); return true; }
      else return false;
   }
}
?>