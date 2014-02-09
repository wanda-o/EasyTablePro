<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.view');
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/general.php';

/**
 * HTML View class for the EasyTables Component
 *
 * @package     EasyTablePro
 *
 * @subpackage Views
 *
 * @since       1.0
 */

class EasyTableProViewTable extends JViewLegacy
{
	/**
	 * EasyTable view display method.
	 *
	 * @param   string  $tpl  Tmpl file name.
	 *
	 * @return void
	 *
	 * @since   1.0
	 **/
	public function display($tpl = null)
	{
		// Get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');
		$state = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		// Assign the Data
		$this->form  = $form;
		$this->item  = $item;
		$this->state = $state;

		// Change the model
		$this->get('Item', '');

		// Should we be here?
		$this->canDo = ET_General_Helper::getActions($this->item->id);

		// Setup the toolbar etc
		$this->addToolBar();
		$this->addCSSEtc();

		// Get the current task
		$et_task = JRequest::getVar('task');

		// Do not allow it to be published until a table is created.
		if (!isset($this->item->ettd) or !$this->item->ettd)
		{
			$this->published = JHTML::_('select.booleanlist', 'published', 'class="inputbox" disabled="disabled"', $this->item->published);
			$this->item->ettd = '';
		}
		else
		{
			$this->published = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $this->item->published);
		}

		// Parameters for this table instance
		if (isset($item->params))
		{
			$params = $item->params;
		}
		else
		{
			$params = '';
		}

		$this->assignRef('params', $params);

		// Max file size for uploading
		$umfs = ET_General_Helper::umfs();

		// Get the max file size for uploads from Pref's, default to servers PHP setting if not found or > greater than server allows.
		$maxFileSize = ($umfs > $state->params->get('maxFileSize')) ? $umfs : $state->params->get('maxFileSize', $umfs);

		$this->assign('maxFileSize', $maxFileSize);


		if (isset($this->item->ettd))
		{
			$this->assignRef('ettd_record_count', $ettd_record_count);
		}

