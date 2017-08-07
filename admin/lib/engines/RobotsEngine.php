<?php
include_once(Site::GetParms('tablesPath')."Configs.php");
class RobotsEngine extends AdminEngine {
   function RobotsEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['only_robots'] = true;
      return Configs::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['only_robots'] = true;
      return Configs::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'listself') {
         $this->table =& Configs::Get('', true);
         $numOfItems = $this->GetCountItems();
         $numItemsOnPage = 1;
         $numPages = 1;
         $items =& $this->GetItems($this->values, array('limit' => $numItemsOnPage), $order);
         include(Site::GetParms('absolutePath').'admin/templates/'.$this->name.'/list.phtml');
         return true;
      }
      else if ($this->parms['action'] == 'editself') {
         $this->table =& Configs::Get('', true);
         if (is_object($this->table)) {
            $item =& reset(Configs::GetRows(array('name' => 'robots_txt')));
            $this->values[0] = ($item['id'] > 0 ? $item['id'] : 0);
            if ($this->values[0]) {
               $this->itemId = $this->values[0];
               $this->table->SetObjectData($this->itemId);
               if (is_object($this->table->GetFormObject())) {
                  $this->form =& $this->table->GetFormObject();
                  $this->form->setParm('action', Site::CreateUrl($this->name.'-changeself', $this->values));
               }
            }
            else {
               if (is_object($this->table->GetFormObject())) {
                  $this->form =& $this->table->GetFormObject();
                  if (defined('LANG')) $this->table->SetValue('lang', 'all');
                  $this->table->SetValue('parts_id', '4');
                  $this->table->SetValue('name', 'robots_txt');
                  $this->table->SetValue('description', 'Содержимое файла robots.txt');
                  $this->table->SetValue('value', "User-agent: *\r\nDisallow: /index.htm\r\nDisallow: /search.htm\r\nDisallow: /picture-click\r\nDisallow: /admin\r\nDisallow: *post_new\r\nDisallow: *topic_new\r\nDisallow: /*?refer\r\nDisallow: *asc\r\nDisallow: *desc\r\nDisallow: *pdf\r\nDisallow: /*utm_source*\r\nDisallow: /*openstat*\r\nHost: www.site.ru");
                  $this->form->setParm('action', Site::CreateUrl($this->name.'-changeself', $this->values));
               }
            }
            return $this->viewEdit();
         }
         else return false;
      }
      else if ($this->parms['action'] == 'changeself') {
         if ($_SERVER['REQUEST_METHOD'] <> 'POST') {
            unset($this->values[0]);
            return Site::CreateUrl($this->name.'-listself', $this->values);
         }
         $this->table =& Configs::Get('', true);
         if (is_object($this->table)) {
            if ($this->values[0]) {
               $this->itemId = $this->values[0];
               $this->table->SetObjectData($this->itemId);
            }
            if (is_object($this->table->GetFormObject())) $this->form =& $this->table->GetFormObject();
            $form = $this->form;
            if (!$form->process()) return false;
            $this->table->SetData($form->get());
            $lastId = $this->table->StoreRow();
            unset($this->values[0]);
            return Site::CreateUrl($this->name.'-listself', $this->values);
         }
         else return false;
      }
      return;
   }

   function doBeforeRun() {
      if (IS_OFFICE !== true) return '404.htm';
   }
}
?>