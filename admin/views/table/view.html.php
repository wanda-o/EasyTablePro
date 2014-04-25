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

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/general.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/tablefunctions.php';

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
	protected $state;

	protected $item;

	protected $form;

	protected $canDo;

	protected $published;

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
		// Get our Joomla Tag and our canDo's
		$this->jvtag = ET_General_Helper::getJoomlaVersionTag();
		$this->canDo = ET_General_Helper::getActions();

		// Get the Data
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->state = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return;
		}

		// Setup the toolbar etc
		$this->addToolBar($this->item, $this->canDo);
		$this->addCSSEtc();

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
		if (isset($this->item->params))
		{
			$params = $this->item->params;
		}
		else
		{
			$params = '';
		}

		$this->params = $params;

		// Max file size for uploading
		$umfs = ET_General_Helper::umfs();

		// Get the max file size for uploads from Pref's, default to servers PHP setting if not found or > greater than server allows.
		$maxFileSize = ($umfs > $this->state->params->get('maxFileSize')) ? $umfs : $this->state->params->get('maxFileSize', $umfs);

		$this->maxFileSize = $maxFileSize;

		parent::display($tpl);
	}

	/**
	 * Sets up our toolbar for the view.
	 *
	 * @param   EasyTableProModelTable  $item   The current table.
	 *
	 * @param   JObject                 $canDo  The permission object.
	 *
	 * @return  void
	 *
	 * @since   1.1
	 */
	private function addToolbar($item, $canDo)
	{
		JHTML::_('behavior.tooltip');

		$jinput = JFactory::getApplication()->input;
		$jinput->set('hidemainmenu', true);
		$user		= JFactory::getUser();

		$isNew		= ($item->id == 0);
		$checkedOut	= !($item->checked_out == 0 || $item->checked_out == $user->get('id'));

		if ($canDo->get('core.edit') || $canDo->get('core.create'))
		{
			$tbarTitle = $isNew ? JText::_('COM_EASYTABLEPRO_TABLE_VIEW_TITLE_NEW') : JText::_('COM_EASYTABLEPRO_TABLE_VIEW_TITLE');
			JToolBarHelper::title($tbarTitle, 'easytablepro-editrecords');
			JToolBarHelper::apply('table.apply');
			JToolBarHelper::save('table.save');
		}

		if (!$item->etet && !$checkedOut && ($canDo->get('core.create')))
		{
			JToolBarHelper::save2new('table.save2new');
		}

		if ((!$item->etet) && $canDo->get('easytablepro.import'))
		{
			JToolBarHelper::divider();
			$importURL = 'index.php?option=com_easytablepro&amp;view=upload&amp;task=upload&amp;id=' . $item->id . '&amp;tmpl=component';
			$width = 700;
			$height = JDEBUG ? 495 : 425;

			$toolbar = JToolBar::getInstance('toolbar');

			if ($this->jvtag == 'j2')
			{
				$toolbar->appendButton('Popup', 'easytablpro-uploadTable', 'COM_EASYTABLEPRO_LABEL_UPLOAD', $importURL, $width, $height);
			}
			else
			{
				// Load our StandardPop button
				JLoader::register('JToolbarButtonStandardpop', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/standardpop.php');
				$toolbar->appendButton(
					'Standardpop',
					'easytablpro-uploadTable',
					'COM_EASYTABLEPRO_LABEL_UPLOAD',
					$importURL,
					$width,
					$height
				);
			}
		}

		JToolBarHelper::divider();

		if ($canDo->get('easytablepro.structure') && !$item->etet)
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
}
