<?php
abstract class theme_BindingHelper
{
	/**
	 * @var theme_persistentdocument_theme
	 */
	private static $currentTheme;
	
	/**
	 * @var string
	 */
	private static $codename;
	
	/**
	 * @var array
	 */
	private static $variables;
	
	/**
	 * @param theme_persistentdocument_theme $theme
	 */
	public static function setCurrentTheme($theme)
	{
		self::$currentTheme = $theme;
		self::$codename = $theme->getCodename();
		self::$variables = array();
	}
		
	public static function XSLGetImage($elementArray)
	{
		$element = $elementArray[0];
		$imageURL = $element->getAttribute('image');
		return str_replace('{IconsBase}', MediaHelper::getIconBaseUrl(), $imageURL);
	}	
	
	
	public static function XSLGetLabel($elementArray)
	{
		$element = $elementArray[0];
		if ($element->hasAttribute('labeli18n'))
		{
			$key = "&" . $element->getAttribute('labeli18n') . ";";
			return f_Locale::translate($key);
		}
		else if ($element->hasAttribute('label'))
		{
			$key = $element->getAttribute('label');
			return f_Locale::translate($key);
		} 
		else if ($element->hasAttribute('name'))
		{
			$key = "&themes." . self::$codename .".skin." . $element->getAttribute('name') . ";";
			return f_Locale::translate($key);
		}
		return '';
	}
	
	public static function XSLGetBaseBinding($elementArray)
	{
		return uixul_lib_BindingObject::getUrl('modules.skin.cEditor#cVariableSections');
	}
	
	
	public static function XSLSetDefaultVarInfo($elementArray)
	{
		$element = $elementArray[0];
		$name = $element->getAttribute("name");
		if (!$name || in_array($name, self::$variables))
		{
			throw new Exception('Invalid empty field name:' . $name);
		}
		self::$variables[] = $name;
		$element->setAttribute('id', 'themes_' . self::$codename . '_' . $name);		
		if (!$element->hasAttribute('type'))
		{
			if ($element->hasAttribute('allowfile'))
			{
				$element->setAttribute('type', 'imagecss');
			}
			else
			{
				$element->setAttribute('type', 'text');
			}			
		}
		
		if ($element->getAttribute('type') == 'imagecss')
		{
			$element->setAttribute('moduleselector', 'media');
			$element->setAttribute('allow', 'modules_media_media');
			$element->setAttribute('allowfile', 'true');
			$element->setAttribute('mediafoldername', 'Inbox_' . self::$codename);
		}
		
		$helpKey = "&themes." . self::$codename .".skin." . ucfirst($name) . "-help;";
		$help = f_Locale::translate($helpKey, null, null, false);
		if ($help)
		{
			$element->setAttribute('shorthelp', $help);
		}
		else
		{
			$element->setAttribute('hidehelp', true);
		}
		return '';
	}	
	
	public static function XSLVariables()
	{
		return JsonService::getInstance()->encode(self::$variables);
	}
}