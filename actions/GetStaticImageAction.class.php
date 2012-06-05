<?php
/**
 * theme_GetStaticImageAction
 * @package modules.theme.actions
 */
class theme_GetStaticImageAction extends change_JSONAction
{
	/**
	 * @param change_Context $context
	 * @param change_Request $request
	 */
	public function _execute($context, $request)
	{
		$result = array();

		$themeCode = $request->getParameter('theme');
		$theme = theme_ThemeService::getInstance()->getByCodeName($themeCode);
		$result[] = array('label' => 'No image', 'value' => 'none');
		if ($theme)
		{
			$prefix = $theme->getCodename() . '/';
			foreach ($theme->getImageArray() as $image) 
			{
				$result[] = array('label' => $prefix . $image->getLabel(), 'value' => 'url(/' . $image->getCodename() .')');
			}
		}
		$result[] = array('label' => '--- --- ---', 'value' => '');	
		$result = array_merge($result, theme_ImageService::getInstance()->getDefaultStaticList());
		return $this->sendJSON($result);
	}
}