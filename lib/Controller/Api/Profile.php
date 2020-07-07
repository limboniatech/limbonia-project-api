<?php
namespace Limbonia\Controller\Api;

/**
 * Limbonia Profile Controller class
 *
 * Admin controller for handling the profile of the logged in user
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Profile extends \Limbonia\Controller\Profile implements \Limbonia\Interfaces\Controller\Api
{
  use \Limbonia\Traits\Controller\Api;
  use \Limbonia\Traits\Controller\Api\HasModel;

  /**
   * List of controllers this controller depends on to function correctly
   *
   * @var array
   */
   protected static $aControllerDependencies = ['user'];
  /**
   * List of valid HTTP methods
   *
   * @var array
   */
  protected static function getHttpMethods()
  {
    return
    [
      'head',
      'get',
      'put',
      'options'
    ];
  }

  /**
   * Perform the base "GET" code then return null on success
   *
   * @return null
   * @throws \Exception
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
    return $this->processApiGetModel();
  }

  /**
   * Run the default "PUT" code and return the updated data
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiPut()
  {
    if (!is_array($this->oRouter->data) || count($this->oRouter->data) == 0)
    {
      throw new \Exception('No valid data found to process', 400);
    }

    return $this->processApiPutModel();
  }
}