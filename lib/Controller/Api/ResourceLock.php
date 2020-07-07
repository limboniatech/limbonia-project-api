<?php
namespace Limbonia\Controller\Api;

/**
 * Limbonia Resource Lock Controller class
 *
 * Admin controller for handling site resource locks
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class ResourceLock extends \Limbonia\Controller\ResourceLock implements \Limbonia\Interfaces\Controller\Api
{
  use \Limbonia\Traits\Controller\Api;
  use \Limbonia\Traits\Controller\Api\HasModel;
}