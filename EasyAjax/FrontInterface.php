<?php
namespace EasyAjax;

/**
 * EasyAjax\FrontInterface interface
 *
 * @package EasyAjax
 * @author Giuseppe Mazzapica
 *
 */
interface FrontInterface {
    
    function __construct( Proxy $proxy );
    
    static function get_get( $var );
    
    static function get_post( $var );
    
    static function is_ajax();
    
    function is_valid_ajax();
    
    function setup( $scope = '', $where = '', $allowed = array() );
    
    function register();

    
}