<?php
namespace Limbonia\Controller\Api;

/**
 * Limbonia Resource Controller class
 *
 * Admin controller for handling site resource keys
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class ResourceKey extends \Limbonia\Controller\ResourceKey implements \Limbonia\Interfaces\Controller\Api
{
  use \Limbonia\Traits\Controller\Api;
  use \Limbonia\Traits\Controller\Api\HasModel;
}