<?php
/**
 * Plugin Name: Easy Ajax
 * Author: Giuseppe Mazzapica
 * Version: 0.3.1
 * Description: Makes ajax in WordPress ridiculously simple.
 */
/**
 * @package EasyAjax
 * @version 0.3.1
 */
if ( ! defined( 'ABSPATH' ) ) die();

if ( ! function_exists( 'easyajax' ) ) {


    /**
     * Setup and launch easyajax
     *
     * @param object $scope the object scope that will execute action if given
     * @param string $where 'priv', 'nopriv' or 'both'. Limit actions to logged in users or not
     * @param array $allowed list of allowed actions
     * @return null
     * @access public
     */
    function easyajax( $scope = '', $where = '', $allowed = array () ) {
        $action = ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ? 'admin_init' : 'wp_footer';
        if ( did_action( $action ) ) return;
        if ( ! defined( 'EASYAJAX_URL' ) ) define( 'EASYAJAX_URL', plugins_url( '/', __FILE__ ) );
        if ( ! class_exists( 'EasyAjax\Front' ) ) {
            require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
        }
        $instance = new EasyAjax\Front( new \EasyAjax\Proxy );
        $instance->setup( $scope, $where, $allowed );
    }

}