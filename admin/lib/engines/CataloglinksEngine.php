<?php
class CataloglinksEngine extends AdminEngine {
  function CataloglinksEngine($name, $parms) { $this->AdminEngine($name, $parms); }

  function GetCountItems($parms = array())
  {
    return Cataloglinks::GetCountRows($parms);
  }

  function GetItems($parms = array(), $limit = array())
  {
    return Cataloglinks::GetRows($parms, $limit);
  }

  function selfRun()
  {
    $this->values = array($this->parms['item_type'], $this->parms['item_id'], $this->parms['target_type']);
    if($this->parms['action'] == 'to')
      return $this->showTo();
    if($this->parms['action'] == 'from')
      return $this->showFrom();
    if($this->parms['action'] == 'changelistto')
      return $this->changelistTo();
    if($this->parms['action'] == 'changelistfrom')
      return $this->changelistFrom();
    if($this->parms['action'] == 'check')
      return $this->checkLinks();
    if($this->parms['action'] == 'deletebroken')
      return $this->checkLinks(true);
    return;
  }//selfRun

  function showTo()
  {
    include_once( Site::GetParms( 'libPath' ).'Cataloglinksmgr.php' );
    $linksObj = new Cataloglinksmgr();
    $item_type = $this->parms['item_type'];
    $item_id = $this->parms['item_id'];
    $target_type = $this->parms['target_type'];
    $sameTypes = ( $item_type == $target_type );
    $item_name = $linksObj->clGetItemName($item_type, $item_id);
    $target_name = $linksObj->clName($target_type);
    $info = $linksObj->clGetInfo();
    $links = $linksObj->clGetLinks($item_type, $item_id, $target_type);
    include_once(Site::GetParms('tablesPath').$info[$target_type]['table'].'.php');
    if(strlen($info[$target_type]['hierarchy']))
      $items = $this->GetHierarchyList($info[$target_type]);
    else
    {
        $isAjax = $info[$target_type]['is_ajax'];
        if($isAjax)
        {
            if(strlen($_REQUEST['like']))
            {
                $like = $_REQUEST['like'];
                $parms = array_merge((is_array($info[$target_type]["condition_array"]) ? $info[$target_type]["condition_array"] : array()), array("like" => $like));
                eval('$items = '.$info[$target_type]['table'].'::'.( $info[$target_type]['get_names_func'] ).'($parms, array(), $info[$target_type]["order"]);');
            }
            else
                $items = array();
        }
        else {
            eval('$items = '.$info[$target_type]['table'].'::'.$info[$target_type]['get_names_func'].'($info[$target_type]["condition_array"], array(), $info[$target_type]["order"]);');
        }
    }
    include(Site::GetTemplate($this->name, 'to'));

    if($_POST['is_ajax'] == 1)
    {
        ob_end_clean();
        header("Content-type: text/html; charset=windows-1251");
        die($itemsContent.' ');
    }
    unset( $linksObj );

    return true;
  }

  function GetHierarchyList($info, $parentId = 0, $prefix = '', $level = 0)
  {
    eval('$tmp = '.$info['table'].'::'.$info['get_names_func'].'(array("'.$info['hierarchy'].'" => "'.$parentId.'") + (is_array($info["condition_array"]) ? $info["condition_array"] : array()));');
    if(count($tmp))
    {
      $items = array();
      foreach($tmp as $id => $item)
      {
        $item['name'] = $prefix . $item['name'];
        $item['level'] = $level;
        $items[$item['id']] = $item;
        $items += $this->GetHierarchyList($info, $item['id'], $prefix.' - ', $level + 1);
      }
    }
    else
      return array();
    return $items;
  }

