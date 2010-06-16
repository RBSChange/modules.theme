<?php
/**
 * Class where to put your custom methods for document theme_persistentdocument_pagetemplate
 * @package modules.theme.persistentdocument
 */
class theme_persistentdocument_pagetemplate extends theme_persistentdocument_pagetemplatebase 
{
	/**
	 * @param string $moduleName
	 * @param string $treeType
	 * @param array<string, string> $nodeAttributes
	 */
	protected function addTreeAttributes($moduleName, $treeType, &$nodeAttributes)
	{
		$thumbnail = $this->getThumbnail();
		if ($thumbnail)
		{		
			$nodeAttributes['hasPreviewImage'] = true;
			if ($treeType == 'wlist')
			{
	    		$nodeAttributes['thumbnailsrc'] = $thumbnail->getUISrc();
			}
		}
	}
	
	/**
	 * @param string $actionType
	 * @param array $formProperties
	 */
	//	public function addFormProperties($propertiesNames, &$formProperties)
	//	{	
	//	}
	
	/**
	 * @see f_persistentdocument_PersistentDocumentImpl::getTreeNodeLabel()
	 *
	 * @return String
	 */
	public function getLabel()
	{
		return f_Locale::translateUI(parent::getLabel());
	}
	
	/**
	 * @return string
	 */
	private function getContentFilePath()
	{
		return f_util_FileUtils::buildWebeditPath($this->getProjectpath());
	}
	
	/**
	 * @return string
	 */
	public function getContent()
	{
		return file_get_contents($this->getContentFilePath());
	}
	
	/**
	 * @return f_util_DOMDocument
	 */
	public function getDOMContent()
	{
		return f_util_DOMUtils::fromPath($this->getContentFilePath());
	}
	
	/**
	 * @return string
	 */
	public function getDocTypeDeclaration()
	{
		if ($this->getDoctype() == 'XHMTL-1.0-Transitional')
		{
			return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		}
		return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
	}
	
	
	
	/**
	 * @return string[]
	 */
	public function getScriptIds()
	{
		if ($this->getUseprojectjs())
		{
			$result = $this->getDocumentService()->getStandardScriptIds();		
		}
		else
		{
			$result = array();
		}
		return $this->appendToArray($result, $this->getJs());
	}
	
	/**
	 * @return string[]
	 */	
	public function getScreenStyleIds()
	{
		if ($this->getUseprojectcss())
		{
			$result = $this->getDocumentService()->getStandardScreenStyleIds();	
		}
		else
		{
			$result = array();
		}
		return $this->appendToArray($result, $this->getCssscreen());
	}
	
	/**
	 * @return string[]
	 */	
	public function getPrintStyleIds()
	{
		if ($this->getUseprojectcss())
		{
			$result = $this->getDocumentService()->getStandardPrintStyleIds();	
		}
		else
		{
			$result = array();
		}
		return $this->appendToArray($result, $this->getCssprint());
	}
	
	/**
	 * @param string[] $array
	 * @param string $string
	 * @return string[]
	 */
	private function appendToArray($array, $string)
	{
		if (f_util_StringUtils::isNotEmpty($string))
		{
			$ids = explode(',', $string);
			foreach ($ids as $id) 
			{
				$cleanId = trim($id);
				if (!in_array($cleanId, $array))
				{
					$array[] = $cleanId;
				}
			}
		}
		return $array;
	}
}