<?php
include_once(Site::GetParms('tablesPath')."Links.php");
class SitepagesEngine extends AdminEngine {
   function SitepagesEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      return Sitepages::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      return Sitepages::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         return Site::CreateUrl($this->name.'-list');
      }
      if ($this->parms['action'] == 'notlinks') {
         $items1 =& Sitepages::GetRows();
         $items2 = array();
         if (sizeOf($items1) > 0) {
            $i = 0;
            $j = 0;
            $size = sizeOf($items1);
            $keys = array_keys($items1);
            $page = (int)($this->values[0] ? $this->values[0] : 1);
            $numItemsOnPage = (int)($this->parms['per_page'] ? $this->parms['per_page'] : 20);
            $start = ($page - 1) * $numItemsOnPage;
            if ($start > $size) $start = $size;
            $end = $start + $numItemsOnPage;
            if ($end > $size) $end = $size;
            while ($i < $numItemsOnPage) {
               $j ++;
               if ($j > $size) break;
               if ($j < $start) continue;
               $k = $j - 1;
               if (!Links::GetLinksCheck(array('active' => 1, 'regexp' => $items1[$keys[$k]]['url']))) $items[] = $items1[$keys[$k]];
               $i ++;
            }
         }
         unset($items1);
         unset($keys);
         $numOfItems = sizeOf($items);
         $numPages = $size / $numItemsOnPage;
         if ($numOfItems) $pageNavigator = Site::GetPageNavigator($page, $numPages, Site::CreateUrl($this->name.'-notlinks_%'));
         include(Site::GetTemplate($this->name, 'notlinks'));
         return true;
      }
   }

   function buttonsAddTop() {
      echo '<td><input type="button" value="Нет в links" onclick="location.href=\''.Site::GetBaseRef().Site::CreateUrl($this->name.'-notlinks').'\'" class="button" /></td>';
   }

   function doBeforeRun() {
      if (IS_OFFICE !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'url');
         Site::SetSession($this->name."-order", 'asc');
      }
   }
}
?>