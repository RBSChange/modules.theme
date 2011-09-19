<?php
/**
 * commands_theme_CompileTheme
 * @package modules.theme.command
 */
class commands_theme_CompileTheme extends c_ChangescriptCommand
{
	/**
	 * @return String
	 * @example "<moduleName> <name>"
	 */
	function getUsage()
	{
		return "<theme>[ <theme2>][ <theme3>]";
	}

	/**
	 * @return String
	 * @example "initialize a document"
	 */
	function getDescription()
	{
		return "Compile theme data";
	}
	
	/**
	 * @see c_ChangescriptCommand::getEvents()
	 */
	public function getEvents()
	{
		return array(
			array('target' => 'compile-all'),
		);
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
		$components = array();		
		$themes = glob("themes/*/install.xml");
		if (is_array($themes))
		{
			foreach ($themes as $theme)
			{
				$components[] = basename(dirname($theme));
			}
		}
		return array_diff($components, $params);
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
		$this->message("== Compile theme ==");
		$this->loadFramework();
		$ts = theme_ModuleService::getInstance();
		if (f_util_ArrayUtils::isEmpty($params))
		{
			$ts->regenerateAllThemes(true);
			$this->executeCommand('clear-webapp-cache');
			return $this->quitOk('All themes compiled successfully.');
		}

		foreach ($params as $theme)
		{
			$themeBasePath = f_util_FileUtils::buildWebeditPath('themes', $theme);
			if (!is_dir($themeBasePath))
			{
				$this->errorMessage("Theme $theme does not exist.");
			}
			else
			{
				$ts->regenerateTheme($theme, null, true);
				$this->message("$theme compiled");
			}
		}
		
		if ($this->hasError())
		{
			return $this->quitError("All themes could not be compiled: ".$this->getErrorCount()." errors");
		}
		
		$this->executeCommand('clear-webapp-cache');
		return $this->quitOk("Command successfully executed");
	}
}