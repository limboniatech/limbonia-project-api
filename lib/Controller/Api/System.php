<?php
namespace Limbonia\Controller\Api;

/**
 * Limbonia System Controller class
 *
 * Admin controller for handling all the basic system configuration and management
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class System extends \Limbonia\Controller\System implements \Limbonia\Interfaces\Controller\Api
{
  use \Limbonia\Traits\Controller\Api;

  /**
   * Perform the base "GET" code then return null on success
   *
   * @return null
   * @throws \Limbonia\Exception
   */
  protected function processApiHead()
  {
    return null;
  }

  /**
   * Perform and return the default "GET" code
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiGet()
  {
    switch ($this->oRouter->action)
    {
      case 'model-controller-base':
        break;

      default:
        throw new \Limbonia\Exception\Web('No action specified for GET System', 0, 400);
    }
  }
}