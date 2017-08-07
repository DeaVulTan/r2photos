<?php
include_once(Site::GetParms('tablesPath')."Mainmenu.php");
class OrdersphotographerEngine {
   var $name; var $parms;
   function OrdersphotographerEngine($name, $parms) {
        $this->name = $name;
        $this->parms = $parms;
        $parmsT = array('href' => 'ordersphotographer');
        if (defined("LANG")) $parmsT['lang'] = LANG;
        $menu = Mainmenu::GetRow($parmsT);
        $this->menu = $menu;
        $this->menuPath = (defined('LANG') && LANG <> 'ru' ? '/'.LANG : '').($this->menu['path'] ? $this->menu['path'] : '/');
        define("MENU_ID", ($this->menu['id'] ? $this->menu['id'] : 0));
        define("MENU_NAME", ($this->menu['name'] ? $this->menu['name'] : 'Заказ обратного звонка'));
        define("MENU_PATH", $this->menuPath);
        unset($menu);
   }

   function run() {
      if (!MENU_ID) Site::SetParms('bread-crumbs', array(MENU_NAME));
      if ($this->parms['action'] == 'send') return $this->doClose();
      else if ($this->parms['action'] == 'ok') return $this->viewOk();
      else return $this->viewForm();
   }

   function viewForm() {
      if( !empty( $_POST ) ) {
        return $this->doClose();
      }
      $form = $this->createForm();
      $photographerId = ( int ) $this->parms['id'];
      include( Site::GetTemplate( $this->name, ( Site::GetParms( 'isAjax' ) ? 'form-popup' : 'form' ) ) );
      if( Site::GetParms( 'isAjax' ) ) {
        die();
      }
      return true;
   }
   
   function viewFormPopUp() {
      $form = $this->createForm();
      include(Site::GetTemplate($this->name, 'form-popup'));
      return true;
   }
   
   private function doClose(){
     $form = $this->createForm();
     if( $form->processIfSubmitted() ){
       $photographerId = ( int ) $this->parms['id'];
       $formTemplate = Site::GetTemplate( $this->name, ( Site::GetParms( 'isAjax' ) ? 'form-popup' : 'form' ) );
       $form_data = $form->get();
       
       if(!preg_match('/[a-zA-Zа-яА-Я]+/', $form_data['fio'])){
         $alert = 'Укажите Ваше имя!';
         unset($form_data['fio'], $form_data['code']);
         $form->assign($form_data);
         include( $formTemplate );
         die();
        }
   
        if(!preg_match('/[0-9]{3,}/', $form_data['phone'])){
         $alert = 'Укажите телефон!';
         unset($form_data['phone'], $form_data['code']);
         $form->assign($form_data);
         include( $formTemplate );
         die();
        }

       if (!Utils::CheckMouseMove()) {
         $alert = 'Укажите правильный проверочный код!';
         unset($form_data['code']);
         $form->assign($form_data);
         include( $formTemplate );
         die();
       }
            
       include_once(Site::GetParms('libPath').'Mail.php');
       $form_data['subject'] = Utils::GetValue('subject_mail_after_send_orddersphotographer_email');
       $mailFiles = array(
          'logo' => Site::GetParms('absolutePath').'image/logo.png',
          'pixel' => Site::GetParms('absolutePath').'image/0.gif'
       );

       $insert = array(
        array(
            'idate' => time(),
            'photographer_id' => $form_data['photographer_id'],
            'fio' => $form_data['fio'],
            'phone' => $form_data['phone'],
            'email' => $form_data['email'],
            'text' => $form_data['text'],
        ),
       );
       Ordersphotographer::Save( $insert );

       foreach ($mailFiles as $k => $v) $form_data[$k] = "cid:".md5($v);
       $form_data['iwh'] = getimagesize($mailFiles['logo']);
       $form_data['bottom_address'] = Utils::GetValue('address_bottom_mail');
       $form_data['bottom_line'] = Utils::GetValue('line_bottom_mail');
       $form_data['photographer'] = Photographers::GetRow( array( 'id' => $form_data['photographer_id'] ) );
       $message = MailMessage::GetFromTemplate(
                     array(
                        'FROM'    => Utils::GetValue('mailer_email'),
                        'TO'      => Utils::GetValue('photographer_email'),
                        'CONTENT-TYPE' => 'text/html',
                        'SUBJECT' => $form_data['subject']
                     ),
                     Site::GetTemplate('layout', 'mail-common'),
                     $form_data,
                     array_values($mailFiles)
                  );
         $message->send();
       return $this->menuPath.$this->name.'/ok';
     }
     
   }

   function viewOk() {
      include(Site::GetTemplate($this->name, 'ok'));
      if( Site::GetParms( 'isAjax' ) ) {
        die();
      }
      return true;
   }

   function createForm() {
      include_once(Site::GetParms('libPath').'Form.php');
      return new Form(
         array(
               new Input('photographer_id'),
               new Input('fio'),
               new Input('phone'),
               new Input('email'),
               new TextArea('text'),
              ),
         array(
         'name' => $this->name.'Form',
         'id' => $this->name.'Form',
         'data-validate-style' => 'alert',
         'action' => $this->menuPath.$this->name.'/send'
        )
      );
   }
}
?>
