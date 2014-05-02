<?php
namespace EasyAjax;


/**
 * EasyAjax\Proxy class
 *
 * @package EasyAjax
 * @author Giuseppe Mazzapica
 *
 */
class Proxy implements ProxyInterface {


    protected $action;


    protected $scope;


    protected $type;


    protected $callable;


    function setup( $action, $scope, $type ) {
        error_reporting( 0 );
        $this->action = $action;
        $this->scope = $scope;
        $this->type = $type;
        $this->get_callable();
        if ( ! empty( $this->callable ) && is_callable( $this->callable ) ) $this->actions();
    }


    function proxy() {
        if ( ! (bool) $this->allowed() ) return;
        $out = $this->output();
        if ( ! empty( $out ) ) {
            $this->to_json( $out );
            if ( ! is_scalar( $out ) ) $out = json_encode( $out );
            $out = (string) $out;
            die( $out );
        }
    }


    function output() {
        do_action( 'easyajax_pre_action' );
        do_action( 'easyajax_pre_action_' . $this->action );
        $out = call_user_func( $this->callable );
        do_action( 'easyajax_done_action', $this->action );
        do_action( 'easyajax_done_action_' . $this->action, $out );
        return $out;
    }


    function get_callable() {
        $action = explode( '.', $this->action );
        if ( count( $action ) === 2 ) {
            if ( $action[0]{0} === '!' ) {
                $this->get_method( $action[0], $action[1] );
            } else {
                $this->new_object_metod( $action[0], $action[1] );
            }
        } else {
            $this->get_function( $action[0] );
        }
    }


    protected function allowed() {
        return apply_filters( 'easyajax_allowed', 1, $this->action, $this->scope, $this->type );
    }


    protected function to_json( $out ) {
        if ( (bool) Front::get_request( 'getjson' ) ) {
            wp_send_json( $out );
            die();
        }
    }


    protected function actions() {
        if ( $this->type != 'nopriv' ) {
        	add_action( "wp_ajax_" . $this->action, array($this, 'proxy'), 100 );
		}
        if ( $this->type != 'priv' ){
        	add_action( "wp_ajax_nopriv_" . $this->action, array($this, 'proxy'), 100 );
		}
    }


    protected function get_method( $class, $method ) {
        $check = isset( $GLOBALS[$class] ) && is_object( $GLOBALS[$class] );
        if ( $check && method_exists( $GLOBALS[$class], $method ) ) {
            $this->callable = array($GLOBALS[$class], $method);
        } elseif ( method_exists( $class, $method ) ) {
            $this->callable = array($class, $method);
        }
    }


    protected function new_object_metod( $class, $method ) {
        $class = ltrim( $class, '!' );
        $obj = new $class;
        if ( method_exists( $obj, $method ) ) $this->callable = array($obj, $method);
    }


    protected function get_function( $name ) {
        if ( is_object( $this->scope ) && method_exists( $this->scope, $name ) ) {
            $this->callable = array($this->scope, $name);
        } else {
            if ( function_exists( $name ) ) $this->callable = $name;
        }
    }


}


