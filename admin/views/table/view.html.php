<?php
/**
 * @package	   EasyTables
 * @author	   Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author	   Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

jimport( 'joomla.application.component.view');
require_once ''.JPATH_COMPONENT_ADMINISTRATOR.'/helpers/managerfunctions.php';

/**
 * HTML View class for the EasyTables Component
 *
 * @package	   EasyTables
 * @subpackage Views
 */

class EasyTableProViewTable extends JView
{
	/**
	 * EasyTable view display method
	 * 
	 * @return void
	 **/
	function display($tpl = null)
	{
		// get the Data
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

		// Should we be here?
		$this->canDo = ET_MgrHelpers::getActions($item->id);

		// Setup the toolbar etc
		$this->addToolBar();
		$this->addCSSEtc();
////
		//get the current task
		$et_task = JRequest::getVar('task');

		if(!$this->item->ettd)	// Do not allow it to be published until a table is created.
		{
			$this->assignRef('published', JHTML::_('select.booleanlist', 'published', 'class="inputbox" disabled="disabled"', $this->item->published ));
		}
		else
		{
			$this->assignRef('published', JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $this->item->published ));
		}
		// Parameters for this table instance
		$params = $item->params;
		$this->assignRef('params', $params);

		// Max file size for uploading

		$umfs = ET_MgrHelpers::umfs();
		//Get the max file size for uploads from Pref's, default to servers PHP setting if not found or > greater than server allows.

		$maxFileSize = ($umfs > $state->params->get('maxFileSize')) ? $umfs : $state->params->get('maxFileSize',$umfs);

		$this->assign('maxFileSize', $maxFileSize);


		if($this->item->ettd)
		{
			$this->assignRef('ettd_record_count',$ettd_record_count);
		}