  function showFrom()
  {
    include_once( Site::GetParms( 'libPath' ).'Cataloglinksmgr.php' );
    $linksObj = new Cataloglinksmgr();
    $item_type = $this->parms['target_type'];
    $target_id = $this->parms['item_id'];
    $target_type = $this->parms['item_type'];
    $target_name = $linksObj->clGetItemName($target_type, $target_id);
    $target_typename = $linksObj->clName($target_type);
    $sameTypes = ( $item_type == $target_type );
    $info = $linksObj->clGetInfo();
    $links = $linksObj->clGetLinksFrom($item_type, $target_type, $target_id);
    include_once(Site::GetParms('tablesPath').$info[$item_type]['table'].'.php');
    eval('$items = '.$info[$item_type]['table'].'::'.$info[$item_type]['get_names_func'].'($info[$item_type]["condition_array"]);');
    include(Site::GetTemplate($this->name, 'from'));
    unset( $linksObj );
    return true;
  }

  function changelistTo()
  {
    if(!$_POST['_change'])
      return false;
    include_once( Site::GetParms( 'libPath' ).'Cataloglinksmgr.php' );
    $linksObj = new Cataloglinksmgr();
    $item_type = $this->parms['item_type'];
    $item_id = $this->parms['item_id'];
    $target_type = $this->parms['target_type'];
    $info = $linksObj->clGetInfo();
    $idsChange = array();
    $idsAdd = $idsDel = $idsMod = array();
    foreach($_POST['targetIds'] as $targetId => $val)
      if($_POST['linkIds'][$targetId] && !$_POST['itemIds'][$targetId]) {
        $idsDel[$_POST['linkIds'][$targetId]] = $_POST['linkIds'][$targetId];
      } else if(!$_POST['linkIds'][$targetId] && $_POST['itemIds'][$targetId]) {//add
        $idsAdd[] = array(
          'item_type' => $item_type,
          'item_id' => $item_id,
          'target_type' => $target_type,
          'target_id' => $targetId,
          'ord' => $_POST['ord'][$targetId],
        );
      } else if($_POST['linkIds'][$targetId]) {//modify
        $idsMod[] = array(
          'id' => $_POST['linkIds'][$targetId],
          'ord' => $_POST['ord'][$targetId],
        );
      }
    $db = Site::GetDB();
    foreach( $idsDel as $id ) {
        $item = $db->SelectRow( "SELECT * FROM `catalog_links` WHERE `id` = ".( $id ) );
        $item = $db->SelectRow( "SELECT * FROM `catalog_links` WHERE `item_type` = ".( $item['target_type'] )." AND `item_id` = ".( $item['target_id'] )." AND `target_type` = ".( $item['item_type'] )." AND `target_id` = ".( $item['item_id'] ) );
        if( $item['id'] ) {
            $idsDel[ $item['id'] ] = $item['id'];
        }
    }
    foreach( $idsAdd as $item ) {
        $idsAdd[] = array(
            'item_type' => $item['target_type'],
            'item_id' => $item['target_id'],
            'target_type' => $item['item_type'],
            'target_id' => $item['item_id'],
        );
    }
    Cataloglinks::Delete($idsDel);
    Cataloglinks::Save($idsAdd);
    if(count($idsMod))
      foreach($idsMod as $id => $item)
        $db->Query("UPDATE `catalog_links` SET `ord` = '".$item['ord']."' WHERE `id` = ".$item['id']);
    unset( $linksObj );
    return Site::CreateUrl(strtolower($info[$item_type]['table']).'-list');
  }

