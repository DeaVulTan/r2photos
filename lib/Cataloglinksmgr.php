<?php// Управление привязками различных записей в бд из двух таблиц// Необходимо инициализировать все возможные сущности в конструкторе//// v.1.6
//
// Подключение:
//  1) подправить AdminEngine::doChangeList (добавить проверку на удаление связываемых объектов):
/*
        ... после вызова $this->table->DeleteRows($delItems);
        include_once( Site::GetParms( 'libPath' ).'Cataloglinksmgr.php' );
        $linksObj = new Cataloglinksmgr();
        $linksObj->DeleteLinks( $this->table->name, $delItems );
        unset( $linksObj );
*/
//  2) подправить admin/rules.php (страница редактирования привязок тегов требует больше параметров, чем остальные энжины):
//          'cataloglinks-([a-z]+)(_([0-9]+))?(_([0-9]+))?(_([0-9]+))?\.htm' => array('Login' => array(), 'Cataloglinks' => array('action' => '{1}', 'item_type' => '{3}', 'item_id' => '{5}', 'target_type' => '{7}'), 'Layout' => array('path' => 'common')),
//  3) скопировать:
//    - lib/Cataloglinksmgr.php
//    - admin/lib/engines/CataloglinksEngine.php
//    - admin/templates/cataloglinks/*
//
//  Настройка:
//  1) подправить конструктор _этого_ файла (lib/Cataloglinksmgr.php) под свои нужды (добавить/удалить сущности)
//  2) у всех сущностей класс работы с базой должен иметь функцию GetNames (например, News::GetNames), возвращающую названия сущностей:
/*
        static function GetNames( $parms = array(), $limit = array(), $order = array() ) {
          $db = Site::GetDB();
          return $db->SelectSet("SELECT `id`, `name` FROM `news`".
                                 Tags::whereString($parms).Tags::orderString($order).
                                 Tags::limitString($limit), 'id');
        }
*/
//  3) на странице редактирования сущности в админке добавить ссылку на редактирование привязок:
/*
       function getLinksStr( $name, $item ) {
        include_once( Site::GetParms( 'libPath' ).'Cataloglinksmgr.php' );
        $links = new Cataloglinksmgr();
        $type = $links->clType('items');    //привязка к товарам каталога
        $linksTo = $links->getCountLinksFrom($type, $item['id']);
        echo '['.$linksTo[$itemType = $links->clType('news')].' - <a href="cataloglinks-to_'.$type.'_'.$item['id'].'_'.$itemType.'.htm">смотреть</a>]<br />';   //привязка новостей
        unset( $links );
       }
*/
//  4) в классе работы с таблицей сущности добавить колонку со ссылкой на редактирование тегов:
//      '_links' => array('name' => 'Новости', 'function' => 'getLinksStr', 'style' => ' nowrap '),
//  5) выборка id'ов привязанных объектов:
/*
      include_once( Site::GetParms( 'libPath' ).'Cataloglinksmgr.php' );
      $links = new Cataloglinksmgr();
      $newsIds = $links->getItemsLinksTo( $links->clType( 'items' ), $item['id'], $links->clType( 'news' ) );
      unset( $links );*//*//  6) Таблица:    CREATE TABLE `catalog_links` (      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,      `item_type` int(11) NOT NULL,      `item_id` int(11) NOT NULL,      `target_type` int(11) NOT NULL,      `target_id` int(11) NOT NULL,      `ord` int(11) NOT NULL,      PRIMARY KEY (`id`),      KEY `test` (`id`,`item_type`,`item_id`,`target_type`,`target_id`)    ) ENGINE=InnoDB DEFAULT CHARSET=utf8*/
