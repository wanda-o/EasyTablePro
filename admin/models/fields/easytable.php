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

        // A quick check of table sizes as they affect FooTable views.
        $this->disableBigTablesForFooTableView($options);

        // Don't forget to prefix it with a "None Selected" options
		$noneSelected = new stdClass;
		$noneSelected->value = '';
		$noneSelected->text = '-- ' . JText::_('COM_EASYTABLEPRO_LABEL_NONE_SELECTED') . ' --';
        $noneSelected->disable = "true";
		array_splice($options, 0, 0, array($noneSelected));

		return $options;
	}

    /**
     * We look through and make sure that only the tables that fit within the small size are enabled.
     *
     * @param   array  $options  Array of option objects for our EasyTable <select> element
     *
     *
     */
    protected function disableBigTablesForFooTableView($options)
    {
    // Check if it's a FooTable view
        $fooTableView = strpos($this->form->getValue('link'), 'layout=foo');

        if ($fooTableView != false) {
            // Load our table helper file
            require_once JPATH_ADMINISTRATOR . '/components/com_easytablepro/helpers/general.php';

            // Check the tables against the small table record limit.
            $small_table_limit = JComponentHelper::getParams('com_easytablepro')->get('table_small_sized', 500);

            foreach ($options as $table) {
                $tableObj = ET_General_Helper::getEasyTable($table->value);

                if ($tableObj->record_count > $small_table_limit) {
                    $table->disable = "true";
                }
            }

        }
    }
}
