<?php
namespace Limbonia\Controller\Api;

/**
 * Limbonia User Controller class
 *
 * Admin controller for handling users
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class User extends \Limbonia\Controller\User implements \Limbonia\Interfaces\Controller\Api
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
      case 'roles':
        return $this->oModel->getRoles();

      case 'tickets':
        return $this->oModel->getTickets();
    }

    return $this->originalProcessApiGetModel();
  }

  /**
   * Delete the API specified list of models then return true
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiDeleteList()
  {
    if (empty($this->oRouter->search))
    {
      throw new \Limbonia\Exception\Web("No list criteria specified", null, 403);
    }

    $hList = $this->getList(['id']);
    $aList = array_keys($hList);

    if (empty($aList))
    {
      throw new \Limbonia\Exception\Web("List criteria produced no results", null, 403);
    }

    if (in_array($this->oApp->user()->id, $aList))
    {
      throw new \Limbonia\Exception\Web("List results cannot contain the current user", null, 403);
    }

    $oMasterUser = $this->oApp->userByEmail('MasterAdmin');

    if (in_array($oMasterUser->id, $aList))
    {
      throw new \Limbonia\Exception\Web("List results cannot contain the master user", null, 403);
    }

    $sTable = $this->oModel->getTable();
    $sIdColumn = $this->oModel->getIDColumn();
    $sSql = "DELETE FROM $sTable WHERE $sIdColumn IN (" . implode(', ', $aList) . ")";
    $iRowsDeleted = $this->oApp->getDB()->exec($sSql);

    if ($iRowsDeleted === false)
    {
      $aError = $this->errorInfo();
      throw new \Limbonia\Exception\DBResult("Model list not deleted from $sTable: {$aError[0]} - {$aError[2]}", $this->getType(), $sSql, $aError[1]);
    }

    return true;
  }
}