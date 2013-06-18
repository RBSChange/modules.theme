<?php
/**
 * @package modules.theme
 */
class theme_GetImageAction extends f_action_BaseAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$path = $request->getParameter('path');
		try
		{
			if (f_util_StringUtils::isNotEmpty($path) && strtolower(substr($path, -4)) != '.php')
			{
				if (Framework::isInfoEnabled())
				{
					Framework::info(__METHOD__ . ':' . $path);
				}
				$pathParts = explode('/', $path);
				if (count($pathParts) > 1)
				{
					$theme = $pathParts[0];
					$imagePath = FileResolver::getInstance()
						->setPackageName('themes_' . $theme)
						->setDirectory('image')
						->getPath(implode(DIRECTORY_SEPARATOR, array_slice($pathParts, 1)));
					if ($imagePath != null)
					{
						// Validate that the image is really in themes/$theme/image.
						$needle = DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . 'image' . DIRECTORY_SEPARATOR;
						if (false !== strpos(realpath($imagePath), $needle))
						{
							$link = f_util_FileUtils::buildWebeditPath('media', 'themes', $path);
							f_util_FileUtils::mkdir(dirname($link));
							f_util_FileUtils::symlink($imagePath, $link, f_util_FileUtils::OVERRIDE);
							MediaHelper::outputHeader($link, null, false);
							readfile($link);
							return View::NONE;
						}
					}
				}
			}
		}
		catch (Exception $e)
		{
			Framework::exception($e);
		}
		f_web_http_Header::setStatus(404);
		return View::NONE;
	}

	/**
	 * @return boolean
	 */
	public function isSecure()
	{
		return false;
	}

	/**
	 * @return int|string
	 */
	public function getRequestMethods()
	{
		return Request::GET;
	}
}