Class Cataloglinksmgr{    var $data;    public function AddType( $attribs = array() )    {
        $parms = $attribs + array(
            'id'            => 0,   //REQUIRED
            'name'          => '',  //REQUIRED
            'title'         => '',  //REQUIRED
            'dbTableClass'  => '',  //REQUIRED: Название класса, работающего с сущностями (News)
            'dbTable'       => '',  //REQUIRED: Таблица (news)
            'tableFieldName'=> 'name',  //Колонка с названием, отображающимся на странице редактирования связей (name)
            'order'         => array( 'name' => 'ASC' ),    //Сортировка( array( 'name' => 'ASC' ) ) (подставляются в table::GetNames)
            'condition'     => false,   //Условия выборки позиций
            'condition_array' => false, //Условия выборки позиций (подставляются в table::GetNames)
            'get_names_func'=> 'GetNames',        );        $this->data[ 'types' ][ $parms['name'] ] = $parms['id'];        $this->data[ 'names' ][ $parms['id'] ] = $parms['title'];        $this->data[ 'info'  ][ $parms['id'] ] = array(            'table'             => $parms['dbTableClass'],            'field'             => $parms['tableFieldName'],            'db_table'          => $parms['dbTable'],            'order'             => $parms['order'],            'condition'         => $parms['condition'],            'condition_array'   => $parms['condition_array'],            'get_names_func'    => $parms['get_names_func'],        );    }    function __construct()    {        $this->data = array();        //описание сущностей
        $this->AddType( array( 'id' => 1, 'name' => 'photographers', 'title' => 'Фотограф', 'dbTable' => 'photographers', 'dbTableClass' => 'Photographers' ) );
        $this->AddType( array( 'id' => 2, 'name' => 'catalog', 'title' => 'Раздел каталога', 'dbTable' => 'catalog', 'dbTableClass' => 'Catalog' ) );
        $this->AddType( array( 'id' => 3, 'name' => 'items', 'title' => 'Фотосессия', 'dbTable' => 'items', 'dbTableClass' => 'Items', 'order' => array( 'catalog_id ASC, i.name' => 'ASC' ) ) );
        $this->AddType( array( 'id' => 4, 'name' => 'works', 'title' => 'Вид работы', 'dbTable' => 'works', 'dbTableClass' => 'Works' ) );        $this->AddType( array( 'id' => 5, 'name' => 'certificates', 'title' => 'Сертификат', 'dbTable' => 'certificates', 'dbTableClass' => 'Certificates' ) );        $this->AddType( array( 'id' => 6, 'name' => 'locations', 'title' => 'Локация', 'dbTable' => 'locations', 'dbTableClass' => 'Locations' ) );
    }    function clData( $type )    {        return $this->data[ $type ];    }    function clType($typeName = false)    {        $types = $this->clData( 'types' );        if( $typeName == false )            return $types;        return ( isset( $types[ $typeName ] ) ? $types[ $typeName ] : 0 );    }//clType    function clName( $type, $giveAll = false )    {        $names = $this->clData( 'names' );        return ( $giveAll ? $names : ( isset( $names[ $type ] ) ? $names[ $type ] : '' ) );    }    function clGetInfo()    {        return $this->clData( 'info' );    }    //возвращает количество ссылок от этого элемента    function getCountLinksFrom( $item_type, $item_id )    {        $db = Site::GetDB();        $res = array();        $types = $this->clType();        $tables = $this->clGetInfo();        foreach( $types as $name => $target_type )            $res[ $target_type ] = $db->SelectValue( "SELECT COUNT(*) FROM `catalog_links` WHERE `item_type` = ".( $item_type )." AND `item_id` = ".( $item_id )." AND `target_type` = ".( $target_type ) );        return $res;    }//getCountLinks    //возвращает привязанные к этому элементу позиции типа $target_type    function getItemsLinksFrom( $target_type, $target_id, $item_type, $limit = false, $isRandom = false, $key = 'id' )    {        $db = Site::GetDB();        $tables = $this->clGetInfo();        $res = $db->SelectSet( "SELECT * FROM `catalog_links` cl LEFT JOIN `".( $tables[ $item_type ]['db_table'] )."` tt on tt.id=cl.item_id WHERE cl.`target_type` = ".( $target_type )." AND ".( is_array( $target_id ) ? "cl.`target_id` IN (".( implode( ',', $target_id ) ).")" : "cl.`target_id` = ".( $target_id ) )." AND cl.`item_type` = ".( $item_type ).' ORDER BY cl.ord'.( $isRandom ? ', MD5(RAND()*NOW())' : '' ).( $limit > 0 ? ' LIMIT '.( $limit ) : '' ), $key );        return $res;    }    function getItemsLinksTo($item_type, $item_id, $target_type, $key = 'id') //возвращает привязанные элементы к    {        $db = Site::GetDB();        $tables = $this->clGetInfo();
        $res = $db->SelectSet( "SELECT * FROM `catalog_links` cl RIGHT JOIN `".$tables[$target_type]['db_table']."` tt on tt.id=cl.target_id WHERE cl.`target_type` = ".$target_type." AND ".( is_array( $item_id ) ? "cl.`item_id` IN (".( implode( ',', $item_id ) ).")" : "cl.`item_id` = ".$item_id )." AND `item_type` = ".$item_type.' ORDER BY cl.ord', $key );        return $res;    }    function getCountLinksTo($target_type, $target_id) //возвращает количество ссылок к этому элементу    {        $db = Site::GetDB();        $res = array();        $types = $this->clType();        foreach($types as $name => $type)            $res[$type] = $db->SelectValue("SELECT COUNT(*) FROM `catalog_links` WHERE `target_type` = ".$target_type." AND `target_id` = ".$target_id." AND `item_type` = ".$type);        return $res;    }//getCountLinks    function clGetItemName($item_type, $item_id)    {        $parts = $this->clGetInfo();        include_once(Site::GetParms('tablesPath').$parts[$item_type]['table'].'.php');        eval('$item = '.$parts[$item_type]['table'].'::GetRow(array("id" => '.$item_id.'));');        if(!count($item))            return false;        return $item[$parts[$item_type]['field']];    }    function clGetLinks($item_type, $item_id, $target_type)    {        $db = Site::GetDB();        $items = $db->SelectSet("SELECT `id`, `target_id`, `ord` FROM `catalog_links` WHERE `item_type` = ".$item_type." AND `item_id` = ".$item_id." AND `target_type` = ".$target_type.' ORDER BY `ord`', 'target_id');        return $items;    }    function clGetLinksFrom($item_type, $target_type, $target_id)    {        $db = Site::GetDB();        $items = $db->SelectSet("SELECT `id`, `item_id` FROM `catalog_links` WHERE `item_type` = ".$item_type." AND `target_id` = ".$target_id." AND `target_type` = ".$target_type, 'item_id');        return $items;    }    //Удаление всех связей  с этим объектом    //Необходимо вызывать из AdnimEngine::doChangeList    // [in]:    //      $dbTable - название таблицы в бд    //      $delItems - массив id'ов объектов    function DeleteLinks( $dbTable, $delItems )    {        $info = $this->clGetInfo();        foreach($info as $id => $item)        {            if($item['db_table'] == $dbTable)            {                $db =& Site::GetDB();                $db->Query("DELETE FROM `catalog_links` WHERE `target_type` = '".$id."' AND `target_id` IN ('".implode("','", $delItems)."');");                $db->Query("DELETE FROM `catalog_links` WHERE `item_type` = '".$id."' AND `item_id` IN ('".implode("','", $delItems)."');");                break;            }        }    }};