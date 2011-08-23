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
			self::$instance = new self();
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
				$dir = new DirectoryIterator($path);
				foreach ($dir as $fileinfo) 
				{
				    if ($fileinfo->isFile()) 
				    {
				    	$ext = f_util_FileUtils::getFileExtension($fileinfo->getFilename());
				    	if (in_array($ext, array('gif', 'png', 'jpg', 'jpeg')))
				    	{
				        	$imagesPath[$fileinfo->getFilename()] = $fileinfo->getPathname();
				    	}
				    }
				}
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
				$label = str_replace(f_util_FileUtils::getFileExtension($baseName, true), '', $baseName);
				$image->setLabel($label);
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
	
	public function getDefaultStaticList()
	{
		$result = array();
		$prefix = 'frontoffice/';
		
		$path = f_util_FileUtils::buildWebeditPath('media', 'frontoffice');
		$dir = new DirectoryIterator($path);
		foreach ($dir as $fileinfo) 
		{
		    if ($fileinfo->isFile()) 
		    {
		    	$ext = f_util_FileUtils::getFileExtension($fileinfo->getFilename());
		    	if (in_array($ext, array('gif', 'png', 'jpg', 'jpeg')))
		    	{
		    		$result[] = array('label' => $prefix. $fileinfo->getFilename(),
		    			'value' => 'url(/' . implode('/', array('media', 'frontoffice', $fileinfo->getFilename())) . ')');
		    	}
		    }
		}
		return $result;
	}
}