<?php
class ItemsEngine extends AdminEngine {
   function ItemsEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      $parms['catalog_id'] = (int)Site::GetSession($this->name."-catalog_id");
      $parms['fabrics_id'] = (int)Site::GetSession($this->name."-fabrics_id");
      $parms['new'] = Site::GetSession($this->name."-is_new");
      $parms['popular'] = Site::GetSession($this->name."-is_popular");
      return Items::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      $parms['catalog_id'] = (int)Site::GetSession($this->name."-catalog_id");
      $parms['fabrics_id'] = (int)Site::GetSession($this->name."-fabrics_id");
      $parms['new'] = (int)Site::GetSession($this->name."-is_new");
      $parms['popular'] = (int)Site::GetSession($this->name."-is_popular");
      return Items::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         if ($_POST['fromFF']) {
            Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
            Site::SetSession($this->name."-catalog_id", $_POST['catalog_id']);
            Site::SetSession($this->name."-fabrics_id", $_POST['fabrics_id']);
            Site::SetSession($this->name."-is_new", $_POST['is_new']);
            Site::SetSession($this->name."-is_popular", $_POST['is_popular']);
         }
         else {
            Site::SetSession($this->name."-catalog_id", $this->values[0]);
            Site::SetSession($this->name."-fabrics_id", $this->values[1]);
         }
         return Site::CreateUrl($this->name."-list");
      }
   }

    function getPhotographersStr( $name, $item ) {
        include_once( Site::GetParms( 'libPath' ).'Cataloglinksmgr.php' );
        $links = new Cataloglinksmgr();
        $type = $links->clType('items');
        $linksTo = $links->getCountLinksFrom($type, $item['id']);
        echo '['.$linksTo[$itemType = $links->clType('photographers')].' - <a href="cataloglinks-to_'.$type.'_'.$item['id'].'_'.$itemType.'.htm">смотреть</a>]<br />';
        unset( $links );
    }//getPhotographersStr

   function doBeforeChangeList() {
      $path = Site::GetParms('absolutePath').'data/image/catalog/';
      if (is_array($_POST['deletedItem']) && $_POST['_del_x'] && $_POST['_del_y']) foreach ($_POST['deletedItem'] as $k => $v) {
         $item = Items::GetRow(array('id' => $k));
         if ($item['picture_small']) unlink(Site::GetParms('absolutePath').$item['picture_small']);
         if ($item['picture_big']) unlink(Site::GetParms('absolutePath').$item['picture_big']);
         if ($item['picture_real']) unlink(Site::GetParms('absolutePath').$item['picture_real']);
         if (is_dir($path.'pictures/'.$item['art'].'/')) {
            $handle = opendir($path.'pictures/'.$item['art'].'/');
            while (false !== ($file = readdir($handle))) if ($file != "." && $file != "..") unlink($path.'pictures/'.$item['art'].'/'.$file);
            closedir($handle);
            rmdir($path.'pictures/'.$item['art'].'/');
         }
      }
      return true;
   }
   
   function doBeforeUpdateData() {
    $thisUrl = Utils::Translit($this->table->data['href']);
    if (!$thisUrl) $thisUrl = Utils::Translit($this->table->data['name']);
    $this->table->SetValue('href', $thisUrl);
    return;
   }

   function doBeforeRun() {
      if (IS_CATALOG !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'ord');
         Site::SetSession($this->name."-order", 'asc');
      }
   }
}
?>