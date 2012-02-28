<?php
/**
 * Class where to put your custom methods for document theme_persistentdocument_pagetemplate
 * @package modules.theme.persistentdocument
 */
class theme_persistentdocument_pagetemplate extends theme_persistentdocument_pagetemplatebase 
{
	/**
	 * @see f_persistentdocument_PersistentDocumentImpl::getTreeNodeLabel()
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
	 * @return f_util_DOMDocument
	 */
	public function getReplacedDOMContent()
	{
		$DOMDocument = f_util_DOMUtils::fromPath($this->getContentFilePath());
		$configuredBloc = $this->getConfiguredBlocks();
		if (count($configuredBloc))
		{
			$resultXPath = new DOMXPath($DOMDocument);
			$resultXPath->registerNameSpace('change', website_PageRessourceService::CHANGE_PAGE_EDITOR_NS);
			foreach ($configuredBloc as $editname => $data) 
			{
				foreach ($resultXPath->query('//change:templateblock[@editname="'.$editname.'"]') as $element) 
				{
					if ($element instanceof DOMElement) 
					{
						if ($data['type'] === 'empty')
						{
							$element->removeAttribute('type');
						}
						else
						{
							$element->setAttribute('type', $data['type']);
							foreach ($data['parameters'] as $name => $value)
							{
								$element->setAttribute('__' . $name, $value);
							}
						}
					}
				}
			}
		}
		return $DOMDocument;
	}	
	
	/**
	 * @return string
	 */
	public function getDocTypeDeclaration()
	{
		if ($this->getDoctype() == 'HTML-5')
		{
			return '<!DOCTYPE html>';
		}
		elseif ($this->getDoctype() == 'XHMTL-1.0-Transitional')
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
	
	/**
	 * @return string
	 */
	public function getEditableblocksJSON()
	{
		$result = array();
		$infos = $this->getDocumentService()->getEditableblocksInfos($this);
		$config = $this->getConfiguredBlocks();
		foreach ($infos as $name => $data) 
		{
			$row = array('editname' => $name, 'ot' => $data['type'], 'op' => $data['parameters']);
			$row['ct'] = $row['ot'];
			$row['cp'] = $row['op'];
			if (isset($config[$name]))
			{
				if (isset($config[$name]['type'])) { $row['ct'] = $config[$name]['type'];}
				if (isset($config[$name]['parameters'])) { $row['cp'] = $config[$name]['parameters'];}
			}
			$result[] = $row;
		}
		return JsonService::getInstance()->encode($result);
	}
	
	/**
	 * @return string
	 */
	public function setEditableblocksJSON($json)
	{
		$config = array();
		if (f_util_StringUtils::isNotEmpty($json))
		{	
			$infos = JsonService::getInstance()->decode($json);
			$default = $this->getDocumentService()->getEditableblocksInfos($this);
			foreach ($infos as $row) 
			{
				$editName = $row['editname'];
				if (!isset($default[$editName])) {continue;}
				$data = $default[$editName];
					
				$type = f_util_StringUtils::isEmpty($row['ct']) ? 'empty' : trim($row['ct']);
				$parameters = f_util_ArrayUtils::isEmpty($row['cp']) ? array() : $row['cp']; 
				$add = $data['type'] != $type;
				if (!$add)
				{
					foreach ($parameters as $name => $value) 
					{
						if (!isset($data['parameters'][$name]) || $data['parameters'][$name] != $value)
						{
							$add = true;
							break;
						}
					}	
				}
				if ($add)
				{
					$config[$editName] = array('type' => $type, 'parameters' => $parameters);
				}
			}
		}
		$this->setConfiguredBlocks($config);
	}
	
	/**
	 * @return array
	 */
	public function getConfiguredBlocks()
	{
		if (f_util_StringUtils::isEmpty($this->getEditableblocks()))
		{
			return array();
		}
		else
		{
			return unserialize($this->getEditableblocks());
		}
	}
	
	public function setConfiguredBlocks($infos)
	{
		if (f_util_ArrayUtils::isNotEmpty($infos))
		{
			$this->setEditableblocks(serialize($infos));
		}
		else
		{
			$this->setEditableblocks(null);
		}
	}
}