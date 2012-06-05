<?php
if (!class_exists('PclZip', false))
{
	require_once PROJECT_HOME . '/modules/theme/tools/pclzip.lib.php';
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
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * @param string $themeCodeName
	 * @return string
	 */
	public function archive($themeCodeName)
	{
		$zipFile = 	TMP_PATH . DIRECTORY_SEPARATOR . uniqid($themeCodeName) .'.zip';
		if (file_exists($zipFile)) {unlink($zipFile);}
		$archive = new ZipArchive();
		$archive->open($zipFile, ZipArchive::CREATE);
		
		$path = realpath(f_util_FileUtils::buildProjectPath('themes', $themeCodeName));	
		$basePathLength = strlen($path) + 1;
		
		foreach (new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::KEY_AS_PATHNAME), RecursiveIteratorIterator::SELF_FIRST)
			as $file => $info)
		{
			if ($info->isFile())
			{
				$newPath = $themeCodeName . DIRECTORY_SEPARATOR . substr($file, $basePathLength);
				$archive->addFile($file, $newPath);
			}
		}
		$archive->close();		
		return $zipFile;
	}
	
	/**
	 * @param string $zipPath
	 * @return string
	 */
	public function restore($zipPath)
	{
		$themeCodeName = null;

		$tmpPath = TMP_PATH . DIRECTORY_SEPARATOR . uniqid('restoreTheme');
		f_util_FileUtils::rmdir($tmpPath);
		f_util_FileUtils::mkdir($tmpPath);
		$tmpPath = realpath($tmpPath);
		
		$archive = new ZipArchive();
		
		if ($archive->open($zipPath))
		{
			$archive->extractTo($tmpPath);
			$archive->close();
					
			$result = glob($tmpPath . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'install.xml');
			if (is_array($result) && count($result) == 1)
			{
				$installPath = $result[0];
				$themeCodeName = basename(dirname($installPath));
				$doc = f_util_DOMUtils::fromPath($installPath);
				$theme = $doc->findUnique('//install');
				if ($theme && $theme->getAttribute('name') == $themeCodeName)
				{
					$path = f_util_FileUtils::buildWebeditPath('themes', $themeCodeName);			
					f_util_FileUtils::cp($tmpPath . DIRECTORY_SEPARATOR . $themeCodeName, $path, f_util_FileUtils::OVERRIDE);
					if (!file_exists($path . DIRECTORY_SEPARATOR . 'install.xml'))
					{
						$themeCodeName = null;
					}
				}
			}
		}
		
		f_util_FileUtils::rmdir($tmpPath);
		return $themeCodeName;
	}
}