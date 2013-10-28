<?php
namespace EasyAjax;


/**
 * EasyAjax\ProxyInterface interface
 *
 * @package EasyAjax
 * @author Giuseppe Mazzapica
 *
 */
interface ProxyInterface {


    /**
     * Prepare the output
     *
     * @param string $action the action to execute
     * @param object $scope the object scope that execute action if given
     * @param string $type can be 'priv', 'nopriv' or 'both'. Limit actions to logged in users or not
     * @return null
     * @access public
     */
    function setup( $action, $scope, $type );


    /**
     * Setup the callable for given action
     *
     * @return null
     * @access public
     */
    function get_callable();


    /**
     * Exectute the callable, echo the result (json encoded id required) and die
     *
     * @return die the resut of callable call
     * @access public
     */
    function proxy();


    /**
     * Fire some pre/post actions hook and get the output fireng the action callable
     *
     * @return mixed the resul of callable
     * @access public
     */
    function output();


}