		parent::display($tpl);
	}

	private function addToolbar()
	{
		JHTML::_('behavior.tooltip');

		$jinput = JFactory::getApplication()->input;
		$jinput->set('hidemainmenu', true);
		$canDo	    = $this->canDo;
		$user		= JFactory::getUser();

		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));

		if($canDo->get('core.edit') || $canDo->get('core.create')) {
			JToolBarHelper::title($isNew ? JText::_('COM_EASYTABLEPRO_TABLE_VIEW_TITLE_NEW') : JText::_('COM_EASYTABLEPRO_TABLE_VIEW_TITLE'), 'easytablepro');
			JToolBarHelper::apply('table.apply');
			JToolBarHelper::save('table.save');
		} 
		if (!$checkedOut && ($canDo->get('core.create'))) {
			JToolBarHelper::save2new('table.save2new');
		}
		JToolBarHelper::divider();

		if($canDo->get('easytablepro.structure')) JToolBarHelper::custom( 'modifyTable', 'easytablpro-modifyTable', 'easytablpro-modifyTable', 'Modify Structure', false, false );
		JToolBarHelper::divider();

		JToolBarHelper::cancel('table.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
		JToolBarHelper::divider();

		JToolBarHelper::help('COM_EASYTABLEPRO_MANAGER_HELP',false,'http://seepeoplesoftware.com/products/easytablepro/1.1/help/manager.html');

	}

	private function addCSSEtc()
	{
		//get the document
		$doc = JFactory::getDocument();

		// First add CSS to the document
		$doc->addStyleSheet('/media/com_easytablepro/css/easytable.css');

		// Get the document object
		$document =JFactory::getDocument();
		
		// Load the defaults first so that our script loads after them
		JHtml::_('behavior.framework', true);
		JHtml::_('behavior.tooltip');
		JHtml::_('behavior.multiselect');
		
		// Then add JS to the document‚ - make sure all JS comes after CSS
		// Tools first
		$jsFile = ('/media/com_easytablepro/js/atools.js');
		$document->addScript($jsFile);
		ET_MgrHelpers::loadJSLanguageKeys($jsFile);
		// Component view specific next...
		$jsFile = ('/media/com_easytablepro/js/easytabletable.js');
		$document->addScript($jsFile);
		ET_MgrHelpers::loadJSLanguageKeys($jsFile);
		
	}

	function getTableIDForName ($tableName)
	{
		// Get a database object
		$db = JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,JText::_("Couldn't talk to the database while trying to get a table ID for table:").' '.$tableName);
		}
		// Get the id for this table
		$query = "SELECT id FROM ".$db->nameQuote('#__easytables')." WHERE `datatablename` ='".$tableName."'";
		$db->setQuery($query);

		$id = $db->loadResult();
		if($id) {
			return $id;
		} else {
			return 0;
		}
	}

	/*
	 * getListView	- accepts the name of an element and a flog
	*				- returns img url for either the tick or the X used in backend components
	*/
	function getListViewImage ($rowElement, $flag=0)
	{
		$btn_title = '';
		if(substr($rowElement,0,4)=='list')
		{
			$btn_title = JText::_( "COM_EASYTABLEPRO_TABLE_TOGGLE_APPEARS_IN_LIST_TT" );
		}
		elseif(substr($rowElement,7,4) == 'link')
		{
			$btn_title = JText::_( "COM_EASYTABLEPRO_TABLE_TURN_ON_DETAIL_LINK_TT" );
		}
		elseif(substr($rowElement,0,6)=='detail')
		{
			$btn_title = JText::_( "COM_EASYTABLEPRO_TABLE_TURN_ON_IN_DETAIL_VIEW_TT" );
		}
		elseif(substr($rowElement,0,6)=='search')
		{
			$btn_title = JText::_( 'COM_EASYTABLEPRO_TABLE_TOGGLE_FIELD_SEARCH_VISIBILITY_TT' );
		}

		if($flag)
		{
			$theImageString = 'tick.png';
		}
		else
		{
			$theImageString = 'publish_x.png';
		}

		$theListViewImage = '<img src="/media/com_easytablepro/images/'.$theImageString.'" name="'.$rowElement.'_img" border="0" title="'.$btn_title.'" alt="'.$btn_title.'" class="hasTip"/>';

		return($theListViewImage);
	}

	function toggleState( &$row, $i, $imgY = 'tick.png', $imgX = 'publish_x.png', $prefix='' )
	{
		$img	= $row->published ? $imgY : $imgX;
		$task	= $row->published ? 'unpublish' : 'publish';
		$alt	= $row->published ? JText::_( 'JPUBLISHED' ) : JText::_( 'COM_EASYTABLEPRO_UNPUBLISHED' );
		$action = $row->published ? JText::_( 'COM_EASYTABLEPRO_TABLE_TURN_OFF_SETTING' ) : JText::_( 'COM_EASYTABLEPRO_TABLE_TURN_ON_SETTING' );
		$href	= '<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i
		.'\',\''. $prefix.$task .'\')" title="'. $action .'"><img src="/media/com_easytablepro/images/'
		. $img .'" border="0" alt="'. $alt .'" /></a>';
		return $href;
	}

	function getTypeList ($id, $selectedType=0)
	{
		$selectOptionText =	 '<select name="type'.$id.'" onchange="com_EasyTablePro.Table.changeTypeWarning()" class="hasTip" title="'.JText::_( 'COM_EASYTABLEPRO_TABLE_FIELD_TYPE_DESC' ).'">';// start our html select structure
		$selectOptionText .= '<option value="0" '.($selectedType ? '':'selected="selected"').'>'.JText::_('COM_EASYTABLEPRO_TABLE_LABEL_TEXT').'</option>';				// Type 0 = Text
		$selectOptionText .= '<option value="1" '.($selectedType==1 ? 'selected="selected"':'').'>'.JText::_('COM_EASYTABLEPRO_TABLE_LABEL_IMAGE').'</option>';			// Type 1 = Image URL
		$selectOptionText .= '<option value="2" '.($selectedType==2 ? 'selected="selected"':'').'>'.JText::_('COM_EASYTABLEPRO_TABLE_LABEL_LINK_URL').'</option>';	// Type 2 = Fully qualified URL
		$selectOptionText .= '<option value="3" '.($selectedType==3 ? 'selected="selected"':'').'>'.JText::_('COM_EASYTABLEPRO_TABLE_LABEL_EMAIL').'</option>';			// Type 3 = Email address
		$selectOptionText .= '<option value="4" '.($selectedType==4 ? 'selected="selected"':'').'>'.JText::_('COM_EASYTABLEPRO_TABLE_LABEL_NUMBER').'</option>';		// Type 4 = Numbers
		$selectOptionText .= '<option value="5" '.($selectedType==5 ? 'selected="selected"':'').'>'.JText::_('COM_EASYTABLEPRO_LABEL_DATE').'</option>';			// Type 5 = Dates
		$selectOptionText .= '</select>';																						// close our html select structure

		return($selectOptionText);
	}

	function getFieldOptions ($params=null)
	{
		$fieldOptions = '';
		if ( isset ($params) )
		{
			$paramsObj = new JRegistry;
			$paramsObj->loadString($params);
			$rawFieldOptions = $paramsObj->get('fieldoptions','');
			if(strlen ( $rawFieldOptions )) {
				if(substr($rawFieldOptions,0,1) == 'x') {
					$unpackedFieldOptions = htmlentities ( pack("H*", substr($rawFieldOptions,1) ));
					$fieldOptions = $unpackedFieldOptions;
				} else {
					$fieldOptions = $rawFieldOptions;
				}
			}
		}
		return($fieldOptions);
	}

}// class
