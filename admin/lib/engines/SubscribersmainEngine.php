<?php
include_once(Site::GetParms('tablesPath')."News.php");
class SubscribersmainEngine extends AdminEngine {
  function SubscribersmainEngine($name, $parms) { $this->AdminEngine($name, $parms); }

  function GetCountItems($parms = array()) {
    $parms['like'] = Site::GetSession($this->name."-like");
    return Subscribersmain::GetCountRows($parms);
  }

  function GetItems($parms = array(), $limit = array(), $order = array()) {
    $parms['like'] = Site::GetSession($this->name."-like");
    return Subscribersmain::GetRows($parms, $limit, array('id' => 'ASC'));
  }

  function doBeforeChangeList() {
    if (is_array($_POST['deletedItem']) && sizeOf($_POST['deletedItem']) > 0 && $_POST['_del_x'] && $_POST['_del_y']) {
      $db =& Site::GetDB();
      foreach ($_POST['deletedItem'] as $k => $v) {
        $db->Query("DELETE FROM subscribers_cont WHERE subscribers_main_id='".$k."'");
        $db->Query("DELETE FROM subscribers_bans WHERE subscribers_main_id='".$k."'");
      }
    }
  }

  function selfRun() {
    if ($this->parms['action'] == 'filter') {
      Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
      return Site::CreateUrl($this->name.'-list');
    }
    if ($this->parms['action'] == 'filterdates') {
      if (preg_match('/^([0-9]{2})\.([0-9]{2})\.([0-9]{4})$/', $_POST['date_from'], $m1)) Site::SetSession('date_from', $_POST['date_from']);
      else Site::SetSession('date_from', date("d.m.Y", (time() - 3600*24*7)));
      if (preg_match('/^([0-9]{2})\.([0-9]{2})\.([0-9]{4})$/', $_POST['date_to'], $m2)) Site::SetSession('date_to', $_POST['date_to']);
      else Site::SetSession('date_to', date("d.m.Y"));
      return Site::CreateUrl($this->name.'-editself_'.$_POST['itemIdEdit']);
    }
    $sub =& Subscribersmain::Get($this->values[0], true);
    if ($this->parms['action'] == 'editself') {
      if (is_object($this->table)) {
        $dateFromStr = (Site::GetSession('date_from') ? Site::GetSession('date_from') : date("d.m.Y", (time() - 3600*24*7)));
        preg_match('/^([0-9]{2})\.([0-9]{2})\.([0-9]{4})$/', $dateFromStr, $m1);
        $dateFrom = mktime(0, 0, 0, $m1[2], $m1[1], $m1[3]);
        $dateToStr = (Site::GetSession('date_to') ? Site::GetSession('date_to') : date("d.m.Y"));
        preg_match('/^([0-9]{2})\.([0-9]{2})\.([0-9]{4})$/', $dateToStr, $m2);
        $dateTo = mktime(0, 0, 0, $m2[2], $m2[1], $m2[3]);
        $this->parms['newsAll'] =& News::GetRows(array(/*'active' => 1, */'date_from' => $dateFrom, 'date_to' => $dateTo));
        if ($this->values[0]) {
          $this->itemId = $this->values[0];
          $this->table->SetObjectData($this->itemId);
          if (is_object($this->table->GetFormObject())) {
            $this->form =& $this->table->GetFormObject();
            $this->form->setParm('action', Site::CreateUrl($this->name.'-changeself', $this->values));
          }
          $item =& Subscribersmain::GetRow(array('id' => $this->values[0]));
          $this->parms['news'] =& Subscriberscont::GetSubsIds(array('subscribers_main_id' => $this->values[0], 'subs_type' => 'n'));
        }
        else {
          if (is_object($this->table->GetFormObject())) {
            $this->form =& $this->table->GetFormObject();
            $this->form->setParm('action', Site::CreateUrl($this->name.'-changeself', $this->values));
          }
        }
        return $this->viewEdit();
      }
      else return false;
    }
    else if ($this->parms['action'] == 'changeself') {
      if (is_object($this->table)) {
        if ($this->values[0]) {
          $this->itemId = $this->values[0];
          $this->table->SetObjectData($this->itemId);
        }
        if (is_object($this->table->GetFormObject())) $this->form =& $this->table->GetFormObject();
        $form = $this->form;
        if (!$form->process()) return false;
        $this->table->SetData($form->get());
        $newId = $this->table->StoreRow();
        if (!$this->values[0]) {
          $save = array();
          $i = 1;
          if (sizeOf($_POST['news']) > 0) foreach ($_POST['news'] as $idN => $itemN) {
            $save[$i]['subscribers_main_id'] = $newId;
            $save[$i]['subs_id'] = $idN;
            $save[$i]['subs_type'] = 'n';
            $i ++;
          }
          if (sizeOf($save) > 0) Subscriberscont::Save($save);
        }
        else {
          $newId = $this->values[0];
          $this->parms['news'] = Subscriberscont::GetSubsIds(array('subscribers_main_id' => $this->values[0], 'subs_type' => 'n'));
          $del = array();
          $save = array();
          $i = 1;
          if (sizeOf($_POST['news']) > 0) {
            if (sizeOf($this->parms['news']) > 0) foreach ($this->parms['news'] as $idO => $itemO) if (!array_key_exists($itemO, $_POST['news'])) $del[] = $idO;
            foreach ($_POST['news'] as $idN => $itemN) if (!in_array($idN, $this->parms['news'])) {
              $save[$i]['subscribers_main_id'] = $newId;
              $save[$i]['subs_id'] = $idN;
              $save[$i]['subs_type'] = 'n';
              $i ++;
            }
          }
          else {
            if (sizeOf($this->parms['news']) > 0) foreach ($this->parms['news'] as $idO => $itemO) $del[] = $idO;
          }
          if (sizeOf($del) > 0) Subscriberscont::Delete($del);
          if (sizeOf($save) > 0) Subscriberscont::Save($save);
        }
        return Site::CreateUrl($this->name.'-editself_'.($this->values[0] ? $this->values[0] : $newId));
      }
      else return false;
    }
    else if ($this->parms['action'] == 'editform') {
      if (is_object($this->table)) {
        $this->itemId = $this->values[0];
        $this->table->SetObjectData($this->itemId);
        if (is_object($this->table->GetFormObject())) {
          $this->form =& $this->table->GetFormObject();
          $this->form->setParm('action', Site::CreateUrl($this->name.'-changeform', $this->values));
        }
        $rubr = array('n' => 'Новости');
        $item =& Subscribersmain::GetRow(array('id' => $this->values[0]));
        $this->parms['news'] =& Subscriberscont::GetRows(array('subscribers_main_id' => $this->values[0]));
        if (sizeOf($this->parms['news']) > 0) foreach ($this->parms['news'] as $idN => $itemN) {
          if ($itemN['subs_type'] == 'n') $this->parms['news'][$idN]['new'] =& News::GetRow(array('id' => $itemN['subs_id']));
        }
        $form = $this->form;
        $form->assign($this->table->GetData());
        include(Site::GetTemplate($this->name, 'editform'));
        return true;
      }
      else return false;
    }
    else if ($this->parms['action'] == 'changeform') {
      $upd = array();
      if (sizeOf($_POST['sort']) > 0) foreach ($_POST['sort'] as $idN => $oneN) $upd[$idN]['ord'] = $oneN;
      if (sizeOf($upd) > 0) Subscriberscont::Update($upd);
      $upd2 = array();
      $upd2[$this->values[0]]['theme'] = $_POST['theme'];
      Subscribersmain::Update($upd2);
      return Site::CreateUrl($this->name.'-editform_'.$this->values[0]);
    }
    else if ($this->parms['action'] == 'addbaner') {
      if (sizeOf($_FILES['baner']) > 0) {
        if (is_uploaded_file($_FILES['baner']['tmp_name'])) {
          move_uploaded_file($_FILES['baner']['tmp_name'], Site::GetParms('absolutePath')."data/image/".$_FILES['baner']['name']);
          chmod(Site::GetParms('absolutePath')."data/image/".$_FILES['baner']['name'], 0644);
          $tmp = array();
          $save = array();
          $tmp['subscribers_main_id'] = $this->values[0];
          $tmp['picture'] = "data/image/".$_FILES['baner']['name'];
          $tmp['link'] = (trim($_POST['link']) ? trim($_POST['link']) : '');
          $tmp['ord'] = (trim($_POST['ord']) ? trim($_POST['ord']) : 0);
          $save[] = $tmp;
          Subscribersbans::Save($save);
        }
      }
      return Site::CreateUrl($this->name.'-editform_'.$this->values[0]);
    }
    else if ($this->parms['action'] == 'delbaner') {
      $ban =& Subscribersbans::GetRow(array('id' => $this->values[1]));
      if ($ban) {
        if (file_exists(Site::GetParms('absolutePath').$ban['picture'])) unlink(Site::GetParms('absolutePath').$ban['picture']);
        $tmp = array();
        $tmp[] = $ban['id'];
        Subscribersbans::Delete($tmp);
      }
      return Site::CreateUrl($this->name.'-editform_'.$this->values[0]);
    }
    else if ($this->parms['action'] == 'create') {
      if ($this->values[0]) {
        $mail =& Subscribersmain::GetRow(array('id' => $this->values[0]));
        $this->parms['news'] =& Subscriberscont::GetRows(array('subscribers_main_id' => $this->values[0]));
        if (sizeOf($this->parms['news']) > 0) foreach ($this->parms['news'] as $idN => $itemN) {
          if ($itemN['subs_type'] == 'n') $this->parms['news'][$idN]['new'] =& News::GetRow(array('id' => $itemN['subs_id']));
        }
        $TEXT = '';
        $TEXT .= '<table align="center" width="640px" border="0" cellpadding="0" cellspacing="0">
               <tr><td><h1>'.$mail['theme'].'</h1></td></tr>';
        $bans =& Subscribersbans::GetRows(array('subscribers_main_id' => $this->values[0], 'ord' => 0));
        if (sizeOf($bans) > 0) foreach ($bans as $idB => $itemB) {
          $banName = preg_replace('/^.+\/([^\/]+)$/', '\\1', $itemB['picture']);
          ob_start();
           include(Site::GetTemplate($this->name, 'onebansmail'));
           $TEXT .= ob_get_contents();
          ob_end_clean();
        }
        $TEXT .= '<tr><td>'.$sub->GetValue('text_custom').'<br /></td></tr>';
        $i = 0;
        $curentRubr = '';
        $rubr = array('n' => 'Новости');
        $first = true;
        foreach ($this->parms['news'] as $k => $item) {
          if ($curentRubr <> $rubr[$item['subs_type']]) {
            if (!$first) {
              if ($curentRubr == 'Новости') $TEXT .= '<!-- /NEWS -->'."\n";
            }
            $curentRubr = $rubr[$item['subs_type']];
            if ($curentRubr == 'Новости') $TEXT .= '<!-- NEWS -->'."\n";
            $TEXT .= '<tr><td><h2>'.$curentRubr.'</h2></td></tr>';
          }
          $first = false;
          $HREF = '';
          if ($item['subs_type'] == 'n') $HREF = 'news_'.$item['new']['id'].'.htm';
          ob_start();
           include(Site::GetTemplate($this->name, 'onenewsmail'));
           $TEXT .= ob_get_contents();
          ob_end_clean();
          $i ++;
          $bans =& Subscribersbans::GetRows(array('subscribers_main_id' => $this->values[0], 'ord' => $i));
          if (sizeOf($bans) > 0) {
            foreach ($bans as $idB => $itemB) {
              $banName = preg_replace('/^.+\/([^\/]+)$/', '\\1', $itemB['picture']);
              ob_start();
               include(Site::GetTemplate($this->name, 'onebansmail'));
               $TEXT .= ob_get_contents();
              ob_end_clean();
            }
          }
        }
        if ($curentRubr == 'Новости') $TEXT .= '<!-- /NEWS -->'."\n";
        $TEXT .= '</table>';
        ob_start();
        include(Site::GetTemplate($this->name, 'template'));
        $TEMPLATE = ob_get_contents();
        ob_end_clean();
        $TEMPLATE = preg_replace('/\{THEME\}/', $sub->GetValue('theme'), $TEMPLATE);
        $TEMPLATE = preg_replace('/\{TEXT\}/', $TEXT, $TEMPLATE);
        $sub->SetValue('text', $TEMPLATE);
        $sub->StoreRow();
       }
      return $_GET['action'] == 'view' ? 'subscribersmain-view_'.$this->values[0].'.htm' : Site::CreateUrl($this->name.'-list');
    }
    else if ($this->parms['action'] == 'view') {
      if ($this->values[0]) {
        $item =& Subscribersmain::GetRow(array('id' => $this->values[0]));
        include(Site::GetTemplate($this->name, 'view'));
        return true;
      }
      else return Site::CreateUrl($this->name.'-list');
    }
    else if ($this->parms['action'] == 'sendtest') {
      if ($this->values[0]) {
        $item =& Subscribersmain::GetRow(array('id' => $this->values[0]));
        list($text, $files) = $this->attachFiles($item['text']);

        include_once(Site::GetParms('libPath').'Mail.php');
        $message = new MailMessage(array('FROM' => Utils::GetValue('mailer_email'),
                              'TO' => $sub->GetValue('test_emails'),
                              'CONTENT-TYPE' => 'text/html',
                              'SUBJECT' => $item['theme']),
                          $text, $files);
        $message->send();
      }
      return Site::CreateUrl($this->name.'-list');
    }

    else if ($this->parms['action'] == 'sendall') {
      if ($this->values[0]) {
        $send =& Subscribersmain::Get($this->values[0]);
        $send->SetValue('is_send', 1);
        $send->StoreRow();
      }
      return Site::CreateUrl($this->name.'-list');
    }
  }

  private function attachFiles($text) {
    $text = preg_replace('/src=\"http:\/\/([^\/]*)?\//', 'src="', $text);
    $text = preg_replace('/src=\"\//', 'src="', $text);
    $text = preg_replace('/data\/image\//', '', $text);
    preg_match_all('/src=\"([^\"]+)\"/', $text, $m);
    $files = array();
    $imagesPath = 'data/image/';
    $imagesAbsPath = Site::GetParms('absolutePath').$imagesPath;
    if (count($m[1]) > 0) foreach ($m[1] as $fileName) {
    if (!empty($fileName) && file_exists($imagesAbsPath.$fileName)) {
      $files[] = $imagesAbsPath.$fileName;
      $text = preg_replace('/'.preg_replace('/([\/\.\(\)\#\"\'\[\]\*\^\&\$\!\+\|\?])/', '\\\\\1', $fileName).'/', "cid:".md5($imagesAbsPath.$fileName), $text);
      }
    }
    return array($text, $files);
    }

  function doBeforeRun() {
    if (IS_SUBSCRIBE !== true) return '404.htm';
  }
}