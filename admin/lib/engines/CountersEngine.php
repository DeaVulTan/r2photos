<?php
include_once(Site::GetParms('tablesPath')."Configs.php");
class CountersEngine extends AdminEngine {
   function CountersEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['only_counters'] = true;
      return Configs::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['only_counters'] = true;
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
            $item =& reset(Configs::GetRows(array('name' => 'counters_txt')));
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
                  $this->table->SetValue('name', 'counters_txt');
                  $this->table->SetValue('description', 'Счётчики на сайте');
                  $this->table->SetValue('value', "<!-- Видимая часть счётчиков -->\n\n<!-- // -->\n<div class='pa dn'>\n<!-- Невидимая часть счётчиков -->\n\n<!-- // -->\n</div>");
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
      if (IS_CONFIG !== true) return '404.htm';
   }
}
?>