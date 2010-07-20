<?php
/**
 * commands_theme_Install
 * @package modules.theme.command
 */
class commands_theme_Install extends commands_AbstractChangeCommand
{
	/**
	 * @return String
	 */
	function getUsage()
	{
		return "<theme>";
	}

	/**
	 * @return String
	 * @example "initialize a document"
	 */
	function getDescription()
	{
		return "Install theme";
	}
	
	/**
	 * @see c_ChangescriptCommand::getParameters()
	 *
	 * @param Integer $completeParamCount
	 * @param String[] $params
	 * @param unknown_type $options
	 * @param String $current
	 * @return String[]
	 */
	function getParameters($completeParamCount, $params, $options, $current)
	{
		if ($completeParamCount == 0)
		{
			$components = array();		
			$themes = glob("themes/*/install.xml");
			if (is_array($themes))
			{
				foreach ($themes as $theme)
				{
					$components[] = basename(dirname($theme));
				}
			}
			return $components;
		}
	}
	
	/**
	 * @param String[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 */
//	protected function validateArgs($params, $options)
//	{
//	}

	/**
	 * @return String[]
	 */
//	function getOptions()
//	{
//	}

	/**
	 * @param String[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 * @see c_ChangescriptCommand::parseArgs($args)
	 */
	function _execute($params, $options)
	{
		$this->message("== Install ==");

		$this->loadFramework();
		if (f_util_ArrayUtils::isNotEmpty($params) && count($params) == 1)
		{
			$path = f_util_FileUtils::buildWebeditPath('themes', $params[0], 'install.xml');
			if (is_readable($path))
			{
				theme_ModuleService::getInstance()->installTheme($params[0]);
				$this->getParent()->executeCommand('clear-webapp-cache');
				return $this->quitOk('Theme ' . $params[0] . ' installed successfully');
			}
		}
		return $this->quitError('no theme defined');
	}
}