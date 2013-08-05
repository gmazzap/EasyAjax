<?php
/**
 * @package EasyAjax
 * @version 0.1.0
 */
/*
Plugin Name: WP Easy Ajax
*
Author: Giuseppe Mazzapica
Version: 0.1.0
Description: Makes ajax in Wordpress ridiculously simple.
*/


/**
* EasyAjax class
*
* @package EasyAjax
* @author Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
*
*/
class EasyAjax {
	
	
	/**
	* Plugin version
	*
	* @since	0.1.0
	*
	* @var	string
	*/
	protected $version = '0.1.0';
	
	
	/**
	 * The callable name passed via ajax
	 *
	 * @since	0.1.0
	 *
	 * @var	string
	 *
	 */
	static $action;
	
	
	/**
	 * The callable that must be run on ajax request
	 *
	 * @since	0.1.0
	 *
	 * @var	string|array
	 *
	 */
	static $callable;
	
	
	/**
	 * The class that can handle dynamically all ajax request via its methods. Setted via constructor.
	 *
	 * @since	0.1.0
	 *
	 * @var	object
	 *
	 */
	static $scope;
	
	
	/**
	 * The url to use in js function for ajax requests
	 *
	 * @since	0.1.0
	 *
	 * @var	string
	 *
	 */
	var $url;
	
	
	
	/**
	 * Setted to 'admin' or 'front' allow EasyAjax to enqueque js only in backend or frontend respectively. Setted via constructor.
	 *
	 * @since	0.1.0
	 *
	 * @var	string
	 *
	 */
	var $where;
	
	
	
	
	protected static function get_callable() {
		$act_array = explode('.', self::$action);
		if ( count ($act_array) == 2) {
			$class = $act_array[0];
			// Use dynamic method for ajax. the object must be instializated and putted in global scope.
			// Object method must be passed like this: object_variable_name.method_name
			if ( isset($GLOBALS[$class]) && is_object($GLOBALS[$class]) && method_exists($GLOBALS[$class], $act_array[1]) ) {
				self::$callable = array( $GLOBALS[$class], $act_array[1] );
			// Use static method for ajax. Class method must be passed like this: class_name.method_name
			} elseif ( method_exists($class, $act_array[1]) ) {
				self::$callable = array( $class, $act_array[1] );
			} 
		} elseif (  count ($act_array) == 1 ) {
			// Use scope object. just pass the method name in the action param
			if ( is_object(self::$scope) && method_exists(self::$scope, $act_array[0]) ) {
				self::$callable = array( self::$scope, $act_array[0] );
			} else {
				// Use functions. just pass the function name in the action param
				if ( function_exists($act_array[0]) ) self::$callable = $act_array[0];
			}
		}
		if ( empty(self::$callable) ) exit();
	}
	
	
	
	/**
	 * Dispatch ajax request to the appropriate 'callable' after checking for nonce
	 *
	 * @since	0.1.0
	 *
	 * @access	public
	 * @return	null
	 *
	 */
	public static function proxy() {
		// On ajax output any unwanted output, like error or warning messages can break every expectation
		error_reporting(0);
		$out = @call_user_func( self::$callable, $_POST );
		if ( isset($_REQUEST['getjson']) && $_REQUEST['getjson']) self::tojson($out);
		if ( ! is_null($out) ) {
			if ( ! is_scalar($out) ) $out = @json_encode( $out );
		    $out = (string)$out;
		}
		die( $out );
	}
	
	
	
	/**
	 * Output the return of callable as a json document setting http headers
	 *
	 * @since	0.1.0
	 *
	 * @access	public
	 * @param	$out	string|int|array|object|bool|null	the output of callable
	 * @return	null
	 *
	 */
	static function tojson($out) {
		if ( ! empty($out) ) {
			header('Cache-Control: no-cache, must-revalidate');
        	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        	header('Content-type: application/json');
			echo json_encode( $out );
		}
		die();
	}
	
	
	
	/**
	 * Check if the current request is done by ajax
	 *
	 * @since	0.1.0
	 *
	 * @access	public
	 * @return	bool
	 *
	 */
	static function is() {
		return defined('DOING_AJAX');
	}
	
	
	
