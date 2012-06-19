<?php
/**
 * commands_theme_GenerateSkin
 * @package modules.theme.command
 */
class commands_theme_Init extends c_ChangescriptCommand
{
	/**
	 * @return string
	 * For exemple "<moduleName> <name>"
	 */
	function getUsage()
	{
		return "<theme>";
	}

	/**
	 * @return string
	 * For exemple "initialize a document"
	 */
	function getDescription()
	{
		return "Initialize a theme directory";
	}
	
	/**
	 * @param string[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 */
	protected function validateArgs($params, $options)
	{
		if (count($params) != 1)
		{
			return false;
		}
		$moduleName = $params[0];
		if (!preg_match('/^[a-z][a-z0-9]{1,24}$/', $moduleName))
		{
			$this->errorMessage("Invalid theme name ([a-z][a-z0-9]{1,24}): " . $moduleName);
			return false;
		}
		return true;
	}

	/**
	 * @param string[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 * @see c_ChangescriptCommand::parseArgs($args)
	 */
	function _execute($params, $options)
	{
		$this->message("== Initialize theme ==");

		$this->loadFramework();
		
		$themeName = $params[0];
		$themeDir = PROJECT_HOME."/themes/".$themeName;
		if (file_exists($themeDir))
		{
			return $this->quitError("Theme $themeName already exists");	
		}
		f_util_FileUtils::mkdir($themeDir);
		f_util_FileUtils::mkdir($themeDir."/image");
		f_util_FileUtils::mkdir($themeDir."/js");
		f_util_FileUtils::mkdir($themeDir."/i18n");
		f_util_FileUtils::mkdir($themeDir."/modules");
		f_util_FileUtils::mkdir($themeDir."/skin");
		f_util_FileUtils::mkdir($themeDir."/style");
		f_util_FileUtils::mkdir($themeDir."/setup");
		f_util_FileUtils::mkdir($themeDir."/templates");
		
		$installDom = f_util_DOMUtils::fromString('<?xml version="1.0" encoding="UTF-8"?>
<script>
	<binding fileName="modules/theme/persistentdocument/import/theme_binding.xml" />
	<rootfolder module="theme">
		<theme id="'.$themeName.'" byCodename="'.$themeName.'" label="'.$themeName.'" description="'.$themeName.'">
			<!-- 
			<pagetemplate byCodename="'.$themeName.'/fileNameWithoutExtension" doctype="HTML-5|XHTML-1.0-Strict|XHTML-1.0-Transitional" js="themes.'.$themeName.'.js..." useprojectcss="false|true" cssscreen="themes.'.$themeName.'..." />
			-->
		</theme>
	</rootfolder>
</script>');
		f_util_DOMUtils::save($installDom, $themeDir."/setup/init.xml");
		
		$package = c_Package::getNewInstance('themes', $themeName, PROJECT_HOME);
		$package->setVersion(FRAMEWORK_VERSION);
		$package->setDownloadURL('none');
		
		
		$installDom = f_util_DOMUtils::fromString('<?xml version="1.0" encoding="UTF-8"?>
<install></install>');
		
		$package->populateNode($installDom->documentElement);	
		f_util_DOMUtils::save($installDom, $themeDir."/install.xml");
		
		$this->getBootStrap()->updateProjectPackage($package);
		
		$this->executeCommand('theme.install', array($themeName));
		
		return $this->quitOk("Theme " . $package->__toString() . " initialized successfully. Please now edit files in ".PROJECT_HOME."/themes/$themeName/.");
	}
}