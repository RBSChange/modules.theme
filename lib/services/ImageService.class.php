<?php
/**
 * theme_ImageService
 * @package modules.theme
 */
class theme_ImageService extends f_persistentdocument_DocumentService
{
	/**
	 * @var theme_ImageService
	 */
	private static $instance;

	/**
	 * @return theme_ImageService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}

	/**
	 * @return theme_persistentdocument_image
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_theme/image');
	}

	/**
	 * Create a query based on 'modules_theme/image' model.
	 * Return document that are instance of modules_theme/image,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_theme/image');
	}
	
	/**
	 * Create a query based on 'modules_theme/image' model.
	 * Only documents that are strictly instance of modules_theme/image
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->pp->createQuery('modules_theme/image', false);
	}
	
	/**
	 * @param string $codeName
	 * @return theme_persistentdocument_image
	 */
	public function getByCodeName($codeName)
	{
		return $this->createQuery()->add(Restrictions::eq('codename', $codeName))->findUnique();
	}
	
	/**
	 * @param theme_persistentdocument_theme $theme
	 */
	public function refreshByFiles($theme)
	{
		$paths = FileResolver::getInstance()
				->setPackageName('themes_' . $theme->getCodename())
				->setDirectory('image')->getPaths('');	

		$imagesPath = array();
		if (is_array($paths) && count($paths))
		{
			foreach ($paths as $path) 
			{
				$this->walkThemeDirectory($path, '', $imagesPath);
			}
		}
		
		$images = array();
		foreach ($imagesPath as $baseName => $path) 
		{
			$codeName = 'media/themes/' . $theme->getCodename() . '/' . $baseName;
			$image = $this->getByCodeName($codeName);
			if (!$image)
			{
				$image = $this->getNewDocumentInstance();		
				$image->setCodename($codeName);
				$image->setLabel($baseName);
				$image->setThemeid($theme->getId());
				$image->setProjectpath('themes/' . $theme->getCodename() . '/image/' . $baseName);
				$image->save();
				$theme->addImage($image);
			}
			$images[] = $image->getId();
		}
		
		$toDelete = array();
		foreach ($theme->getImageArray() as $image) 
		{
			if (!in_array($image->getId(), $images))
			{
				$toDelete[] =  $image->getId();
				$theme->removeImage($image);	
			}
		}
			
		if (count($toDelete))
		{
			$this->createQuery()
				->add(Restrictions::in('id', $toDelete))
				->delete();
		}
	}
	
	protected function walkThemeDirectory($path, $labelPrefix, &$imagesPath)
	{
		$dir = new DirectoryIterator($path);
		foreach ($dir as $fileinfo)
		{
			if ($fileinfo->isFile())
			{
				$ext = f_util_FileUtils::getFileExtension($fileinfo->getFilename());
		    	if (in_array($ext, array('gif', 'png', 'jpg', 'jpeg')))
		    	{
		    		$imagesPath[$labelPrefix . $fileinfo->getFilename()] = $fileinfo->getPathname();
		    	}
			}
			elseif ($fileinfo->isDir() && !f_util_StringUtils::beginsWith($fileinfo->getFilename(), '.'))
			{
				$this->walkThemeDirectory($path . $fileinfo->getFilename() . DIRECTORY_SEPARATOR, $labelPrefix . $fileinfo->getFilename() . '/', $imagesPath);
			}
		}
	}
	
	public function getDefaultStaticList()
	{
		$result = array();
		$prefix = 'frontoffice/';
		$urlPrefix = 'media/frontoffice';		
		$this->walkStaticDirectory(f_util_FileUtils::buildWebeditPath('media', 'frontoffice'), $urlPrefix, $prefix, $result);
		return $result;
	}
	
	protected function walkStaticDirectory($path, $urlPrefix, $labelPrefix, &$result)
	{
		$dir = new DirectoryIterator($path);
		foreach ($dir as $fileinfo)
		{
			if ($fileinfo->isFile())
			{
				$ext = f_util_FileUtils::getFileExtension($fileinfo->getFilename());
				if (in_array($ext, array('gif', 'png', 'jpg', 'jpeg')))
				{
					$result[] = array('label' => $labelPrefix . $fileinfo->getFilename(),
						'value' => 'url(/' . implode('/', array($urlPrefix, $fileinfo->getFilename())) . ')');
				}
			}
			elseif ($fileinfo->isDir() && !f_util_StringUtils::beginsWith($fileinfo->getFilename(), '.'))
			{
				$this->walkStaticDirectory($path . DIRECTORY_SEPARATOR . $fileinfo->getFilename(), $urlPrefix . '/' . $fileinfo->getFilename(), $labelPrefix . $fileinfo->getFilename() . '/', $result);
			}
		}
	}
}