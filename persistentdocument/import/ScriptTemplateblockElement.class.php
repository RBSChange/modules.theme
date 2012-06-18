<?php
class theme_ScriptTemplateblockElement extends import_ScriptBaseElement
{
	/**
	 * @return array
	 */
	public function getBlockInfos()
	{
		$attrs = $this->getComputedAttributes();
		if (!isset($attrs['type']))
		{
			throw new Exception('The attribute "type" is required on templateblocks.');
		}
		if (!isset($attrs['editname']))
		{
			throw new Exception('The attribute "editname" is required on templateblocks.');
		}
		$blockInfos = array('type' => $attrs['type'], 'parameters' => array());
		foreach ($attrs as $name => $value)
		{
			if (f_util_StringUtils::beginsWith($name, '__'))
			{
				if ($value instanceof f_persistentdocument_PersistentDocument)
				{
					$value = $value->getId();
				}
				elseif (is_array($value))
				{
					$value = implode(',', DocumentHelper::getIdArrayFromDocumentArray($value));
				}
				$blockInfos['parameters'][substr($name, 2)] = $value;
			}
			elseif ($name != 'editname' && $name != 'type')
			{
				echo __METHOD__, ' Unknown attribue ', $name, PHP_EOL;
			}
		}
		return array($attrs['editname'], $blockInfos);
	}
}