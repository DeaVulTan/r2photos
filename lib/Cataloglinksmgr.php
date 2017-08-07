<?php
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
      unset( $links );

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
            'get_names_func'=> 'GetNames',
        $this->AddType( array( 'id' => 1, 'name' => 'photographers', 'title' => 'Фотограф', 'dbTable' => 'photographers', 'dbTableClass' => 'Photographers' ) );
        $this->AddType( array( 'id' => 2, 'name' => 'catalog', 'title' => 'Раздел каталога', 'dbTable' => 'catalog', 'dbTableClass' => 'Catalog' ) );
        $this->AddType( array( 'id' => 3, 'name' => 'items', 'title' => 'Фотосессия', 'dbTable' => 'items', 'dbTableClass' => 'Items', 'order' => array( 'catalog_id ASC, i.name' => 'ASC' ) ) );
        $this->AddType( array( 'id' => 4, 'name' => 'works', 'title' => 'Вид работы', 'dbTable' => 'works', 'dbTableClass' => 'Works' ) );
    }
        $res = $db->SelectSet( "SELECT * FROM `catalog_links` cl RIGHT JOIN `".$tables[$target_type]['db_table']."` tt on tt.id=cl.target_id WHERE cl.`target_type` = ".$target_type." AND ".( is_array( $item_id ) ? "cl.`item_id` IN (".( implode( ',', $item_id ) ).")" : "cl.`item_id` = ".$item_id )." AND `item_type` = ".$item_type.' ORDER BY cl.ord', $key );