<?php
/**
 * commands_theme_GenerateSkin
 * @package modules.theme.command
 */
class commands_theme_Init extends commands_AbstractChangeCommand
{
	/**
	 * @return String
	 * @example "<moduleName> <name>"
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
		return "Initialize a theme directory";
	}
	
	/**
	 * @param String[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 */
	protected function validateArgs($params, $options)
	{
		return count($params) == 1;
	}

	/**
	 * @param String[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 * @see c_ChangescriptCommand::parseArgs($args)
	 */
	function _execute($params, $options)
	{
		$this->message("== Initialize theme ==");

		$this->loadFramework();
		
		$themeName = $params[0];
		$themeDir = WEBEDIT_HOME."/themes/".$themeName;
		if (file_exists($themeDir))
		{
			return $this->quitError("Theme $themeName already exists");	
		}
		f_util_FileUtils::mkdir($themeDir);
		f_util_FileUtils::mkdir($themeDir."/image");
		f_util_FileUtils::mkdir($themeDir."/js");
		f_util_FileUtils::mkdir($themeDir."/locale");
		f_util_FileUtils::mkdir($themeDir."/modules");
		f_util_FileUtils::mkdir($themeDir."/skin");
		f_util_FileUtils::mkdir($themeDir."/style");
		f_util_FileUtils::mkdir($themeDir."/templates");
		
		$installDom = f_util_DOMUtils::fromString('<?xml version="1.0" encoding="UTF-8"?>
<script>
  <binding fileName="modules/theme/persistentdocument/import/theme_binding.xml" />
  <rootfolder module="theme">
    <theme id="'.$themeName.'" byCodename="'.$themeName.'" label="" description="">
      <!-- 
      <pagetemplate byCodename="'.$themeName.'/fileNameWithoutExtension" doctype="XHTML-1.0-Strict|XHTML-1.0-Transitional" js="themes.'.$themeName.'.js..." useprojectcss="false|true" cssscreen="themes.'.$themeName.'..." />
      -->
    </theme>
    </rootfolder>
</script>');
		f_util_DOMUtils::save($installDom, $themeDir."/install.xml");
		
		return $this->quitOk("Theme $themeName initialized successfully. Please now edit files in themes/$themeName/.");
	}
}