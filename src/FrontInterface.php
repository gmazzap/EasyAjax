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


    /**
     * Constructor setup Proxy depency
     *
     * @param EasyAjax\Proxy $proxy
     * @return null
     * @access public
     */
    function __construct( Proxy $proxy );


    /**
     * Check the request for ajax
     *
     * @return bool true if current request an ajax request
     * @access public
     */
    static function is_ajax();


    /**
     * Check the request for valid EasyAjax
     *
     * @return bool true if current request is a valid EasyAjax ajax request
     * @access public
     */
    function is_valid_ajax();


    /**
     * Setup the EasyAjax Instance
     *
     * @param object $scope the object scope that will execute action if given
     * @param string $where can be 'priv', 'nopriv' or 'both'. Limit actions to logged in users or not
     * @param array $allowed list of allowed actions
     * @return null
     * @access public
     */
    function setup( $scope = '', $where = '', $allowed = array() );


    /**
     * Check the request and launch EasyAjax\Proxy setup if required
     *
     * @return null
     * @access public
     */
    function register();


}


