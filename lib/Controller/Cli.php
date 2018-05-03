<?php
namespace Limbonia\Controller;

/**
 * Limbonia CLI Controller Class
 *
 * This allows the basic controller to run in the command line environment
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Cli extends \Limbonia\Controller
{
  const OPTION_VALUE_NONE = 0;
  const OPTION_VALUE_ALLOW = 1;
  const OPTION_VALUE_REQUIRE = 2;

  protected $sTemplateName = '';

  protected $sTempalteDesc = 'This utility has no description';

  protected $hOptionList =
  [
    [
      'short' => 'h',
      'long' => 'help',
      'desc' => 'Print this help screen',
      'value' => self::OPTION_VALUE_NONE
    ],
    [
      'long' => 'debug',
      'desc' => 'Set the debug level of this utility, if no value is specified then it defaults to highest debug level available.',
      'value' => self::OPTION_VALUE_ALLOW
    ]
  ];

  public function displayHelp()
  {
    $sHelp = "
Usage:
$this->sTemplateName [options]

$this->sTempalteDesc

Options:\n";

    $iMaxLength = 2;

    foreach ($this->hOptionList as $hOption)
    {
      if (isset($hOption['long']))
      {
        $iTemp = strlen($hOption['long']);

        if ($iTemp > $iMaxLength)
        {
          $iMaxLength = $iTemp;
        }
      }
    }

    foreach ($this->hOptionList as $hOption)
    {
      $bShort = isset($hOption['short']);
      $bLong = isset($hOption['long']);

      if (!$bShort && !$bLong)
      {
        continue;
      }

      if ($bShort && $bLong)
      {
        $sOpt = '-' . $hOption['short'] . ', --' . $hOption['long'];
      }
      elseif ($bShort && !$bLong)
      {
        $sOpt = '-' . $hOption['short'] . "\t";
      }
      elseif (!$bShort && $bLong)
      {
        $sOpt = '    --' . $hOption['long'];
      }

      $sOpt = str_pad($sOpt, $iMaxLength + 7);
      $sHelp .= "\t$sOpt\t\t{$hOption['desc']}\n\n";
    }

    die($sHelp . "\n");
  }

  public function setDescription($sDesc)
  {
    $this->sTempalteDesc = $sDesc;
  }

  public function processOptions()
  {
    $sShortOptions = '';
    $aLongOptions = [];

    foreach ($this->hOptionList as $hOption)
    {
      $sOptionValueMod = '';

      if (isset($hOption['value']))
      {
        if ($hOption['value'] == self::OPTION_VALUE_ALLOW)
        {
          $sOptionValueMod = '::';
        }
        elseif ($hOption['value'] == self::OPTION_VALUE_REQUIRE)
        {
          $sOptionValueMod = ':';
        }
      }

      if (isset($hOption['short']))
      {
        $sShortOptions .= $hOption['short'] . $sOptionValueMod;
      }

      if (isset($hOption['long']))
      {
        $aLongOptions[] = $hOption['long'] . $sOptionValueMod;
      }
    }

    $hActiveOptions = getopt($sShortOptions, $aLongOptions);

    if (isset($hActiveOptions['h']) || isset($hActiveOptions['help']))
    {
      $this->displayHelp();
    }

    return $hActiveOptions;
  }

  public function addOption($hOption)
  {
    $this->hOptionList[] = $hOption;
  }

  protected function generateTemplateFile()
  {
    $oServer = \Limbonia\Input::singleton('server');
    $sCliName = preg_replace("/^limbonia_/", '', basename($oServer['argv'][0]));
    $this->sTemplateName = $sCliName;
    $sTemplateFile = $this->templateFile($sCliName);

    if (!empty($sTemplateFile))
    {
      return $sTemplateFile;
    }

    $hModeOpt = getopt('', ['mode::']);

    if (isset($hModeOpt['mode']) && empty($hModeOpt['mode']))
    {
      throw new \Exception('Mode not specified');
    }

    $sTemplateName = isset($hModeOpt['mode']) ? $hModeOpt['mode'] : '';
    $sTemplateFile = $this->templateFile($sTemplateName);

    if (!empty($sTemplateFile))
    {
      $this->sTemplateName = $sTemplateName;
      return $sTemplateFile;
    }

    return $this->templateFile('default');
  }

  protected function renderPage()
  {
    $sModuleDriver = isset($this->oApi->module) ? \Limbonia\Module::driver($this->oApi->module) : '';

    if (empty($sModuleDriver))
    {
      try
      {
        $aAvailableModes = [];

        foreach (\Limbonia\Controller::templateDirs() as $sDir)
        {
          foreach (glob($sDir . '/' . $this->type . '/*.php') as $sFileName)
          {
            $aAvailableModes[] = basename($sFileName, '.php');
          }
        }

        $aAvailableModes = array_unique($aAvailableModes);
        sort($aAvailableModes);
        $iPos = array_search('default', $aAvailableModes);

        if (false !== $iPos)
        {
          unset($aAvailableModes[$iPos]);
        }

        $iPos = array_search('error', $aAvailableModes);

        if (false !== $iPos)
        {
          unset($aAvailableModes[$iPos]);
        }

        if (count($aAvailableModes) > 0)
        {
          $this->addOption
          ([
            'long' => 'mode',
            'value' => \Limbonia\Controller\Cli::OPTION_VALUE_REQUIRE,
            'desc' => "This utility has the following built-in modes:\n\t\t\t\t" . implode("\n\t\t\t\t", $aAvailableModes)
          ]);
        }

        $this->processOptions();
        return $this->templateRender($this->generateTemplateFile());
      }
      catch (Exception $e)
      {
        $this->templateData('failure', 'Failed to generate the requested data: ' . $e->getMessage());
        return $this->templateRender('error');
      }
    }

    try
    {
      $oCurrentModule = $this->moduleFactory($sModuleDriver);
      $this->sTemplateName = strtolower($sModuleDriver) . '_' . $this->oApi->action;
      $oCurrentModule->prepareTemplate();
      $this->templateData('options', $this->processOptions());
      $sModuleTemplate = $oCurrentModule->getTemplate();
      return $this->templateRender($sModuleTemplate);
    }
    catch (\Exception $e)
    {
      $this->templateData('failure', "The module {$this->oApi->module} could not be instaniated: " . $e->getMessage());
      return $this->templateRender('error');
    }
  }

  /**
   * Run everything needed to react and display data in the way this controller is intended
   */
  public function run()
  {
    try
    {
      $this->templateData('controller', $this);
      $this->oUser = $this->generateUser();
    }
    catch (\Exception $e)
    {
      echo $e->getMessage() . "\n";
    }

    $this->templateData('currentUser', $this->oUser);
    die($this->renderPage());
  }
}