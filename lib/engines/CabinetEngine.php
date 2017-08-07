<?php
include_once(Site::GetParms('tablesPath')."Users.php");
include_once(Site::GetParms('tablesPath')."Mainmenu.php");
include_once(Site::GetParms('tablesPath')."Orders.php");
include_once(Site::GetParms('tablesPath')."Orders.php");
class CabinetEngine {
   var $name; var $parms;
   function CabinetEngine($name, $parms) {
        $this->name = $name;
        $this->parms = $parms;
        $parmsT = array('href' => 'cabinet');
        if (defined("LANG")) $parmsT['lang'] = LANG;
        $menu = Mainmenu::GetRow($parmsT);
        $this->menu = $menu;
        $this->menuPath = (defined('LANG') && LANG <> 'ru' ? '/'.LANG : '').($this->menu['path'] ? $this->menu['path'] : '/');
        define("MENU_ID", ($this->menu['id'] ? $this->menu['id'] : 0));
        define("MENU_NAME", ($this->menu['name'] ? $this->menu['name'] : 'Личный кабинет'));
        define("MENU_PATH", $this->menuPath);
        unset($menu);
   }

   function run() {
      //Для хлебных крошек
      $breadCrumbsName = MENU_NAME;
      if ($this->parms['action'] == 'login' || ($this->parms['action'] == 'cabinet' && $_POST['logon_login'] && $_POST['logon_passwd'] && (!defined("UID") || (defined("UID") && !UID)))) $breadCrumbsName = 'Авторизация';
      else if (in_array($this->parms['action'], array('forgot', 'forgotsearch', 'forgot/ok'))) $breadCrumbsName = 'Забыли пароль?';
      Site::SetParms('bread-crumbs', array($breadCrumbsName));
      //----------------//
      if (defined("UID") && UID > 0) {
         if ($this->parms['action'] == 'cabinet') return $this->viewMain();
         else if ($this->parms['action'] == 'form') return $this->viewForm();
         else if ($this->parms['action'] == 'change') return $this->doChange();
         else if ($this->parms['action'] == 'orders') return $this->viewOrders();
         else if ($this->parms['action'] == 'discount') return $this->viewDiscount();
         else if ($this->parms['action'] == 'subscribe') return $this->viewSubscribe();
         else if ($this->parms['action'] == 'cabinet/ok') return $this->viewOk();
         else if ($this->parms['action'] == 'changepassword') return $this->doChangePassword();
      }
      if ($this->parms['action'] == 'login') return $this->viewLogin();
      else if ($this->parms['action'] == 'forgot') return $this->viewForgotForm();
      else if ($this->parms['action'] == 'forgotsearch') return $this->doSearchPassw();
      else if ($this->parms['action'] == 'forgot/ok') return $this->viewForgotOk();
      else if ($this->parms['action'] == 'cabinet' && $_POST['logon_login'] && $_POST['logon_passwd']) return $this->viewBadLogin();
      else return Site::CreateUrl('login');
   }

   function viewMain() {
      if( Site::GetParms( 'isAjax' ) ) {
        Utils::JSONResponse(
            array(
                'success' => true,
                'redirect' => '/cabinet',
            )
        );
      }
      include( Site::GetTemplate( $this->name, 'main' ) );
      return true;
   }//viewMain

   function viewOrders() {

      $orders = Orders::GetRowsByUser(array('users_id' => UID), array(), array('idate' => 'desc'));
      include( Site::GetTemplate( $this->name, 'orders' ) );
      return true;
   }//viewOrders

   function viewDiscount() {
      include( Site::GetTemplate( $this->name, 'discount' ) );
      return true;
   }//viewDiscount

   function viewSubscribe() {
      include( Site::GetTemplate( $this->name, 'subscribe' ) );
      return true;
   }//viewSubscribe

   function viewForm() {
      if( Site::GetParms( 'isAjax' ) ) { //удачная авторизация
        Utils::JSONResponse( array( 'success' => true, 'redirect' => '/cabinet' ) );
      }
      $user =& Users::GetRow(array('id' => UID));
      $form =& $this->createForm();
      $form->assign($user);
      $form->set('old_email', $user['email']);
      $form->set('old_nikname', $user['nikname']);
      Site::SetSession( 'r2photos-site-error', false );
      include(Site::GetTemplate($this->name, 'form'));
      return true;
   }

