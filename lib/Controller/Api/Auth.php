<?php
namespace Limbonia\Controller\Api;

/**
 * Limbonia Auth Controller class
 *
 * Admin controller for handling user authentication
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Auth extends \Limbonia\Controller\Auth implements \Limbonia\Interfaces\Controller\Api
{
  use \Limbonia\Traits\Controller\Api
  {
    validUser as originalValidUser;
  }

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
      'post',
      'delete',
      'options'
    ];
  }

  /**
   * Is the current user valid?
   *
   * @return boolean
   */
  public function validUser()
  {
    if ($this->oRouter->method == 'post')
    {
      return true;
    }

    return $this->originalValidUser();
  }

  /**
   * Perform the base "GET" code then return null on success
   *
   * @return null
   * @throws \Exception
   */
  protected function processApiHead()
  {
    $this->oApp->getDB();
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
    return $this->oApp->getDB()->query("SELECT * FROM UserAuth");
  }

  /**
   * Run the default "POST" code and return the created data
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiPost()
  {
    $hData = $this->oRouter->data;

    if (empty($hData['email']) || empty($hData['password']))
    {
      throw new \Limbonia\Exception\Web('Authentication failed', null, 401);
    }

    if ($hData['email'] === $this->oApp->master['User'] && $hData['password'] === $this->oApp->master['Password'])
    {
      $oUser = $this->oApp->userByEmail('MasterAdmin');
    }
    else
    {
      $oUser = $this->oApp->userByEmail($hData['email']);
      $oUser->authenticate($hData['password']);
    }

    return
    [
      'auth_token' => $oUser->generateAuthToken(),
      'user' => $oUser->getAll()
    ];
  }

  /**
   * Run the default "DELETE" code and return true
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiDelete()
  {
    $this->oApp->user()->deleteAuthToken($this->oRouter->action);
    return null;
  }
}