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
class JFormFieldEasyTable extends JFormFieldList
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $type = 'EasyTable';

	/**
	 * getOptions() provides the options for each PUBLISHED table.
	 *
	 * @return  array
	 *
	 * @since   1.1
	 */
	protected function getOptions()
	{
		// Initialise variables.
		$db = JFactory::getDBO();

		// Get array of tables to build each option from...
		$optionsQuery = $db->getQuery(true);
		$optionsQuery->select('id as value, easytablename as text');
		$optionsQuery->from('#__easytables');
		$optionsQuery->where('published = 1');
		$optionsQuery->orderby('easytablename');

		$db->setQuery($optionsQuery);
		$options = $db->loadObjectList();

		// Don't forget to prefix it with a "None Selected" options
		$noneSelected = new stdClass;
		$noneSelected->value = '';
		$noneSelected->text = '-- ' . JText::_('COM_EASYTABLEPRO_LABEL_NONE_SELECTED') . ' --';
        $noneSelected->disable = "true";
		array_splice($options, 0, 0, array($noneSelected));

		return $options;
	}
}
