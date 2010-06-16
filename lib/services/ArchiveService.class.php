<?php
if (!class_exists('PclZip', false))
{
	require_once WEBEDIT_HOME . '/modules/theme/tools/pclzip.lib.php';
}

/**
 * theme_ArchiveService
 * @package modules.theme
 */
class theme_ArchiveService extends BaseService 
{
	/**
	 * theme_ArchiveService instance.
	 *
	 * @var theme_ArchiveService
	 */
	private static $instance = null;


	/**
	 * Gets a theme_ArchiveService instance.
	 *
	 * @return theme_ArchiveService
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = self::getServiceClassInstance(get_class());
			if (!defined("PCLZIP_TEMPORARY_DIR"))
			{
				$tmpDir = TMP_PATH;			
				define("PCLZIP_TEMPORARY_DIR", $tmpDir."/".uniqid("pclzip"));
			}
		}
		return self::$instance;
	}
	
	/**
	 * @param string $themeCodeName
	 * @return string
	 */
	public function archive($themeCodeName)
	{
		$zipFile = 	$this->getZipName($themeCodeName);
		if (file_exists($zipFile)) {unlink($zipFile);}
		
		$path = f_util_FileUtils::buildWebeditPath('themes', $themeCodeName);
		$localPath = $themeCodeName;
		$zip = new PclZip($zipFile);
		$zip->add($path, PCLZIP_OPT_REMOVE_PATH, $path, PCLZIP_OPT_ADD_PATH, $localPath);
		return $zipFile;
	}
	
	/**
	 * @param string $zipPath
	 * @return string
	 */
	public function restore($zipPath)
	{
		$themeCodeName = null;
		$zip = new PclZip($zipPath);
		$tmpPath = TMP_PATH . DIRECTORY_SEPARATOR . uniqid('restoreTheme');
		f_util_FileUtils::rmdir($tmpPath);
		$zip->extract(PCLZIP_OPT_PATH, $tmpPath);
		
		$result = glob($tmpPath . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'install.xml');
		if (is_array($result) && count($result) == 1)
		{
			$installPath = $result[0];
			$themeCodeName = basename(dirname($installPath));
			$doc = f_util_DOMUtils::fromPath($installPath);
			$theme = $doc->findUnique('//theme');
			if ($theme && $theme->getAttribute('id') == $themeCodeName)
			{
				$path = f_util_FileUtils::buildWebeditPath('themes', $themeCodeName);			
				f_util_FileUtils::cp($tmpPath . DIRECTORY_SEPARATOR . $themeCodeName, $path, f_util_FileUtils::OVERRIDE);
				if (!file_exists($path . DIRECTORY_SEPARATOR . 'install.xml'))
				{
					$themeCodeName = null;
				}
			}
		}
		f_util_FileUtils::rmdir($tmpPath);
		return $themeCodeName;
	}
	
	/**
	 * @param string $themeCodeName
	 * @return string
	 */
	private function getZipName($themeCodeName)
	{
		return f_util_FileUtils::buildWebeditPath('themes', $themeCodeName . '.zip');	
	}
}