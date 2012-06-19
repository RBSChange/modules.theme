<?php
class theme_PreviewImageAction extends change_Action
{
	
	/**
	 * @param change_Context $context
	 * @param change_Request $request
	 */
	public function _execute ($context, $request)
	{
		try
		{
			$document = $this->getDocumentInstanceFromRequest($request);
			if ($document instanceof theme_persistentdocument_pagetemplate || $document instanceof theme_persistentdocument_theme)
			{
				$thumbnail = $document->getThumbnail();
				if ($thumbnail)
				{
					$pathParts = explode('/', $thumbnail->getProjectpath());
					$imagePath = FileResolver::getInstance()->setPackageName($pathParts[0] . '_' . $pathParts[1])
						->setDirectory($pathParts[2])->getPath(implode(DIRECTORY_SEPARATOR, array_slice($pathParts, 3)));
					
					// Handle thumbnail formating.
					$formatSizeInfo = array();
					if ($request->hasParameter('max-height'))
					{
						$formatSizeInfo['max-height'] = intval($request->getParameter('max-height'));
					}
					if ($request->hasParameter('max-width'))
					{
						$formatSizeInfo['max-width'] = intval($request->getParameter('max-width'));
					}
					
					if (count($formatSizeInfo))
					{
						$resized = f_util_FileUtils::getTmpFile('thumbnail_') . '.' . f_util_ArrayUtils::lastElement(explode('.', $imagePath));
						media_ResizerFormatter::getInstance()->resize($imagePath, $resized, $formatSizeInfo);
						MediaHelper::outputHeader($resized, null, false);
						readfile($resized);
						f_util_FileUtils::unlink($resized);
					}
					else
					{
						MediaHelper::outputHeader($imagePath, null, false);
						readfile($imagePath);
					}
					return change_View::NONE;
				}
			}
		}
		catch (Exception $e)
		{
			Framework::exception($e);
		}
		f_web_http_Header::setStatus(404);
		return change_View::NONE;
	}

	
	/**
	 * @return boolean
	 */	
	public function isSecure()
	{
		return true;
	}
}