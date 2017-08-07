<?php
include_once(Site::GetParms('tablesPath')."Users.php");
include_once(Site::GetParms('tablesPath')."Mainmenu.php");
class RegistrationEngine {
   var $name; var $parms;
   function RegistrationEngine($name, $parms) {
        $this->name = $name;
        $this->parms = $parms;
        $parmsT = array('href' => 'registration');
        if (defined("LANG")) $parmsT['lang'] = LANG;
        $menu = Mainmenu::GetRow($parmsT);
        $this->menu = $menu;
        $this->menuPath = (defined('LANG') && LANG <> 'ru' ? '/'.LANG : '').($this->menu['path'] ? $this->menu['path'] : '/');
        define("MENU_ID", ($this->menu['id'] ? $this->menu['id'] : 0));
        define("MENU_NAME", ($this->menu['name'] ? $this->menu['name'] : 'Регистрация'));
        define("MENU_PATH", $this->menuPath);
        unset($menu);
   }

   function run() {
      if (!MENU_ID) Site::SetParms('bread-crumbs', array(MENU_NAME));
      if (!$this->parms['action']) return $this->viewForm();
      else if ($this->parms['action'] == 'add') return $this->doAdd();
      else if ($this->parms['id']) return $this->doAction();
      else if ($this->parms['action'] == 'ok' || $this->parms['action'] == 'also' || $this->parms['action'] == 'end' || $this->parms['action'] == 'del') return $this->viewText();
      else return false;
   }

   function viewForm() {
      $form =& $this->createForm();
      $form->set( 'subscribe', true );
      include(Site::GetTemplate($this->name, 'form'));
      if( Site::GetParms( 'isAjax' ) ) {
        die();
      }
      return true;
   }

