<?php
/**
 * @package modules.theme
 * @method theme_JavascriptService getInstance()
 */
class theme_JavascriptService extends f_persistentdocument_DocumentService
{
	/**
	 * @return theme_persistentdocument_javascript
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_theme/javascript');
	}

	/**
	 * Create a query based on 'modules_theme/javascript' model.
	 * Return document that are instance of modules_theme/javascript,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_theme/javascript');
	}
	
	/**
	 * Create a query based on 'modules_theme/javascript' model.
	 * Only documents that are strictly instance of modules_theme/javascript
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_theme/javascript', false);
	}
	
	/**
	 * @param string $codeName
	 * @return theme_persistentdocument_javascript
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
				->setDirectory('js')
				->getPaths('');	

		$jsPaths = array();
		if (is_array($paths) && count($paths))
		{
			foreach ($paths as $path) 
			{
				$dir = new DirectoryIterator($path);
				foreach ($dir as $fileinfo) 
				{
					if ($fileinfo->isFile()) 
					{
						$jsParts = explode('.', $fileinfo->getFilename());
						if (count($jsParts) == 2 && $jsParts[1] == 'js')
						{
							$jsPaths[$jsParts[0]] = $fileinfo->getPathname();
						}
					}
				}
			}
		}
		
		$jss = array();
		foreach ($jsPaths as $baseName => $path) 
		{
			$codeName = 'themes.' . $theme->getCodename() . '.' . $baseName;
			$js = $this->getByCodeName($codeName);
			if (!$js)
			{
				$js = $this->getNewDocumentInstance();		
				$js->setCodename($codeName);
				$js->setLabel($baseName);
				$js->setThemeid($theme->getId());
				$js->setProjectpath('themes/' . $theme->getCodename() . '/js/' . $baseName .'.js');
				$js->save();
				$theme->addJavascript($js);
			}
			$jss[] = $js->getId();
		}
		
		$toDelete = array();
		foreach ($theme->getJavascriptArray() as $js) 
		{
			if (!in_array($js->getId(), $jss))
			{
				$toDelete[] =  $js->getId();
				$theme->removeJavascript($js);	
			}
		}
			
		if (count($toDelete))
		{
			$this->createQuery()->add(Restrictions::in('id', $toDelete))
				->delete();
		}
	}
}