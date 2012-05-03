<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */
	defined('_JEXEC') or die ('Restricted Access');


jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');

class JFormFieldEasyTableFields extends JFormFieldList
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	public $type = 'EasyTableFields';

	protected function getOptions()
	{
		$db = JFactory::getDBO();
		$result ='';

		// Get our menu item ID
		$Ap = JFactory::getApplication();
		$jinput = $Ap->input;
		$id = $jinput->get('id', null);
		$theOpt = $jinput->get('option','No Table Option');

		if($theOpt == 'com_menus')
		{
			$menus = $Ap->getMenu('site');
			$menuItem = $menus->getItem($id);
			if($menuItem) {
				$id = $menuItem->params->get('id',0);
			}
			else
			{
				$id = 0;
			}
		}

		if($id)
		{
			$query = $db->getQuery(true);
			$query->select('id as value');
			$query->select('label as text');
			$query->from('#__easytables_table_meta');
			$query->where($db->quoteName('easytable_id') . ' = ' . $id);
			$query->order($db->quoteName('position'));

			$db->setQuery($query);
			$options = $db->loadObjectList();
			$noneSelected = new stdClass();
			$noneSelected->value = 0;
			$noneSelected->text = '-- '.JText::_( 'COM_EASYTABLEPRO_LABEL_NONE_SELECTED' ).' --';
			array_splice($options,0,0,array($noneSelected));
		}
		else
		{
			$options = array(JText::_("Select A Table first..."));
		}
		return $options;
	}
}