  function changelistFrom()
  {
    if(!$_POST['_change'])
      return false;
    include_once( Site::GetParms( 'libPath' ).'Cataloglinksmgr.php' );
    $linksObj = new Cataloglinksmgr();
    $item_type = $this->parms['target_type'];
    $target_id = $this->parms['item_id'];
    $target_type = $this->parms['item_type'];
    $info = $linksObj->clGetInfo();
    $idsChange = array();
    $idsAdd = $idsDel = array();
    foreach($_POST['itemIds'] as $itemId => $val)
      if($_POST['linkIds'][$itemId] && !$_POST['targetIds'][$itemId])
        $idsDel[$_POST['linkIds'][$itemId]] = $_POST['linkIds'][$itemId];
      else
      if(!$_POST['linkIds'][$itemId] && $_POST['targetIds'][$itemId])
      {//add
        $idsAdd[] = array(
          'item_type' => $item_type,
          'item_id' => $itemId,
          'target_type' => $target_type,
          'target_id' => $target_id,
        );
      }
    $db = Site::GetDB();
    foreach( $idsDel as $id ) {
        $item = $db->SelectRow( "SELECT * FROM `catalog_links` WHERE `id` = ".( $id ) );
        $item = $db->SelectRow( "SELECT * FROM `catalog_links` WHERE `item_type` = ".( $item['target_type'] )." AND `item_id` = ".( $item['target_id'] )." AND `target_type` = ".( $item['item_type'] )." AND `target_id` = ".( $item['item_id'] ) );
        if( $item['id'] ) {
            $idsDel[ $item['id'] ] = $item['id'];
        }
    }
    foreach( $idsAdd as $item ) {
        $idsAdd[] = array(
            'item_type' => $item['target_type'],
            'item_id' => $item['target_id'],
            'target_type' => $item['item_type'],
            'target_id' => $item['item_id'],
        );
    }
    Cataloglinks::Delete($idsDel);
    Cataloglinks::Save($idsAdd);
    unset( $linksObj );
    if(strlen($info[$target_type]['page_to']))
      return Site::CreateUrl(strtolower($info[$target_type]['page_to']).'-list');
    return Site::CreateUrl(strtolower($info[$target_type]['table']).'-list');
  }

  function checkLinks($delete = false)
  {
    $db = Site::GetDB();
    include_once( Site::GetParms( 'libPath' ).'Cataloglinksmgr.php' );
    $linksObj = new Cataloglinksmgr();
    $info = $linksObj->clGetInfo();
    $br = '<br />';
    $existedItems = array();
    $needToDeleteIds = array();
    foreach($info as $type => $item)
      $existedItems[$type] = $db->SelectSet("SELECT `id` FROM `".$item['db_table']."`;", 'id');
    foreach($info as $item_type => $from)
    {
      if(!$delete)
        echo '<h2>Привязки от: '.$linksObj->clName($item_type).' ['.$item_type.']</h2>';
      foreach($info as $target_type => $to)
      {
        if(!$delete)
          echo '<br /><strong>=> '.$linksObj->clName($target_type).'</strong>';
        if($items = $db->SelectSet("SELECT * FROM `catalog_links` WHERE `item_type` = ".$item_type." AND `target_type` = ".$target_type, 'id'))
        {
          $fromIds = $toIds = array();
          foreach($items as $id => $item)
          {
            $fromIds[$item['item_id']] = $item['item_id'];
            $toIds[$item['target_id']] = $item['target_id'];
          }
          if(!$delete)
            echo ' Всего связей: '.count($items);
          $fromDeleted = $toDeleted = 0;
          foreach($items as $id => $item)
          {
            if(!isset($existedItems[$item_type][$item['item_id']]))
            {
              ++$fromDeleted;
              $needToDeleteIds[$id] = $id;
              if(!$delete)
                echo ' [f'.$item['item_id'].']';
            }
            if(!isset($existedItems[$target_type][$item['target_id']]))
            {
              ++$toDeleted;
              $needToDeleteIds[$id] = $id;
              if(!$delete)
                echo ' [t'.$item['target_id'].']';
            }
          }//foreach items
          if(!$delete)
            echo '; Устаревших связей: '.($fromDeleted + $toDeleted).' ('.$fromDeleted.' + '.$toDeleted.')';
        }//if items
      }//foreach to
    }//foreach from
    if(!$delete)
    {
      echo '<br /><br />Всего устаревших связей: '.count($needToDeleteIds);
      echo '<br /><br /><a href="'.$this->name.'-deletebroken.htm">Удалить устаревшие связи</a>';
    }
    else
    if(count($needToDeleteIds))
    {
      $db->Query("DELETE FROM `catalog_links` WHERE `id` IN (".implode(',', $needToDeleteIds).")");
      echo '<br /><br />Удалено связей: '.count($needToDeleteIds);
    }
    else
      echo 'Все связи целы';
    unset( $linksObj );
    return true;
  }//checkLinks
}
?>