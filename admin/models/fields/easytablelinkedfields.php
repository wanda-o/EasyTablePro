<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */
defined('_JEXEC') or die('Restricted Access');

JFormHelper::loadFieldClass('list');
/**
 * JFormFieldEasyTable provides the options for the Table selection menu.
 *
 * @package     EasyTables
 *
 * @subpackage  Model/Fields
 *
 * @since       1.1
 */
class JFormFieldEasyTableLinkedFields extends JFormFieldList
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $type = 'EasyTableLinkedFields';

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

		// Get setup with menu item ID etc
		$Ap = JFactory::getApplication();
		$jinput = $Ap->input;
		$id = $jinput->get('id', null);
		$linkedTabledId = 0;

		// If we have a table ID retrieve the list of fields from the our EasyTables Table Meta
		if ($id)
		{
			// Load our table so we can get it's params
			$table = JTable::getInstance('Table', 'EasyTableProTable');
			$table->load($id);
			$params = new JRegistry;
			$params->loadString($table->params);
			$linkedTabledId = $params->get('id', 0);
		}

		if ($id && $linkedTabledId)
		{
			$query = $db->getQuery(true);
			$query->select($db->quoteName('id') . ' as ' . $db->quoteName('value'));
			$query->select('label as text');
			$query->from('#__easytables_table_meta');
			$query->where($db->quoteName('easytable_id') . ' = ' . $linkedTabledId);
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
