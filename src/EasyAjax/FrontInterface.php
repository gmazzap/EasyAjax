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
    function setup( $scope = '', $where = '', $allowed = [ ] );

    /**
     * Check the request and launch EasyAjax\Proxy setup if required
     *
     * @return null
     * @access public
     */
    function register();
}