   function doChange() {
      $user =& Users::GetRow(array('id' => UID));
      $form =& $this->createForm();
      if ($form->processIfSubmitted() && preg_match("/".mb_strtolower(PROJECT_NAME, 'utf-8')."/", $_SERVER['HTTP_REFERER'])) {
         $form_data = $form->get();
         foreach ($form_data as $k => $v) $form_data[$k] = strip_tags($v);
         if ($form_data['email'] <> $form_data['old_email']) {
            $userAlso =& Users::GetRow(array('email' => $form_data['email']));
            if ($userAlso['id'] > 0) {
               $alert = 'Пользователь с e-mail <b>'.$form_data['email'].'</b> уже зарегистрирован!';
               $form->set('email', '');
               include(Site::GetTemplate($this->name, 'form'));
               return true;
            }
         }
         if ($form_data['nikname'] <> $form_data['old_nikname']) {
            $userAlso =& Users::GetRow(array('nikname' => $form_data['nikname']));
            if ($userAlso['id'] > 0) {
               $alert = 'Пользователь с ником <b>'.$form_data['nikname'].'</b> уже зарегистрирован!';
               $form->set('nikname', '');
               include(Site::GetTemplate($this->name, 'form'));
               return true;
            }
         }
         $oldEmail = $form_data['old_email'];
         unset($form_data['refer'], $form_data['old_email'], $form_data['old_nikname'], $form_data['avatar']);
         $userObj =& Users::Get(UID, true);
         $userObj->SetData($form_data);
         $userObj->StoreRow();
         $avatar_uploaded = false;
         if (is_uploaded_file($_FILES['avatar_file']["tmp_name"])) {
            preg_match('/\.([^\.]+)$/', $_FILES['avatar_file']["name"], $m);
            $fileName = "data/image/users/users_".UID.".".$m[1];
            $adr = Site::GetParms('absolutePath').$fileName;
            $adr_tmp = Site::GetParms('absolutePath')."data/image/users/users_tmp_".UID.".".$m[1];
            move_uploaded_file($_FILES['avatar_file']["tmp_name"], $adr_tmp);
            if ($size = GetImageSize($adr_tmp)) {
               if ($size[0] <= 110 && $size[1] <= 110) {
                  rename($adr_tmp, $adr);
                  $avatar_uploaded = true;
               }
            }
         }
         if ($avatar_uploaded) {
            $tmp[UID]['avatar'] = $fileName;
            Users::Update($tmp);
         }
         $newPassword = strip_tags( $_REQUEST['password_change'] );
         $passwordChanged = false;
         /* Пользователь сменил почту, отправляем новый пароль и меняем куки */
         $nameHash = mb_strtolower(PROJECT_NAME, 'utf-8').'_hash';
         if ($form_data['email'] <> $oldEmail) {
            if ($this->changePass(UID, empty( $newPassword ) ? false : $newPassword)) {
                $userN = Users::GetRow(array('id' => UID));
                setcookie($nameHash, $userN['password']);
                $passwordChanged = true;
            }
         }
         if( !$passwordChanged && !empty( $newPassword ) ) { //change password
            $md5 = $this->changePass( UID, $newPassword );
         }
         return Site::CreateUrl($this->menuPath.$this->name.'/ok'.( $md5 === false ? '' : '?'.http_build_query( array( 'autologin_idmd5' => $md5, 'refer' => $this->menuPath.$this->name.'/ok' ) ) ));
      }
      else return Site::CreateUrl($this->menuPath.$this->name);
   }

   function viewOk() {
      Site::SetSession( 'r2photos-site-error', false );
      include(Site::GetTemplate($this->name, 'ok'));
      return true;
   }

   function createForm() {
      include_once(Site::GetParms('libPath').'Form.php');
      return new Form(
         array(
               new Input('name'),
               new Input('fio'),
               new Input('phones'),
               new Input('address'),
               new Input('email'),
               new Input('nikname'),
               new Upload('avatar'),
               new TextArea('sig'),
               new Input('old_email', array('type' => 'hidden')),
               new Input('old_nikname', array('type' => 'hidden')),
              ),
         array(
            'name' => $this->name.'Form',
            'id' => $this->name.'Form',
            'action' => URL_ADD.$this->menuPath.$this->name.'/change',
            'enctype' => 'multipart/form-data',
            'data-validate-style' => 'alert'
         )
      );
   }

   function viewLogin($isMain = false) {
      $form =& $this->createFormLogin();
      //получаем ссылки $AutVK, $AuthFB и т.д. на авторизацию через соцсети
      if (Site::GetParms('social_authorization')) {
        $engine = Site::GetEngine('social');
        extract($engine->GetLinks());
        unset($engine);
      }
      $errorInfo = unserialize( Site::GetSession( 'r2photos-site-error' ) );
      if( !empty( $errorInfo['error'] ) ) {
          $form->set( 'logon_login', $errorInfo['data']['logon_login'] );
      }
      Site::SetSession( 'r2photos-site-error', false );
      include( Site::GetTemplate( $this->name, 'login'.( $isMain ? '-main' : '' ) ) );
      if( Site::GetParms( 'isAjax' ) ) {
        die();
      }
      return true;
   }

