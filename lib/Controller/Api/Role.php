<?php
namespace Limbonia\Controller\Api;

/**
 * Limbonia Role Controller class
 *
 * Admin controller for handling groups
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Role extends \Limbonia\Controller\Role implements \Limbonia\Interfaces\Controller\Api
{
  use \Limbonia\Traits\Controller\Api;
  use \Limbonia\Traits\Controller\ApiModel
  {
    processApiGetModel as originalProcessApiGetModel;
  }

  /**
   * Generate and return the default model data, filtered by API controls
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiGetModel()
  {
    switch ($this->oRouter->action)
    {
      case 'resources':
        $hResourceList = [];
        $hKeys = $this->oModel->getResourceKeys();

        foreach ($this->oModel->getResourceList() as $oResource)
        {
          $hResourceList[$oResource->id] = $oResource->getAll();
          $hResourceList[$oResource->id]['Level'] = $hKeys[$oResource->id];
        }

        return $hResourceList;
    }

    return $this->originalProcessApiGetModel();
  }
}