	/**
	 * Register the action for ajax request
	 *
	 * @since	0.1.0
	 *
	 * @access public
	 * @param string $rawaction ajax action. It can contain 'priv.{action}' to limit action only to logged users.
	 * @return null
	 * @uses add_action
	 */
	function register() {
		$rawaction = $_POST['action'];
		if ( ! is_string($rawaction) || empty($rawaction) || ! self::is() ) return;
		$action = $rawaction ? explode('.', $rawaction) : false;
		if ( ( $action[0] == 'priv' || $action[0] == 'nopriv' ) && count($action)>1 ) {
			$type = $action[0];
			array_unshift($action);
			$action = implode('.', $action);
		} else {
			$action = $rawaction;
			$type = 'both';
		}
		self::$action = $action;
		$check_allowed = false;
		if ( apply_filters('easyajax_check_allowed', $check_allowed) ) {
			$allowed = (array)apply_filters('easyajax_allowed_actions', array());
			if ( ! in_array( self::$action, $allowed ) ) exit();
		}
		if ( ! self::is() || ! wp_verify_nonce($_GET['eanonce'], __FILE__) || empty(self::$action) ) exit();
		self::get_callable();
		if ( $type != 'nopriv' ) add_action( "wp_ajax_" . $action, array(__CLASS__, 'proxy') );
		if ( $type != 'priv' ) add_action( "wp_ajax_nopriv_" . $action, array(__CLASS__, 'proxy') );
	}
	
	
	
	/**
	 * Set the url for the ajax request
	 *
	 * @since	0.1.0
	 *
	 * @access	public
	 * @return	null
	 *
	 */
	function url( ) {
		$url = admin_url('admin-ajax.php') . '?easyajax&eanonce=' .  wp_create_nonce(__FILE__);
		$this->url = $url;
	}
	
	
	
	/**
	 * Register and enqueue js scripts
	 *
	 * @since	0.1.0
	 *
	 * @access	public
	 * @return	null
	 */
	function js( ) {
		wp_register_script( 'easyajax', plugins_url( 'js/easyajax.js', __FILE__ ), array('jquery'), NULL, true );
		wp_enqueue_script( 'easyajax' );
		wp_localize_script( 'easyajax', 'easy_ajax_vars', array('ajaxurl' => $this->url) );
	}
	
	
	
	/**
	 * Add the action that enqueque js. Choosing from admin or front end hook
	 *
	 * @since	0.1.0
	 *
	 * @access	protected
	 * @return	null
	 */
	function prepare_js( ) {
		if ( ! is_admin() && ($this->where != 'admin') ) {
			add_action( 'wp_enqueue_scripts', array($this, 'js'), 10 );
		} elseif ( is_admin() && ($this->where != 'front') ) {
			add_action( 'admin_enqueue_scripts', array($this, 'js'), 10 );
		}
	}


	
	
	/**
	 * Constructor. During an ajax url register the action to callable, during a regular request generate the url and do the js stuff.
	 *
	 * @since	0.1.0
	 *
	 * @access	public
	 * @return	null
	 */
	function __construct( $object_scope = '', $where = '', $allowed = array() ) {
		
		if ( ! defined('ABSPATH') ) die( 'Easy_Ajax class work with WordPress.' );
		
		// blocking EasyAjax is easy!
		if ( ! apply_filters( 'pre_easy_ajax', 1, $_POST, self::is(), $_POST ) ) exit();
		
		if ( self::is() && isset($_POST['action']) && ! empty($_POST['action']) && isset($_GET['easyajax']) && isset($_GET['eanonce']) ) {
			
			if ( ! empty($allowed) ) {
				add_filter('easyajax_check_allowed', function() { return true; }, 9999 );
				add_filter('easyajax_allowed_actions', function() { return $allowed; }, 9999 );
			}
			
			if ( $object_scope && is_object($object_scope) ) self::$scope = $object_scope;
			add_action( 'admin_init', array($this, 'register') );
		
		} elseif ( ! self::is() && ( ( ! is_admin() && ( $where != 'admin' ) ) || ( is_admin() && ( $where != 'front' ) ) ) ) {	
			if( $where ) $this->where = $where;
			add_action( 'init', array($this, 'url'), 20 ); 
			add_action( 'init', array($this, 'prepare_js'), 30 );
		}
	}
}

