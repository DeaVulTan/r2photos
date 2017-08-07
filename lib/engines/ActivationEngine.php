<?php
include_once(Site::GetParms('tablesPath')."Mainmenu.php");
include_once(Site::GetParms('tablesPath')."Cards.php");
class ActivationEngine {
    var $name; var $parms;
    function ActivationEngine($name, $parms) {
        $this->name = $name;
        $this->parms = $parms;
        $parmsT = array('href' => 'activation');
        if (defined("LANG")) $parmsT['lang'] = LANG;
        $menu = Mainmenu::GetRow($parmsT);
        $this->menu = $menu;
        $this->menuPath = (defined('LANG') && LANG <> 'ru' ? '/'.LANG : '').($this->menu['path'] ? $this->menu['path'] : '/');
        define("MENU_ID", ($this->menu['id'] ? $this->menu['id'] : 0));
        define("MENU_NAME", ($this->menu['name'] ? $this->menu['name'] : 'Новости'));
        define("MENU_PATH", $this->menuPath);
        unset($menu);
   }

    function run() {
        if (!MENU_ID) Site::SetParms('bread-crumbs', array(MENU_NAME));
        if( $this->parms['action'] === 'send' ) return $this->DoAdd();
        return $this->ViewForm();
    }

    function DoAdd() {
        foreach( $_REQUEST as $name => $value ) {
            $_REQUEST[ $name ] = trim( strip_tags( $value ) );
        }
        /* if( !preg_match( '/^[0-9a-z\-]+$/i', $_REQUEST['cardnum'] ) ) {
            $this->ViewForm( array( 'alert' => 'Укажите корректный номер карты' ) );
            die();
        }
        //cart
        $card = Cards::GetRow( array( 'active' => 1, 'cardnum' => $_REQUEST['cardnum'] ) );
        if( !$card['id'] ) {
            sleep( 1 );
            $this->ViewForm( array( 'alert' => 'Карты не существует либо карта неактивна' ) );
            die();
        }
        if( $card['status'] == Cards::STATUS_ACTIVE ) {
            sleep( 1 );
            $this->ViewForm( array( 'alert' => 'Карта уже активирована' ) );
            die();
        }
        if( $card['status'] == Cards::STATUS_OFFLINE ) {
            sleep( 1 );
            $this->ViewForm( array( 'alert' => 'Карты не существует либо карта неактивна' ) );
            die();
        }
        if( $card['status'] == Cards::STATUS_SOLD && $card['idate'] < strtotime( date( 'd.m.Y' ) ) ) {
            sleep( 1 );
            $this->ViewForm( array( 'alert' => 'Срок действия карты истёк '.date( 'd.m.Y', $card['idate'] ) ) );
            die();
        }
        */
        //phone
        if( !preg_match( '/[0-9]{3,}/i', $_REQUEST['phone'] ) ) {
            $this->ViewForm( array( 'alert' => 'Укажите номер телефона' ) );
            die();
        }
        //step 1
        $step = ( int ) $_REQUEST['step'];
        if( !in_array( $step, array( 1, 2 ) ) ) {
            $this->ViewForm( array( 'alert' => 'Внутренняя ошибка' ) );
            die();
        }
        /* if( $step == 1 ) {
            $this->ViewFormStep2();
            die();
        }
        //step 2
        $idateVisit = strtotime( $_REQUEST['idate_visit'] );
        if( $idateVisit < strtotime( '+1 day', strtotime( date( 'd.m.Y' ) ) ) ) {
            $this->ViewFormStep2( array( 'alert' => 'Укажите желаемую дату посещения' ) );
            die();
        }
        if( !preg_match( '/.+/i', $_REQUEST['fio'] ) ) {
            $this->ViewFormStep2( array( 'alert' => 'Укажите ваши фамилию, имя и отчество' ) );
            die();
        }
        $idateBirthday = strtotime( $_REQUEST['idate_birthday'] );
        if( $idateBirthday < strtotime( '-100 years' ) || $idateBirthday > strtotime( '+1 year' ) ) {
            $this->ViewFormStep2( array( 'alert' => 'Укажите дату рождения' ) );
            die();
        }
        if( !preg_match( '/.+@.+\..+/i', $_REQUEST['email'] ) ) {
            $this->ViewFormStep2( array( 'alert' => 'Укажите Ваш E-mail' ) );
            die();
        }
        */
        //
        $_REQUEST['idate'] = time();
        $_REQUEST['idate_visit'] = $idateVisit;
        $_REQUEST['idate_birthday'] = $idateBirthday;
        $_REQUEST['users_id'] = ( int ) UID;
        $_REQUEST['city'] = Utils::CheckCityByIP();
        $activation = Activation::Get( 0, true );
        $activation->SetData( $_REQUEST );
        $activation->StoreRow();

        $form_data = $_REQUEST;
        include_once(Site::GetParms('libPath').'Mail.php');
        $form_data['subject'] = Utils::GetValue('subject_mail_after_add_activation');
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
                        'TO'      => Utils::GetValue('activation_email'),
                        'CONTENT-TYPE' => 'text/html',
                        'SUBJECT' => $form_data['subject']
                     ),
                     Site::GetTemplate('layout', 'mail-common'),
                     $form_data,
                     array_values($mailFiles)
                  );
        $message->send();

        include( Site::GetTemplate( $this->name, 'ok' ) );
        die();
    }//DoAdd

    function ViewForm( $parameters = array() ) {
        $form = $this->CreateForm();
        extract( $parameters );
        $form->assign( $_REQUEST );
        include( Site::GetTemplate( $this->name, 'form-step-1' ) );
        if( Site::GetParms( 'isAjax' ) ) {
            die();
        }
        return true;
    }//ViewForm

    function ViewFormStep2( $parameters = array() ) {
        $form = $this->CreateFormStep2();
        extract( $parameters );
        $form->assign( $_REQUEST );
        include( Site::GetTemplate( $this->name, 'form-step-2' ) );
        if( Site::GetParms( 'isAjax' ) ) {
            die();
        }
        return true;
    }//ViewFormStep2

    function CreateForm() {
        include_once(Site::GetParms('libPath').'Form.php');
        return new Form(
            array(
                new Input('cardnum'),
                new Input('phone'),
                new Input('email'),
                new Input('fio'),
                ),
            array(
                'name' => $this->name.'Form',
                'id' => $this->name.'Form',
                'data-validate-style' => 'alert',
                'action' => $this->menuPath.$this->name.'/send'
            )
        );
    }//CreateForm

    function CreateFormStep2() {
        include_once(Site::GetParms('libPath').'Form.php');
        return new Form(
            array(
                new Input('idate_visit'),
                new Input('fio'),
                new Input('idate_birthday'),
                new Input('email'),
                ),
            array(
                'name' => $this->name.'Form',
                'id' => $this->name.'Form',
                'data-validate-style' => 'alert',
                'action' => $this->menuPath.$this->name.'/send'
            )
        );
    }//CreateFormStep2
}
?>