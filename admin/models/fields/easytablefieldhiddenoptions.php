<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */
defined('_JEXEC') or die('Restricted Access');

/**
 * JFormFieldEasyTable provides the options for the field selection menu, once a table has been specified.
 *
 * @package     EasyTables
 *
 * @subpackage  Model/Fields
 *
 * @since       1.1
 */
class JFormFieldEasyTableFieldHiddenOptions extends JFormField
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $type = 'EasyTableFieldHiddenOptions';




    protected function getInput()
    {
        // Get any defined class
        $class = ' class="table';
        $class = !empty($this->class) ? $class . ' ' . $this->class : $class;
        $class .= '"';

        $options = $this->getOptions();

        if (count($options) > 1)
        {
            // Add our JS to the page
            $jAp = JFactory::getApplication();
            $doc = $jAp->getDocument();
            $doc->addScript(JUri::base(true) . '/components/com_easytablepro/models/fields/easytablefieldhiddenoptions.js');

            // Initialisations
            $elements = array();
            $allFieldIds = '';

            // Get any existing settings
            $field_hidden_options_values = $this->form->getValue('params.field_hidden_options_values');
            if ($field_hidden_options_values != null)
            {
                $options = $this->updateWithExisting($options, json_decode($field_hidden_options_values));
            }

            // Open table
            $elements[] = '<table id="' . $this->id . '" name="' . $this->name .'"' . $class . '/>';

            // Add Headings
            $elements[] = '<thead><th>' . JText::_('COM_EASYTABLEPRO_LABEL_LIST_VIEW') . '</th><th title="' . JText::_('COM_EASYTABLEPRO_MENU_FOOTABLE_SET_HIDDEN_DESC') . '" colspan="3">' . JText::_('COM_EASYTABLEPRO_MENU_FOOTABLE_SET_HIDDEN') . '</th></thead>';

            // Add rows
            foreach ($options as $field) {
                // Store our field id
                $allFieldIds .= $allFieldIds == '' ? '' : ',';
                $allFieldIds .= $field->id;

                // Get our visibility settings
                list($list_view_checked, $mobile_checked, $tablet_checked, $all_checked) = $this->getVisiblity($field);

                // Begin row
                $elements[] = '<tr>';

                // Build Field label cell
                $elements[] = '<td>';
                $elements[] = '<label class="checkbox"><input type="checkbox" id="etp_FieldOptions_list_view_' . $field->id . '" value="' . $field->fieldalias . '"' . $list_view_checked . '>' . $field->label . '</label>';
                $elements[] = '</td>';

                // Build Field Visibility cell
                // $all_checked = !$field->list_view ? ' checked' : '';

                $elements[] = '<td><div class="control-group" >';
                $elements[] = '<label class="checkbox control-label icon-mobile"><input type="checkbox" name="etp_FieldOptions_hidden_'
                    . $field->id . '" value="phone" class="controls" title="' . JText::_('COM_EASYTABLEPRO_MENU_FIELD_HIDDEN_ON_PHONE') . '"'
                    . $mobile_checked . '>&nbsp;' . JText::_('COM_EASYTABLEPRO_MENU_FIELD_HIDDEN_ON_PHONE') . '</label>';
                $elements[] = '</div></td>';
                $elements[] = '<td><div class="control-group" >';
                $elements[] = '<label class="checkbox control-label icon-tablet"><input type="checkbox" name="etp_FieldOptions_hidden_'
                    . $field->id . '" value="tablet" class="controls" title="' . JText::_('COM_EASYTABLEPRO_MENU_FIELD_HIDDEN_ON_TABLET') . '"'
                    . $tablet_checked . '>&nbsp;' . JText::_('COM_EASYTABLEPRO_MENU_FIELD_HIDDEN_ON_TABLET') . '</label>';
                $elements[] = '</div></td>';
                $elements[] = '<td><div class="control-group" >';
                $elements[] = '<label class="checkbox control-label icon-screen"><input type="checkbox" name="etp_FieldOptions_hidden_'
                    . $field->id . '" value="all" class="controls" title="' . JText::_('COM_EASYTABLEPRO_MENU_FIELD_HIDDEN_ON_ALL') . '"'
                    . $all_checked . '>&nbsp;' . JText::_('COM_EASYTABLEPRO_MENU_FIELD_HIDDEN_ON_ALL') . '</label>';
                $elements[] = '</div></td>';

                // End row
                $elements[] = '</tr>';
            }


            // Close table
            $elements[] = '</tbody>';
            $elements[] = '</table>';

            // Add our hidden field with the list of fields id's in it.
            $elements[] = '<input type="hidden" id="etp_Fields_allFieldIds" value="' . $allFieldIds . '" />';
            $html = implode('', $elements);
        }
        else
        {
            $html = '<input type="text" disabled value="' . $options[0] . '"/>';
        }

        return $html;
    }

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
			$query->select($db->qn('id'));
            $query->select($db->qn('fieldalias'));
			$query->select($db->qn('label'));
            $query->select($db->qn('list_view'));
            $query->select($db->qn('detail_view'));
			$query->from('#__easytables_table_meta');
			$query->where($db->quoteName('easytable_id') . ' = ' . $id);
			$query->order($db->quoteName('position'));

			$db->setQuery($query);
			$options = $db->loadObjectList('id');
		}
		else
		{
			// Prompt our user to select a table first
			$options = array(JText::_('COM_EASYTABLEPRO_MODEL_FIELDS_SELECT_A_TABLE_FIRST'));
		}

		return $options;
	}

    /**
     * Merges previously saved options into the default settings for the tables fields. We merge the saved settings
     * into those fields that *actually exists* as old settings may contain values for remove/renamed fields while
     * the defaultOptions may contain new fields that didn't exist previously.
     *
     * @param   Array  $defaultOptions   The options reflecting the current state
     *                                   (i.e. may have new flds may be missing some compared to stored settings)
     *
     * @param   Array  $existingOptions  The previously saved options.
     *
     * @return  Array
     */
    protected function updateWithExisting($defaultOptions, $existingOptions)
    {
        foreach ($existingOptions as $option)
        {
            // Check if this saved option has a match in the default options
            if (array_key_exists($option->id, $defaultOptions))
            {
                // OK, now we can update the default option
                $defaultOpt = $defaultOptions[$option->id];
                $defaultOpt->list_view = $option->list ? 1 : 0;
                if (isset($option->hidden))
                {
                    $defaultOpt->hidden = $option->hidden;
                }
                else
                {
                    $defaultOpt->hidden = '';
                }
            }
        }

        return $defaultOptions;
    }

    protected function getVisiblity($field)
    {
        // Simple check for checked state.
        $list_view_checked = $field->list_view ? ' checked' : '';

        // Check for our hidden property
        if (isset($field->hidden))
        {
            $mobile_checked = strpos($field->hidden, 'phone') ? ' checked' : '';
            $tablet_checked = strpos($field->hidden, 'tablet') ? ' checked' : '';
        }
        else
        {
            $mobile_checked = '';
            $tablet_checked = '';
        }

        $all_checked = !$field->list_view ? ' checked' : '';

        // Return our array
        return array($list_view_checked, $mobile_checked, $tablet_checked, $all_checked);
    }
}
