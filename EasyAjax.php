<?php

/**
 * Plugin Name: Easy Ajax
 * Author: Giuseppe Mazzapica
 * Version: 0.2.0
 * Description: Makes ajax in WordPress ridiculously simple.
 */
/**
 * @package EasyAjax
 * @version 0.2.0
 */
if ( ! defined( 'ABSPATH' ) ) die();

if ( ! function_exists( 'easyajax' ) ) {


    function easyajax( $scope = '', $where = '', $allowed = array() ) {
        $action = ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ? 'admin_init' : 'wp_footer';
        if ( did_action( $action ) ) return;
        if ( ! defined( 'EASYAJAX_URL' ) ) define( 'EASYAJAX_URL', plugins_url( '/', __FILE__ ) );
        if ( ! defined( 'EASYAJAX_PATH' ) ) define( 'EASYAJAX_PATH', plugin_dir_path( __FILE__ ) );
        if ( ! class_exists( '\EasyAjax\Front' ) ) easyajax_load();
        $instance = new \EasyAjax\Front( new \EasyAjax\Proxy );
        $instance->setup( $scope, $where, $allowed );
    }


}


if ( ! function_exists( 'easyajax_load' ) ) {


    function easyajax_load() {
        $files = array('FrontInterface', 'ProxyInterface', 'Front', 'Proxy');
        foreach ( $files as $file )
            require_once EASYAJAX_PATH . 'EasyAjax/' . $file . '.php';
    }


}