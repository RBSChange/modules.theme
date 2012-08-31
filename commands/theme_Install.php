<?php
/**
 * commands_theme_Install
 * @package modules.theme.command
 */
class commands_theme_Install extends c_ChangescriptCommand
{
	/**
	 * @return string
	 */
	public function getUsage()
	{
		return "<theme>";
	}

	/**
	 * @return string
	 * For exemple "initialize a document"
	 */
	public function getDescription()
	{
		return "Install theme";
	}
	
	/**
	 * @see c_ChangescriptCommand::getEvents()
	 */
	public function getEvents()
	{
		return array(
			array('target' => 'reset-database'),
		);
	}
	
	/**
	 * @see c_ChangescriptCommand::getParameters()
	 *
	 * @param integer $completeParamCount
	 * @param string[] $params
	 * @param unknown_type $options
	 * @param string $current
	 * @return string[]
	 */
	public function getParameters($completeParamCount, $params, $options, $current)
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
	 * @param string[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 * @see c_ChangescriptCommand::parseArgs($args)
	 */
	public function _execute($params, $options)
	{
		$this->message("== Install ==");

		$this->loadFramework();
		$themes = array();
		
		if (f_util_ArrayUtils::isNotEmpty($params) && count($params) == 1)
		{
			$themes[] = $params[0];
		}
		else
		{
			foreach (glob("themes/*/install.xml") as $installXML)
			{
				$themes[] = basename(dirname($installXML));
			}
		}
		
		if (count($themes) == 0)
		{
			return $this->quitError('no theme defined');
		}
		
		foreach ($themes as $theme)
		{
			$path = f_util_FileUtils::buildProjectPath('themes', $theme, 'install.xml');
			if (is_readable($path))
			{
				theme_ModuleService::getInstance()->installTheme($theme);
				$this->okMessage("Theme $theme installed successfully");
			}
		}
		$this->executeCommand('clear-webapp-cache');
		return $this->quitOk();
	}
}