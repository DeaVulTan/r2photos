<?php
class CategoriesEngine {
   var $name; var $parms;
   function CategoriesEngine($name, $parms) { $this->name = $name; $this->parms = $parms; }

   function run() {
      return $this->viewCategories();
   }

   function viewCategories () {
      $parms = array('active' => 1);
      if ($this->parms['category']) $parms['id'] = $this->parms['category'];
      $categories =& Categories::GetRows($parms);
      if (sizeOf($categories) > 0) { foreach ($categories as $id => $one) $categories[$id]['forums'] =& Forums::GetRows(array('cat_id' => $id)); }
      include(Site::GetTemplate('forum', 'categories'));
      return true;
   }
}
?>