   function doAdd() {
      $form =& $this->createForm();
      if ($form->processIfSubmitted() && preg_match("/".mb_strtolower(PROJECT_NAME, 'utf-8')."/", $_SERVER['HTTP_REFERER'])) {
         $temp = array();
         $form_data = $form->get();
         if( !Utils::CheckMouseMove() ) {
            $alert = 'Браузер должен поддерживать cookies!';
            $form->set('password', '');
            $form->set('re_password', '');
            include(Site::GetTemplate($this->name, 'form'));
            if( Site::GetParms( 'isAjax' ) ) {
                die();
            }
            return true;
         }
         if (!preg_match('/.+/', $form_data['fio'])) {
            $alert = 'Укажите Ваше имя!';
            $form->set('password', '');
            $form->set('re_password', '');
            include(Site::GetTemplate($this->name, 'form'));
            if( Site::GetParms( 'isAjax' ) ) {
                die();
            }
            return true;
         }
         if (!preg_match('/^([0-9a-zA-Z_]+)$/', $form_data['password'])) {
            $alert = 'Укажите пароль! Пароль может состоять из цифр, букв латинского алфавита и символа "_"!';
            $form->set('password', '');
            $form->set('re_password', '');
            include(Site::GetTemplate($this->name, 'form'));
            if( Site::GetParms( 'isAjax' ) ) {
                die();
            }
            return true;
         }
         if ($form_data['password'] <> $form_data['re_password']) {
            $alert = 'Пароль и подтверждение пароля не совпадают!';
            $form->set('password', '');
            $form->set('re_password', '');
            include(Site::GetTemplate($this->name, 'form'));
            if( Site::GetParms( 'isAjax' ) ) {
                die();
            }
            return true;
         }
         if (!preg_match('/^[0-9A-Za-z\._-]+\@([0-9a-z_-]+\.)+[a-z]{2,4}$/i', $form_data['email'])) {
            $alert = 'Укажите правильный e-mail!';
            $form->set('email', '');
            $form->set('password', '');
            $form->set('re_password', '');
            include(Site::GetTemplate($this->name, 'form'));
            if( Site::GetParms( 'isAjax' ) ) {
                die();
            }
            return true;
         }
         $userAlso =& Users::GetRow(array('email' => $form_data['email']));
         if ($userAlso['id'] > 0) {
            $alert = 'Пользователь с e-mail <b>'.$form_data['email'].'</b> уже зарегистрирован!';
            $form->set('email', '');
            $form->set('password', '');
            $form->set('re_password', '');
            include(Site::GetTemplate($this->name, 'form'));
            if( Site::GetParms( 'isAjax' ) ) {
                die();
            }
            return true;
         }
         $passMail = $form_data["password"];
         $idMD5 = md5($form_data['email'].":".$form_data['password']);
         unset($form_data["re_password"], $form_data["code"], $form_data['avatar']);
         $form_data["password"] = $idMD5;
         $user =& Users::Get('', true);
         $user->SetData($form_data);
         $user->SetValue('is_active', 0);
         $newId = $user->StoreRow();
         $avatar_uploaded = false;
         if (is_uploaded_file($_FILES['avatar_file']["tmp_name"])) {
            preg_match('/\.([^\.]+)$/', $_FILES['avatar_file']["name"], $m);
            $fileName = "data/image/users/users_".$newId.".".$m[1];
            $adr = Site::GetParms('absolutePath').$fileName;
            $adr_tmp = Site::GetParms('absolutePath')."data/image/users/users_tmp_".$newId.".".$m[1];
            move_uploaded_file($_FILES['avatar_file']["tmp_name"], $adr_tmp);
            if ($size = GetImageSize($adr_tmp)) {
               if ($size[0] <= 110 && $size[1] <= 110) {
                  rename($adr_tmp, $adr);
                  $avatar_uploaded = true;
               }
            }
         }
         if ($avatar_uploaded) {
            $tmp[$newId]['avatar'] = $fileName;
            Users::Update($tmp);
         }
         $form_data["password"] = $passMail;
         include_once(Site::GetParms('libPath').'Mail.php');
         $form_data['subject'] = Utils::GetValue('subject_mail_after_do_registration');
         $mailFiles = array(
            'logo' => Site::GetParms('absolutePath').'image/logo.png',
            'pixel' => Site::GetParms('absolutePath').'image/0.gif'
         );
         foreach ($mailFiles as $k => $v) $form_data[$k] = "cid:".md5($v);
         $form_data['iwh'] = getimagesize($mailFiles['logo']);
         $form_data['bottom_address'] = Utils::GetValue('address_bottom_mail');
         $form_data['bottom_line'] = Utils::GetValue('line_bottom_mail');
         $form_data['menu_path'] = preg_replace('/^\//', '', $this->menuPath);
         $message =& MailMessage::GetFromTemplate(
                     array(
                        'FROM'    => $form_data['email'],
                        'TO'      => Utils::GetValue('users_email'),
                        'CONTENT-TYPE' => 'text/html',
                        'SUBJECT' => $form_data['subject']
                     ),
                     Site::GetTemplate('layout', 'mail-common'),
                     $form_data,
                     array_values($mailFiles)
                  );
         $message->send();
         $form_data['idmd5'] = $idMD5;
         $form_data['self_template'] = 'mail-user';
         $message =& MailMessage::GetFromTemplate(
                     array(
                        'FROM'    => Utils::GetValue('mailer_email'),
                        'TO'      => $form_data['email'],
                        'CONTENT-TYPE' => 'text/html',
                        'SUBJECT' => $form_data['subject']
                     ),
                     Site::GetTemplate('layout', 'mail-common'),
                     $form_data,
                     array_values($mailFiles)
                  );
         $message->send();
         if( $form_data['subscribe'] ) {
            include_once( Site::GetParms('tablesPath')."Subscribers.php" );
            $subscriber = Subscribers::Get( '', true );
            $subscriber->SetValue( 'is_active', 1 );
            $subscriber->SetValue( 'idate', time() );
            $subscriber->SetValue( 'email', $form_data['email'] );
            $subscriber->SetValue( 'is_news', 1 );
            $subscriber->StoreRow();
         }
         if( Site::GetParms( 'isAjax' ) ) {
            $this->parms['action'] = 'ok';
            $this->viewText();
            die();
         }
         return Site::CreateUrl($this->menuPath.$this->name.'/ok');
      }
      else return Site::CreateUrl($this->menuPath.$this->name);
   }

   function doAction() {
      $user =& Users::GetRow(array('idmd5' => $this->parms['id']));
      if (!$user['id']) return false;
      if ($this->parms['action'] == 'active') {
         if ($user['is_active'] == 1) return Site::CreateUrl($this->menuPath.$this->name.'/also');
         else {
            $thisUser =& Users::Get($user['id'], true);
            $thisUser->SetValue('is_active', 1);
            $thisUser->StoreRow();
            //автовход
            $engine = Site::GetEngine( 'login' );
            $_POST['autologin_idmd5'] = $this->parms['id'];
            $engine->run();
            unset( $engine );
            //
            return Site::CreateUrl($this->menuPath.$this->name.'/end');
         }
      }
      else {
         $temp = array($user['id']);
         if (!Users::Delete($temp)) return false;
         return Site::CreateUrl($this->menuPath.$this->name.'/del');
      }
   }

   function viewText() {
      include(Site::GetTemplate($this->name, $this->parms['action']));
      return true;
   }

   function createForm() {
      include_once(Site::GetParms('libPath').'Form.php');
      return new Form(
         array(
               new Input('fio'),
               new Input('password', array('type' => 'password')),
               new Input('re_password', array('type' => 'password')),
               new Input('email'),
               new Input('phones'),
               new Input('birthday'),
               new Checkbox('subscribe'),
              ),
         array(
            'name' => $this->name.'Form',
            'id' => $this->name.'Form',
            'action' => $this->menuPath.$this->name.'/add',
            'data-validate-style' => 'alert'
         )
      );
   }
}
?>
