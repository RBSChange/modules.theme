<?php
/**
 * theme_ImportThemeAction
 * @package modules.theme.actions
 */
class theme_ImportThemeAction extends f_action_BaseJSONAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		if (!count($_FILES))
		{
			return $this->sendJSONError(f_Locale::translateUI('&modules.theme.bo.errors.Import-file;', true));
		}
		
		if ($_FILES['filename']['error'] != UPLOAD_ERR_OK || substr($_FILES['filename']['name'], - strlen('.zip')) != '.zip')
		{
			return $this->sendJSONError(f_Locale::translateUI('&modules.theme.bo.errors.Import-file-type;', true));
		}
		
		$zipPath = $_FILES['filename']['tmp_name'];
		$folderId = $request->getParameter('folderId');
		try 
		{
			$folder = (intval($folderId) > 0) ? DocumentHelper::getDocumentInstance($folderId, "modules_generic/folder") : null; 	
			$themeCodename = theme_ArchiveService::getInstance()->restore($zipPath);
			if ($themeCodename != null)
			{
				$theme = theme_ModuleService::getInstance()->installTheme($themeCodename, $folder);
				if ($theme)
				{
					$msg = f_Locale::translate('&modules.theme.bo.general.Import-succes;', 
						array('codename' => $theme->getCodename(), 'label' => $theme->getLabel()));
					return $this->sendJSON(array('theme' => $msg));
				}
			}
		}
		catch (Exception $e)
		{
			Framework::exception($e);
		}
		return $this->sendJSONError(f_Locale::translateUI('&modules.theme.bo.errors.Import-error;', true));		
	}
	
	public function isSecure()
	{
		return true;
	}
}