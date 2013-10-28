<?php
namespace EasyAjax;


/**
 * EasyAjax\Front class
 *
 * @package EasyAjax
 * @author Giuseppe Mazzapica
 *
 */
class Front implements FrontInterface {


    protected $proxy;


    protected $scope;


    protected $where;


    protected $allowed;


    protected $url;


    static function get_get( $var ) {
        return \filter_input( INPUT_GET, $var, FILTER_SANITIZE_STRING );
    }


    static function get_post( $var ) {
        return \filter_input( INPUT_POST, $var, FILTER_SANITIZE_STRING );
    }


    static function get_request( $var ) {
        $val = \filter_input( INPUT_POST, $var, FILTER_SANITIZE_STRING );
        if ( \is_null( $val ) ) $val = \filter_input( INPUT_GET, $var, FILTER_SANITIZE_STRING );
        return $val;
    }


    function __construct( Proxy $proxy ) {
        $this->proxy = $proxy;
    }


    static function is_ajax() {
        return \defined( 'DOING_AJAX' ) && DOING_AJAX;
    }


    function is_valid_ajax() {
        if ( ! self::is_ajax() ) return false;
        $action = self::get_request( 'action' );
        $nonce = self::get_get( 'eanonce' );
        return ! empty( $action ) && \wp_verify_nonce( $nonce, __FILE__ );
    }


    function setup( $scope = '', $allowed = array(), $where = '' ) {
        $this->scope = \is_object( $scope ) ? $scope : null;
        $this->where = \in_array( $where, array('admin', 'front', 'both') ) ? $where : 'both';
        $allowed = \is_array( $allowed ) ? $allowed : array();
        $this->allowed = (array) \apply_filters( 'easyajax_all_allowed', $allowed );
        $this->dispatch();
    }


    function register() {
        if ( ! $this->is_valid_ajax() ) return;
        $action_array = $this->get_action();
        $action = $action_array[0];
        $translated = $this->where == 'admin' ? 'priv' : 'nopriv';
        $type = ( $this->where == 'both' ) ? $action_array[1] : $translated;
        if ( ! empty( $this->allowed ) && ! \in_array( $action, $this->allowed ) ) return;
        $this->proxy->setup( $action, $this->scope, $type );
    }


    protected function dispatch() {
        if ( $this->is_valid_ajax() ) {
            $this->ajax();
        } else {
            $this->not_ajax();
        }
    }


    protected function ajax() {
        \add_action( 'admin_init', array($this, 'register') );
    }


    protected function not_ajax() {
        $this->url();
        $this->pre_js();
    }


    protected function url() {
        if ( ! empty( $this->url ) ) return;
        $nonce = wp_create_nonce( __FILE__ );
        $cust = \apply_filters( 'easyajax_custom_url', '' );
        $base = \filter_var( $cust, \FILTER_VALIDATE_URL ) ? $cust : \admin_url( 'admin-ajax.php' );
        $url = add_query_arg( array('easyajax' => '1', 'eanonce' => $nonce), $base );
        $this->url = $url;
    }


    protected function pre_js() {
        if ( ! \is_admin() && ($this->where != 'admin' ) ) {
            \add_action( 'wp_enqueue_scripts', array($this, 'js'), 10 );
        } elseif ( \is_admin() && ($this->where != 'front' ) ) {
            \add_action( 'admin_enqueue_scripts', array($this, 'js'), 10 );
        }
    }


    function js() {
        if ( isset( $GLOBALS['wp_scripts']->registered['easyajax'] ) ) return;
        $url = 'js/easyajax.js';
        $path = \untrailingslashit( \ABSPATH ) . \wp_make_link_relative( \EASYAJAX_URL );
        $v = \filemtime( $path . $url );
        wp_enqueue_script( 'easyajax', \EASYAJAX_URL . $url, array('jquery'), $v, 1 );
        wp_localize_script( 'easyajax', 'easy_ajax_vars', array('ajaxurl' => $this->url) );
    }


    protected function get_action() {
        $action = explode( '.', self::get_request( 'action' ) );
        if ( ( $action[0] === 'priv' || $action[0] === 'nopriv' ) && \count( $action ) > 1 ) {
            $type = $action[0];
            \array_unshift( $action );
        } else {
            $type = 'both';
        }
        return array(\implode( '.', $action ), $type);
    }


}