		parent::display($tpl);
	}

	/**
	 * Sets up our toolbar for the view.
	 *
	 * @return  void
	 *
	 * @since   1.1
	 */
	private function addToolbar()
	{
		JHTML::_('behavior.tooltip');

		$jinput = JFactory::getApplication()->input;
		$jinput->set('hidemainmenu', true);
		$canDo	    = $this->canDo;
		$user		= JFactory::getUser();

		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));

		if ($canDo->get('core.edit') || $canDo->get('core.create'))
		{
			$tbarTitle = $isNew ? JText::_('COM_EASYTABLEPRO_TABLE_VIEW_TITLE_NEW') : JText::_('COM_EASYTABLEPRO_TABLE_VIEW_TITLE');
			JToolBarHelper::title($tbarTitle, 'easytablepro-editrecords');
			JToolBarHelper::apply('table.apply');
			JToolBarHelper::save('table.save');
		}

		if (!$this->item->etet && !$checkedOut && ($canDo->get('core.create')))
		{
			JToolBarHelper::save2new('table.save2new');
		}

		if ((!$this->item->etet) && $canDo->get('easytablepro.import'))
		{
			JToolBarHelper::divider();
			$importURL = 'index.php?option=com_easytablepro&amp;view=upload&amp;task=upload&amp;id=' . $this->item->id . '&amp;tmpl=component';

			$toolbar = JToolBar::getInstance('toolbar');

			if (JDEBUG)
			{
				$toolbar->appendButton('Popup', 'easytablpro-uploadTable', 'COM_EASYTABLEPRO_LABEL_UPLOAD', $importURL, 700, 495);
			}
			else
			{
				$toolbar->appendButton('Popup', 'easytablpro-uploadTable', 'COM_EASYTABLEPRO_LABEL_UPLOAD', $importURL, 700, 425);
			}
		}

		JToolBarHelper::divider();

		if ($canDo->get('easytablepro.structure') && !$this->item->etet)
		{
			JToolBarHelper::custom(
				'modifyTable',
				'easytablpro-modifyTable',
				'easytablpro-modifyTable',
				'COM_EASYTABLEPRO_LABEL_MODIFY_STRUCTURE',
				false,
				false
			);
			JToolBarHelper::divider();
		}

		JToolBarHelper::cancel('table.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
		JToolBarHelper::divider();

		JToolBarHelper::help('COM_EASYTABLEPRO_MANAGER_HELP', false, 'http://seepeoplesoftware.com/products/easytablepro/1.1/help/manager.html');

	}

	/**
	 * Adds any CSS and JS files to the document head .
	 *
	 * @return   void
	 *
	 * @since    1.1
	 */
	private function addCSSEtc()
	{
		// Get the document
		$doc = JFactory::getDocument();

		// First add CSS to the document
		$doc->addStyleSheet(JURI::root() . 'media/com_easytablepro/css/easytable.css');

		// Get the document object
		$document = JFactory::getDocument();

		// Load the defaults first so that our script loads after them
		JHtml::_('behavior.framework', true);
		JHtml::_('behavior.tooltip');
		JHtml::_('behavior.multiselect');

		// Then add JS to the document‚ - make sure all JS comes after CSS
		// Tools first
		$jsFile = ('media/com_easytablepro/js/atools.js');
		ET_General_Helper::loadJSLanguageKeys('/' . $jsFile);
		$document->addScript(JURI::root() . $jsFile);

		// Component view specific next...
		$jsFile = ('media/com_easytablepro/js/easytabletable.js');
		ET_General_Helper::loadJSLanguageKeys('/' . $jsFile);
		$document->addScript(JURI::root() . $jsFile);
	}

	/**
	 * Attempts to return the data table name for an external/linked table.
	 *
	 * @param   string  $tableName  The table name in the J! Database.
	 *
	 * @return  int     Either the Id of the table or 0 if not found.
	 *
	 * @since   1.1
	 */
	public function getTableIDForName ($tableName)
	{
		// Get a database object
		$db = JFactory::getDBO();

		if (!$db)
		{
			JError::raiseError(
				500,
				JText::sprintf(
					'COM_EASYTABLEPRO_TABLE_COULDNT_GET_THE_DATABASE_WHILE_TRYING_TO_GET_A_TABLE_ID_FOR_TABLE_X',
					$tableName
				)
			);
		}

		// Get the id for this table
		$query = "SELECT id FROM " . $db->quoteName('#__easytables') . " WHERE `datatablename` ='" . $tableName . "'";
		$db->setQuery($query);

		$id = $db->loadResult();

		if ($id)
		{
			return $id;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * getListView	- accepts the name of an element and a flag
	*				- returns img url for either the tick or the X used in backend components
	 *
	 * @param   string  $rowElement  Name of the row element.
	 *
	 * @param   int     $flag        Current state of element.
	 *
	 * @return  html
	 *
	 * @since   1.0
	*/
	public function getListViewImage ($rowElement, $flag=0)
	{
		$btn_title = '';

		if (substr($rowElement, 0, 4) == 'list')
		{
			$btn_title = JText::_('COM_EASYTABLEPRO_TABLE_TOGGLE_APPEARS_IN_LIST_TT');
		}
		elseif (substr($rowElement, 7, 4) == 'link')
		{
			$btn_title = JText::_('COM_EASYTABLEPRO_TABLE_TURN_ON_DETAIL_LINK_TT');
		}
		elseif (substr($rowElement, 0, 6) == 'detail')
		{
			$btn_title = JText::_('COM_EASYTABLEPRO_TABLE_TURN_ON_IN_DETAIL_VIEW_TT');
		}
		elseif (substr($rowElement, 0, 6) == 'search')
		{
			$btn_title = JText::_('COM_EASYTABLEPRO_TABLE_TOGGLE_FIELD_SEARCH_VISIBILITY_TT');
		}

		if ($flag)
		{
			$theImageString = 'tick.png';
		}
		else
		{
			$theImageString = 'publish_x.png';
		}

		$theListViewImage = '<img src="' . JURI::root() . 'media/com_easytablepro/images/' . $theImageString . '" name="'
			. $rowElement . '_img" border="0" title="' . $btn_title . '" alt="' . $btn_title . '" class="hasTip"/>';

		return($theListViewImage);
	}

	/**
	 * Creates the html for the state toggle.
	 *
	 * @param   int     &$row    The current row.
	 *
	 * @param   int     $i       List index.
	 *
	 * @param   string  $imgY    Yes/Published image name/path.
	 *
	 * @param   string  $imgX    No/Unpublished image name/path.
	 *
	 * @param   string  $prefix  Task prefix.
	 *
	 * @return string
	 */
	protected function toggleState( &$row, $i, $imgY = 'tick.png', $imgX = 'publish_x.png', $prefix='' )
	{
		$img	= $row->published ? $imgY : $imgX;
		$task	= $row->published ? 'unpublish' : 'publish';
		$alt	= $row->published ? JText::_('JPUBLISHED') : JText::_('COM_EASYTABLEPRO_UNPUBLISHED');
		$action = $row->published ? JText::_('COM_EASYTABLEPRO_TABLE_TURN_OFF_SETTING') : JText::_('COM_EASYTABLEPRO_TABLE_TURN_ON_SETTING');
		$href	= '<a href="javascript:void(0);" onclick="return listItemTask(\'cb' . $i . '\',\''
			. $prefix . $task . '\')" title="' . $action . '"><img src="' . JURI::root()
			. 'media/com_easytablepro/images/' . $img . '" border="0" alt="' . $alt . '" /></a>';

		return $href;
	}

	// @todo convert this to use JHTML::_('select.option')

	/**
	 * Creates HTML select for field data types.
	 *
	 * @param   int  $id            Field Id.
	 *
	 * @param   int  $selectedType  Column's data type.
	 *
	 * @return string
	 */
	protected function getTypeList ($id, $selectedType=0)
	{
		// Start html select structure
		$selectOptionTxt =	'<select name="type' . $id . '" onchange="com_EasyTablePro.Table.changeTypeWarning()" class="hasTip" title="';
		$selectOptionTxt .= JText::_('COM_EASYTABLEPRO_TABLE_FIELD_TYPE_DESC') . '">';

		// Type 0 = Text
		$selectOptionTxt .= '<option value="0" ' . ($selectedType ? '':'selected="selected"') . '>' .
							JText::_('COM_EASYTABLEPRO_TABLE_LABEL_TEXT') . '</option>';

		// Type 1 = Image URL
		$selectOptionTxt .= '<option value="1" ' . ($selectedType == 1 ? 'selected="selected"':'') . '>' .
							JText::_('COM_EASYTABLEPRO_TABLE_LABEL_IMAGE') . '</option>';

		// Type 2 = Fully qualified URL
		$selectOptionTxt .= '<option value="2" ' . ($selectedType == 2 ? 'selected="selected"':'') . '>' .
							JText::_('COM_EASYTABLEPRO_TABLE_LABEL_LINK_URL') . '</option>';

		// Type 3 = Email address
		$selectOptionTxt .= '<option value="3" ' . ($selectedType == 3 ? 'selected="selected"':'') . '>' .
							JText::_('COM_EASYTABLEPRO_TABLE_LABEL_EMAIL') . '</option>';

		// Type 4 = Numbers
		$selectOptionTxt .= '<option value="4" ' . ($selectedType == 4 ? 'selected="selected"':'') . '>' .
							JText::_('COM_EASYTABLEPRO_TABLE_LABEL_NUMBER') . '</option>';

		// Type 5 = Dates
		$selectOptionTxt .= '<option value="5" ' . ($selectedType == 5 ? 'selected="selected"':'') . '>' .
							JText::_('COM_EASYTABLEPRO_LABEL_DATE') . '</option>';

		// Close html select structure
		$selectOptionTxt .= '</select>';

		return($selectOptionTxt);
	}

	/**
	 * Extract field options from the fields params block.
	 *
	 * @param   string  $params  The raw params for the current column/field.
	 *
	 * @return  null|string
	 *
	 * @since   1.0
	 */
	protected function getFieldOptions ($params=null)
	{
		$fieldOptions = '';

		if (isset ($params))
		{
			$paramsObj = new JRegistry;
			$paramsObj->loadString($params);
			$rawFieldOptions = $paramsObj->get('fieldoptions', '');

			if (strlen($rawFieldOptions))
			{
				if (substr($rawFieldOptions, 0, 1) == 'x')
				{
					$unpackedFieldOptions = htmlentities(pack("H*", substr($rawFieldOptions, 1)));
					$fieldOptions = $unpackedFieldOptions;
				}
				else
				{
					$fieldOptions = $rawFieldOptions;
				}
			}
		}

		return($fieldOptions);
	}
}
