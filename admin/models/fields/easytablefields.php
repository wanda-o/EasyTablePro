<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');

/**
 * JFormFieldEasyTable provides the options for the Table selection menu.
 *
 * @package     EasyTables
 *
 * @subpackage  Model/Fields
 *
 * @since       1.1
 */
class JFormFieldEasyTableFields extends JFormFieldList
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $type = 'EasyTableFields';

	/**
	 * getOptions() provides the options for each field in a table.
	 *
	 * @return  array
	 *
	 * @since   1.1
	 */
	protected function getOptions()
	{
		$db = JFactory::getDBO();
		$result = '';

		// Get our menu item ID
		$Ap = JFactory::getApplication();
		$jinput = $Ap->input;
		$id = $jinput->get('id', null);
		$theOpt = $jinput->get('option', 'No Table Option');

		// Are we being called to setup a menu item?
		if ($theOpt == 'com_menus')
		{
			$menus = $Ap->getMenu('site');
			$menuItem = $menus->getItem($id);

			if ($menuItem)
			{
				$id = $menuItem->query['id'];
			}
			else
			{
				$id = 0;
			}
		}

		// If we have a table ID retreive the list of fields from the our EasyTables Table Meta
		if ($id)
		{
			$query = $db->getQuery(true);
			$query->select('CONCAT(' . $db->quoteName('id') . ',' . '\':\'' . ',' . $db->quoteName('fieldalias') . ') as value');
			$query->select('label as text');
			$query->from('#__easytables_table_meta');
			$query->where($db->quoteName('easytable_id') . ' = ' . $id);
			$query->order($db->quoteName('position'));

			$db->setQuery($query);
			$options = $db->loadObjectList();
			$noneSelected = new stdClass;
			$noneSelected->value = '';
			$noneSelected->text = '-- ' . JText::_('COM_EASYTABLEPRO_LABEL_NONE_SELECTED') . ' --';
			array_splice($options, 0, 0, array($noneSelected));
		}
		else
		{
			// Prompt our user to select a table first
			$options = array(JText::_('COM_EASYTABLEPRO_MODEL_FIELDS_SELECT_A_TABLE_FIRST'));
		}

		return $options;
	}
}
