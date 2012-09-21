<?php
/**
 * theme_patch_0400
 * @package modules.theme
 */
class theme_patch_0400 extends change_Patch
{
	/**
	 * @return array
	 */
	public function getPreCommandList()
	{
		return array(
			array('disable-site'),
		);
	}
	
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		$ls = LocaleService::getInstance();
		foreach (theme_PagetemplateService::getInstance()->createQuery()->find() as $folder)
		{
			/* @var $folder generic_persistentdocument_systemfolder */
			$label = $this->convertKey($folder->getLabel());
			if ($label !== $folder->getLabel())
			{
				$folder->setLabel($label);
				$folder->save();
			}
		}
	}
	
	/**
	 * @param string $key
	 * @return $key
	 */
	private function convertKey($key)
	{
		$key = str_replace(array('&modules.', '&framework.', '&themes.', ';'), array('m.', 'f.', 't.', ''), $key);
		$keyPart = explode('.', $key);
		if ($keyPart[0] === 'modules')
		{
			$keyPart[0] = 'm';
		}
		elseif ($keyPart[0] === 'framework')
		{
			$keyPart[0] = 'f';
		}
		elseif ($keyPart[0] === 'themes')
		{
			$keyPart[0] = 't';
		}
	
		$keyPartCount = count($keyPart);
		$first = current($keyPart);
		if ($keyPartCount > 1 && in_array($first, array('m', 'f', 't')))
		{
			return implode('.', $keyPart);
		}
		return $key;
	}
	
	/**
	 * @return array
	 */
	public function getPostCommandList()
	{
		return array(
			array('clear-documentscache'),
			array('enable-site'),
		);
	}
	
	/**
	 * @return string
	 */
	public function getExecutionOrderKey()
	{
		return '2012-09-21 08:51:38';
	}
		
	/**
	 * @return string
	 */
	public function getBasePath()
	{
		return dirname(__FILE__);
	}
	
    /**
     * @return false
     */
	public function isCodePatch()
	{
		return false;
	}
}