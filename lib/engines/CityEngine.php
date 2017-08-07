<?php
class CityEngine {
    var $name; var $parms;
    function CityEngine( $name, $parms ) {
        $this->name = $name;
        $this->parms = $parms;
    }

    function run() {
        switch( $this->parms['action'] ) {
            case 'find': return $this->DoSearch();
            case 'change': return $this->DoChange();
        }
        return false;
    }//run

    private function DoSearch() {
        $name = trim( $_REQUEST['name'] );
        if( empty( $name ) ) {
            Utils::JSONResponse( array( 'success' => false ) );
        }
        $cityList = City::GetRows( array( 'active' => 1, 'like' => $name ) );
        Utils::JSONResponse( array(
            'success' => true,
            'data' => $cityList,
        ) );
    }//DoSearch

    private function DoChange() {
        $name = trim( $_REQUEST['name'] );
        $city = City::GetRow( array( 'name' => $name ) );
        if( !$city['id'] ) {
            return false;
        }
        Utils::SetCustomCity( $city['name'] );
        Utils::JSONResponse( array( 'success' => true ) );
    }//DoChange
}
?>