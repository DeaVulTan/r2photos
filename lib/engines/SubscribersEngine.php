<?php
include_once(Site::GetParms('tablesPath')."Mainmenu.php");
class SubscribersEngine {
   var $name; var $parms;
   function SubscribersEngine($name, $parms) {
        $this->name = $name;
        $this->parms = $parms;
        $parmsT = array('href' => 'subscribers');
        if (defined("LANG")) $parmsT['lang'] = LANG;
        $menu = Mainmenu::GetRow($parmsT);
        $this->menu = $menu;
        $this->menuPath = (defined('LANG') && LANG <> 'ru' ? '/'.LANG : '').($this->menu['path'] ? $this->menu['path'] : '/');
        define("MENU_ID", ($this->menu['id'] ? $this->menu['id'] : 0));
        define("MENU_NAME", ($this->menu['name'] ? $this->menu['name'] : 'Подписка на рассылку'));
        define("MENU_PATH", $this->menuPath);
        unset($menu);
   }

   function run() {
      if (!MENU_ID) Site::SetParms('bread-crumbs', array(MENU_NAME));
      if ($this->parms['id']) return $this->doAction();
      else if ($this->parms['action'] == 'do') return $this->doSubscribers();
      else if ($this->parms['action']) return $this->viewText();
      else return $this->viewForm();
   }

   function viewForm() {
      JS::enable('site/subscribers');
      include(Site::GetTemplate($this->name, 'form'));
      return true;
   }

   function doSubscribers() {
      if ($_POST['email'] && preg_match("/".mb_strtolower(PROJECT_NAME)."/", $_SERVER['HTTP_REFERER'])) {
         if( !preg_match( '/.+@.+\..+/', $_POST['email'] ) ) {
            if( Site::GetParms( 'isAjax' ) ) {
                Utils::JSONResponse( array(
                    'message' => nl2br( Utils::GetValue( 'text_subscribe_if_not_enter' ) ),
                ) );
            }
            return Site::CreateUrl($this->menuPath.$this->name.'/enter');
         }

         $user =& Subscribers::GetRow(array('email' => trim($_POST['email'])));
         if (!$user) {
            $new =& Subscribers::Get('', true);
            $new->SetValue('email', trim($_POST['email']));
            $new->SetValue('is_active', 0);
            $new->SetValue('is_news', 1);
            $newId = $new->StoreRow();
            $form_data = $_POST;
            $form_data['id'] = $newId;
            include_once(Site::GetParms('libPath').'Mail.php');
            $form_data['subject'] = Utils::GetValue('subject_mail_after_add_subscriber');
            $mailFiles = array(
                'logo' => Site::GetParms('absolutePath').'image/logo.png',
                'pixel' => Site::GetParms('absolutePath').'image/0.gif'
            );
            foreach ($mailFiles as $k => $v) $form_data[$k] = "cid:".md5($v);
            $form_data['iwh'] = getimagesize($mailFiles['logo']);
            $form_data['bottom_address'] = Utils::GetValue('address_bottom_mail');
            $form_data['bottom_line'] = Utils::GetValue('line_bottom_mail');
            $message =& MailMessage::GetFromTemplate(
                        array(
                           'FROM'    => Utils::GetValue('mailer_email'),
                           'TO'      => trim($_POST['email']),
                           'CONTENT-TYPE' => 'text/html',
                           'SUBJECT' => $form_data['subject']
                        ),
                        Site::GetTemplate('layout', 'mail-common'),
                        $form_data,
                        array_values($mailFiles)
                     );
            $message->send();
            $url = Site::CreateUrl($this->menuPath.$this->name.'/want');
            if( Site::GetParms( 'isAjax' ) ) {
                Utils::JSONResponse( array(
                    'message' => '<span class="success">'.nl2br( Utils::GetValue( 'text_subscribe_active_ok' ) ).'</span>',
                ) );
            }
            return $url;
         }
         else {
            if( Site::GetParms( 'isAjax' ) ) {
                Utils::JSONResponse( array(
                    'message' => nl2br( Utils::GetValue( 'text_subscribe_if_also_in_base' ) ),
                ) );
            }
            return Site::CreateUrl( $this->menuPath.$this->name.'/inbase' );
         }
      }
      else {
        if( Site::GetParms( 'isAjax' ) ) {
            Utils::JSONResponse( array(
                'message' => nl2br( Utils::GetValue( 'text_subscribe_if_not_enter' ) ),
            ) );
        }
        return Site::CreateUrl($this->menuPath.$this->name.'/enter');
      }
   }

   function doAction() {
      $user =& Subscribers::GetRow(array('password' => $this->parms['id']));
      if (!$user['id']) return false;
      if ($this->parms['action'] == 'active') {
         if ($user['is_active'] == 1) return Site::CreateUrl($this->menuPath.$this->name.'/also');
         else {
            $thisUser =& Subscribers::Get($user['id'], true);
            $thisUser->SetValue('is_active', 1);
            $thisUser->StoreRow();
            return Site::CreateUrl($this->menuPath.$this->name.'/ok');
         }
      }
      else {
         $temp = array($user['id']);
         if (!Subscribers::Delete($temp)) return false;
         return Site::CreateUrl($this->menuPath.$this->name.'/del');
      }
   }

   function viewText() {
      include(Site::GetTemplate($this->name, $this->parms['action']));
      return true;
   }
}
?>