   function &createFormLogin() {
      include_once(Site::GetParms('libPath').'Form.php');
      return new Form(
         array(
            new Input('logon_login'),
            new Input('logon_passwd', array('type' => 'password')),
         ),
         array(
            'name' => 'logonForm',
            'id' => 'logonForm',
            'action'   => URL_ADD.$this->menuPath.$this->name,
            'data-validate-style' => 'alert'
         )
      );
   }

   function viewForgotForm() {
      $form =& $this->createFormForgot();
      include(Site::GetTemplate($this->name, 'forgot'));
      if( Site::GetParms( 'isAjax' ) ) {
        die();
      }
      return true;
   }

   function &createFormForgot() {
      include_once(Site::GetParms('libPath').'Form.php');
      return new Form(
         array(new Input('login_forgot')),
         array(
            'name' => 'forgotForm',
            'id' => 'forgotForm',
            'action'   => URL_ADD.$this->menuPath.$this->name.'/forgotsearch',
            'data-validate-style' => 'alert'
         )
      );
   }

   function doSearchPassw() {
      $form =& $this->createFormForgot();
      if ($form->processIfSubmitted()) {
         if( !preg_match( '/.+@.+\..+/', $_REQUEST['login_forgot'] ) ) {
            $alert = 'Укажите корректный адрес e-mail';
            $form->set('login_forgot', '');
            include(Site::GetTemplate($this->name, 'forgot'));
            if( Site::GetParms( 'isAjax' ) ) {
                die();
            }
            return true;
         }

         $data = $form->get();
         foreach ($data as $k => $v) $data[$k] = strip_tags($v);
         if ($user =& Users::GetRow(array('email' => $data['login_forgot']))) {
            if ($this->changePass($user['id'])) {
               if( Site::GetParms( 'isAjax' ) ) {
                $this->viewForgotOk();
                die();
               }
               return Site::CreateUrl($this->menuPath.'forgot/ok');
            }
         } else {
            $alert = 'Пользователь не найден';
            include(Site::GetTemplate($this->name, 'forgot'));
            if( Site::GetParms( 'isAjax' ) ) {
                die();
            }
            return true;
         }
      }
      Site::SetSession( 'r2photos-forgot-error', serialize( array( 'error' => 'Укажите корректный e-mail', 'data' => $_REQUEST ) ) );
      return Site::CreateUrl($this->menuPath.'forgot');
   }

   function doChangePassword() {
      if ($md5 = $this->changePass(UID)) {
         return Site::CreateUrl($this->menuPath.'forgot/ok?autologin_idmd5='.( $md5 ).'&refer=test');
      }
      else return false;
   }

   function changePass($id, $customPassword = false) {
      if ($form_data =& Users::GetRow(array('id' => $id))) {
         $nameHash = strtolower(PROJECT_NAME).'_hash';
         setcookie($nameHash, '', 0);
         $passMail = ( $customPassword === false ? Utils::RandString( 8 ) : $customPassword );
         $upd[$id]['password'] = $md5 = md5($form_data['email'].":".$passMail);
         Users::Update($upd);
         include_once(Site::GetParms('libPath').'Mail.php');
         $form_data['password'] = $passMail;
         $form_data['subject'] = Utils::GetValue('subject_mail_after_forgot_search');
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
                        'TO'      => $form_data['email'],
                        'CONTENT-TYPE' => 'text/html',
                        'SUBJECT' => $form_data['subject']
                     ),
                     Site::GetTemplate('layout', 'mail-common'),
                     $form_data,
                     array_values($mailFiles)
                  );
         $message->send();
         return $md5;
      }
      else return false;
   }

   function viewForgotOk() {
      include(Site::GetTemplate($this->name, 'forgot-ok'));
      return true;
   }

   function viewBadLogin() {
      $form =& $this->createFormLogin();
      $text = trim(Utils::GetValue('text_before_bad_login_form'));
      //получаем ссылки $AutVK, $AuthFB и т.д. на авторизацию через соцсети
      if (Site::GetParms('social_authorization')) {
        $engine = Site::GetEngine('social');
        extract($engine->GetLinks());
        unset($engine);
      }
      include(Site::GetTemplate($this->name, 'bad-login'));
      return true;
   }

   function viewLoginString() {
      $user =& Users::Get(UID, true);
      include(Site::GetTemplate($this->name, 'string'));
      return true;
   }
}
?>
