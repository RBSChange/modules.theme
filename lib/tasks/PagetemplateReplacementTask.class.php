<?php
class theme_PagetemplateReplacementTask extends task_SimpleSystemTask  
{
	/**
	 * @see task_SimpleSystemTask::execute()
	 */
	protected function execute()
	{
		$chunkSize = Framework::getConfigurationValue('modules/theme/pagetemplateReplacementChunkSize');
		$toReplace = DocumentHelper::getDocumentInstanceIfExists($this->getParameter('toReplaceId'));
		if (!($toReplace instanceof theme_persistentdocument_pagetemplate))
		{
			Framework::error(__METHOD__ . ' Invalid toReplaceId:' . $this->getParameter('toReplaceId'));
		}
		$replaceBy = DocumentHelper::getDocumentInstanceIfExists($this->getParameter('replaceById'));
		if (!($replaceBy instanceof theme_persistentdocument_pagetemplate))
		{
			Framework::error(__METHOD__ . ' Invalid replaceById: ' . $this->getParameter('replaceById'));
		}
		
		$langs = RequestContext::getInstance()->getSupportedLanguages();
		$suffixes = array('Websites', 'Topics', 'Pages', 'Templates');
		foreach ($suffixes as $suffix)
		{
			Framework::info(__METHOD__ . ' update ' . $suffix);
			$batchPath = f_util_FileUtils::buildRelativePath('modules', 'theme', 'lib', 'bin', 'batchReplacePagetemplateIn' . $suffix . '.php');
			foreach ($langs as $lang)
			{
				do
				{
					$result = f_util_System::execHTTPScript($batchPath, array($toReplace->getId(), $replaceBy->getId(), $chunkSize, $lang));
					if (f_util_StringUtils::endsWith($result, 'END'))
					{
						break;
					}
					elseif (!f_util_StringUtils::endsWith($result, 'CONTINUE'))
					{
						throw new Exception($result);
					}
					$this->plannedTask->ping();
				}
				while (true);
			}
		}
	}
}