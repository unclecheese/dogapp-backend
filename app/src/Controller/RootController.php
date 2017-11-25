<?php
namespace MyOrg\Controller;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPResponse_Exception;

/**
 * Deny root access (running headless)
 *
 */
class RootController extends Controller
{
    /**
     * We're headless!
     *
     * @param $request
     * @throws HTTPResponse_Exception
     */
    public function index($request)
    {
        return $this->httpError(401, 'Decapitated');
    }
}
