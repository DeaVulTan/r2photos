<?php
include_once(Site::GetParms('tablesPath')."Mainmenu.php");
class OpinionsEngine {
   var $name; var $parms;
   function OpinionsEngine($name, $parms) {
        $this->name = $name;
        $this->parms = $parms;
        $parmsT = array('href' => 'opinions');
        if (defined("LANG")) $parmsT['lang'] = LANG;
        $menu = Mainmenu::GetRow($parmsT);
        $this->menu = $menu;
        $this->menuPath = (defined('LANG') && LANG <> 'ru' ? '/'.LANG : '').($this->menu['path'] ? $this->menu['path'] : '/');
        define("MENU_ID", ($this->menu['id'] ? $this->menu['id'] : 0));
        define("MENU_NAME", ($this->menu['name'] ? $this->menu['name'] : 'Отзывы'));
        define("MENU_PATH", $this->menuPath);
        unset($menu);
   }

   function run() {
      if (!MENU_ID) Site::SetParms('bread-crumbs', array(MENU_NAME));
      if ($this->parms['add'] == 1) return $this->doAdd();
      else if ($this->parms['action'] == 'ok') return $this->viewOk();
      else if ($this->parms['id'] > 0) return $this->viewOpinion();
      else return $this->viewOpinions();
   }

   function viewOpinions() {
      define("MENU_CRUMBS_LASTLINK", false);
      $page = ($this->parms['page'] ? $this->parms['page'] : 1);
      $numOpinionssOnPage = Utils::GetValue('count_opinions_on_page');
      $parms = array('active' => 1);
      if (defined("LANG")) $parms['lang'] = LANG;
      $count =& Opinions::GetCountRows($parms);
      if ($count > 0) {
         $numPages = ceil($count / $numOpinionssOnPage);
         $opinions =& Opinions::GetRows($parms, array('limit' => $numOpinionssOnPage, 'offset' => (($page - 1) * $numOpinionssOnPage)));
      }
      $form =& $this->createForm();
      include(Site::GetTemplate($this->name, 'list'));
      return true;
   }

   function viewOpinion() {
      define("MENU_CRUMBS_LASTLINK", true);
      $parms = array('active' => 1, 'id' => $this->parms['id']);
      if (defined("LANG")) $parms['lang'] = LANG;
      if (!$opinion =& Opinions::GetRow($parms)) return false;
      if ($this->parms['href'] <> $opinion['href']) return false;
      Site::SetParms('bread-crumbs', array(strip_tags($opinion['text'])));
      include(Site::GetTemplate($this->name, 'one'));
      return true;
   }

   function doAdd() {
      define("MENU_CRUMBS_LASTLINK", true);
      Site::SetParms('bread-crumbs', array('Оставить свой отзыв'));
      $form =& $this->createForm();
      if ($form->processIfSubmitted() && preg_match("/".mb_strtolower(PROJECT_NAME, 'utf-8')."/", $_SERVER['HTTP_REFERER'])) {
         $form_data = $form->get();
        
        //Проверка введенных данных
        if(!preg_match('/^[a-zA-Zа-яА-Я0-9-].*$/', $form_data['name'])){
         $alert = 'Укажите Ваше имя!';
         unset($form_data['name'], $form_data['code']);
         $form->assign($form_data);
         include(Site::GetTemplate($this->name, 'form'));
         return true;
        }
        if(!preg_match('/^[0-9A-Za-z._-]+@([0-9a-z_-]+\.)+[a-z]{2,4}$/', $form_data['email'])){
         $alert = 'Укажите правильный e-mail!';
         unset($form_data['email'], $form_data['code']);
         $form->assign($form_data);
         include(Site::GetTemplate($this->name, 'form'));
         return true;
        }
        if(!preg_match('/^[a-zA-Zа-яА-Я0-9-](.|\n)*$/ui', $form_data['text'])){
         $alert = 'Укажите Ваш отзыв!';
         unset($form_data['text'], $form_data['code']);
         $form->assign($form_data);
         include(Site::GetTemplate($this->name, 'form'));
         return true;
        }
        include_once(Site::GetParms('libPath').'Captcha.php');
        if (!preg_match('/^([0-9]+)$/', $_POST['code']) || !CaptchaImg::GetCodeRight($_POST['code'])) {
         $alert = 'Укажите правильный проверочный код!';
         unset($form_data['code']);
         $form->assign($form_data);
         include(Site::GetTemplate($this->name, 'form'));
         return true;
        }

         unset($form_data['code']);
         foreach ($form_data as $k => $v) $form_data[$k] = strip_tags($v);
         $newOpinions =& Opinions::Get('', true);
         $newOpinions->SetData($form_data);
         $newOpinions->SetValue('href', Utils::Translit($form_data['text']));
         if (defined("LANG")) $newOpinions->SetValue('lang', LANG);
         $newOpinions->StoreRow();
         include_once(Site::GetParms('libPath').'Mail.php');
         $form_data['subject'] = Utils::GetValue('subject_mail_after_add_opinion');
         $mailFiles = array(
            'logo' => Site::GetParms('absolutePath').'data/image/subscribers/logo.gif',
            'pixel' => Site::GetParms('absolutePath').'image/0.gif'
         );
         foreach ($mailFiles as $k => $v) $form_data[$k] = "cid:".md5($v);
         $form_data['iwh'] = getimagesize($mailFiles['logo']);
         $form_data['bottom_address'] = Utils::GetValue('address_bottom_mail');
         $form_data['bottom_line'] = Utils::GetValue('line_bottom_mail');
         $message =& MailMessage::GetFromTemplate(
                     array(
                        'FROM'    => $form_data['email'],
                        'TO'      => Utils::GetValue('opinions_email'),
                        'CONTENT-TYPE' => 'text/html',
                        'SUBJECT' => $form_data['subject']
                     ),
                     Site::GetTemplate('layout', 'mail-common'),
                     $form_data,
                     array_values($mailFiles)
                  );
         $message->send();
         return (defined("LANG") ? URL_ADD : '').$this->menuPath.$this->name.'/ok';
      }
      else return $_SERVER['HTTP_REFERER'];
   }

   function viewOk() {
      define("MENU_CRUMBS_LASTLINK", true);
      Site::SetParms('bread-crumbs', array('Отзыв отправлен'));
      include(Site::GetTemplate($this->name, 'ok'));
      return true;
   }

   function &createForm() {
      include_once(Site::GetParms('libPath').'Form.php');
      return new Form(
               array(
                     new Input('name'),
                     new Input('email'),
                     new TextArea('text'),
                     new Capcha('code'),
                    ),
               array(
                  'name' => 'opinionForm',
                  'id' => 'opinionForm',
                  'data-validate-style' => 'alert',
                  'action' => (defined("LANG") ? URL_ADD : '').$this->menuPath.$this->name.'/add',
               )
            );
   }
}
?>
