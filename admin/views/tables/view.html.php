<?php
/**
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

jimport( 'joomla.application.component.view');

require_once ''.JPATH_COMPONENT_ADMINISTRATOR.'/helpers/viewfunctions.php';
require_once ''.JPATH_COMPONENT_ADMINISTRATOR.'/helpers/managerfunctions.php';


/**
 * HTML View class for the EasyTables Component
 *
 * @package    EasyTables
 * @subpackage Views
 */

class EasyTableProViewTables extends JView
{
	function getEditorLink ($locked, $rowId, $tableName, $hasPermission,$userName='')
	{
		$lockText = ($hasPermission ? ($locked ? JText::sprintf( 'COM_EASYTABLEPRO_MGR_DISABLED_TABLE_LOCKED',$userName) : '') : JText::_( 'COM_EASYTABLEPRO_MGR_DISABLED_NO_PERM' ));
		$link_text = JText::_( 'COM_EASYTABLEPRO_MGR_EDIT_PROPERTIES_AND_STRUCTURE_OF' ).' \''.$tableName.'\' '.$lockText ;
		$theEditLink = '<span class="hasTip" title="'.$link_text.'" style="margin-left:10px;" >'.$tableName.'</span>';

		if( !$locked && $hasPermission)
		{
			$theEditLink = '<span class="hasTip" title="'.$link_text.'" style="margin-left:10px;" >'.'<a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$rowId.'\',\'table.edit\');" title="'.$link_text.'" >'.$tableName.'</a></span>';
		}

		return($theEditLink);
	}

	function publishedIcon ($locked, $row, $i, $hasPermission,$userName='')
	{
		$lockText = ($hasPermission ? ($locked ? JText::sprintf( 'COM_EASYTABLEPRO_MGR_DISABLED_TABLE_LOCKED',$userName) : '') : JText::_( 'COM_EASYTABLEPRO_MGR_DISABLED_NO_PERM' ));
		$btn_text = JText::_( ( $row->published ? 'COM_EASYTABLEPRO_MGR_PUBLISHED_BTN':'COM_EASYTABLEPRO_MGR_UNPUBLISHED_BTN') ).' \''.$row->easytablename.'\' '.$lockText;
		$theImageURL = '/media/com_easytablepro/images/'.( ($locked || !$hasPermission) ? 'disabled_' : '' ).($row->published?'publish_g.png':'publish_x.png');
		$theBtn = '<span  class="hasTip" title="'.$btn_text.'" style="margin-left:15px;" ><img src="'.$theImageURL.'" border="0" alt="'.$btn_text.'"></span>';

		if( !$locked && $hasPermission )
		{
			$theBtn = "<span class=\"hasTip\" title=\"$btn_text\" style=\"margin-left:15px;\" >".JHTML::_( 'grid.published',  $row, $i).'</span>';
		}

		return $theBtn;
	}


	function getDataEditorIcon ($locked, $i, $rowId, $tableName, $extTable, $hasPermission,$userName='')
	{
		if($extTable)
		{
			$btn_text = JText::sprintf ( 'COM_EASYTABLEPRO_LINK_LINKED_TABLE_NO_DATA_EDITING' , $tableName);
			$theImageURL = '/media/com_easytablepro/images/disabled_edit.png';
		}
		else
		{
			$lockText = ($hasPermission ? ($locked ? JText::sprintf( 'COM_EASYTABLEPRO_MGR_DISABLED_TABLE_LOCKED',$userName) : '') : JText::_( 'COM_EASYTABLEPRO_MGR_DISABLED_NO_DATA_EDIT_PERM' ));
			$btn_text = JText::_( 'COM_EASYTABLEPRO_MGR_EDIT_DATA_DESC_SEGMENT' ).' \''.$tableName.'\' '.$lockText;
			$theImageURL = '/media/com_easytablepro/images/'.( ($locked || !$hasPermission) ? 'disabled_' : '' ).'edit.png';
		}

		$theEditBtn = '<span class="hasTip" title="'.JText::_( 'COM_EASYTABLEPRO_MGR_EDIT_RECORDS_BTN_TT' ).'::'.$btn_text.'" style="margin-left:4px;" ><img src="'.$theImageURL.'" style="text-decoration: none; color: #333;" alt="'.$btn_text.'" /></span>';

		if( !$locked && !$extTable && $hasPermission)
		{
			$theEditBtn = '<a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$i.'\',\'editData\');" title="'.$btn_text.'" >'.$theEditBtn.'</a>';
		}

		return($theEditBtn);
	}

	function getDataUploadIcon ($locked, $i, $rowId, $tableName, $extTable, $hasPermission,$userName='')
	{
		if($extTable)
		{
			$btn_text = JText::sprintf ( 'COM_EASYTABLEPRO_LINK_LINKED_TABLE_NO_UPLOAD' , $tableName);
			$theImageURL = '/media/com_easytablepro/images/disabled_upload.png';
		}
		else
		{
			$lockText = ($hasPermission ? ($locked ? JText::sprintf( 'COM_EASYTABLEPRO_MGR_DISABLED_TABLE_LOCKED',$userName) : '') : JText::_( 'COM_EASYTABLEPRO_MGR_DISABLED_NO_UPLOAD_PERM' ));
			$btn_text = JText::_( 'COM_EASYTABLEPRO_MGR_UPLOAD_NEW_DESC' ).' \''.$tableName.'\' '.$lockText;
			$theImageURL = '/media/com_easytablepro/images/'.( ($locked || !$hasPermission) ? 'disabled_' : '' ).'upload.png';
		}

		$theBtn = '<span class="hasTip" title="'.JText::_( 'COM_EASYTABLEPRO_MGR_UPLOAD_DATA' ).'::'.$btn_text.'" style="margin-left:10px;" ><img src="'.$theImageURL.'" style="text-decoration: none; color: #333;" alt="'.$btn_text.'" /></span>';

		if( !$locked && !$extTable && $hasPermission)
		{
			$theBtn = '<a href="/administrator/index.php?option=com_easytablepro&amp;task=presentUploadScreen&amp;view=easytableupload&amp;cid='.$rowId.'&amp;tmpl=component" class="modal" title="'.$btn_text.'" rel="{handler: \'iframe\', size: {x: 700, y: 495}}">'.$theBtn.'</a>';
		}

		return($theBtn);
	}

	function getSearchableTick ($rowId, $flag, $locked=true, $hasPermission,$userName='')
	{
		$btn_text = '';
		$theImageString = '/media/com_'._cppl_this_com_name.'/images/'.( ($locked || !$hasPermission) ? 'disabled_' : '' );
		$lockText = ($hasPermission ? ($locked ? JText::sprintf( 'COM_EASYTABLEPRO_MGR_DISABLED_TABLE_LOCKED',$userName) : '') : JText::_( 'COM_EASYTABLEPRO_MGR_DISABLED_NO_PERM' ));

		if( $flag == '' )
		{
			$theImageString .= 'GlobalIcon16x16.png';
			$btn_text = JText::_( "COM_EASYTABLEPRO_MGR_ALLOW_JOOMLA_SEARCH" ).$lockText;
		}
		else if($flag)
		{
			$theImageString .= 'tick.png';
			$btn_text = JText::_( "COM_EASYTABLEPRO_MGR_PREVENT_JOOMLA_SEARCH" ).$lockText;
		}
		else
		{
			$theImageString .= 'publish_x.png';
			$btn_text = JText::_( "COM_EASYTABLEPRO_MGR_GLOBAL_SETTING_FOR_JOOMLA_SEARCH" ).$lockText;
		}

		$theSearchableImage = '<img src="'.$theImageString.'" name="'.$rowId.'_img" border="0" alt="'.$btn_text.'" />';

		$theSearchableButton = (!$locked && $hasPermission) ? '<a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$rowId.'\',\'toggleSearch\');" title="'.$btn_text.'" >'.$theSearchableImage.'</a>' : $theSearchableImage ;
		$theSearchableButton = '<span class="hasTip" title="'.$btn_text.'" style="margin-left:20px;" >'.$theSearchableButton.'</span>';
		
		return($theSearchableButton);
	}

	/**
	 * EasyTables view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
		//get the document and load the js support file
		$doc = JFactory::getDocument();
		// Get the current user
		$user = JFactory::getUser();

		// Get the settings meta record
		$canDo = ET_MgrHelpers::getActions();

		// Setup toolbar, js, css
		$this->addToolbar($canDo);
		$this->addCSSEtc();

		// Search
		$db = JFactory::getDBO();
		$search = $db->getEscaped($this->get('search'));

		// Get data from the model
		$rows = $this->get('Items');
		// A little pagination for our users with *lots* of tables.
		$pagination = $this->get('Pagination');

		$this->assignRef('rows',$rows);
		$this->assignRef('pagination',$pagination);
		$this->assign('search',$search);
		$this->assign('canDo',$canDo);
		$this->assign('et_current_version',ET_VHelpers::current_version());
		parent::display($tpl);
	}

	private function addToolbar($canDo)
	{
		/*
			Setup the Toolbar
		*/
		JToolBarHelper::title(JText::_( 'COM_EASYTABLEPRO' ), 'easytablepro');
		if($canDo->get('core.create'))
		{
			JToolBarHelper::addNew('tables.add', JText::_('COM_EASYTABLEPRO_TABLE_VIEW_TITLE_NEW'));
		}
		if($canDo->get('core.edit'))
		{
			JToolBarHelper::editList();
		}
		JToolBarHelper::divider();
		
		if($canDo->get('core.edit.state'))
		{
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
		}
		if($canDo->get('easytable.link'))

		{

			$toolbar=& JToolBar::getInstance( 'toolbar' );

			$toolbar->appendButton( 'Popup', 'tables.linkTable', 'Link Table', 'index.php?option=com_easytablepro&amp;view=easytablelink&amp;tmpl=component', 500, 280 );

		}
		JToolBarHelper::divider();


		if($canDo->get('core.delete'))
		{
			JToolBarHelper::deleteList( 'COM_EASYTABLEPRO_MGR_DELETE_TABLE_BTN' );
		}
		JToolBarHelper::divider();

		JToolBarHelper::preferences( 'com_'._cppl_this_com_name, 425 );
		JToolBarHelper::divider();

		JToolBarHelper::help('COM_EASYTABLEPRO_HELP_TABLES_VIEW',false,'http://seepeoplesoftware.com/products/easytablepro/1.1/help/tables.html');
	}

	private function addCSSEtc ()
	{
		// Get the document object
		$document = JFactory::getDocument();

		// First add CSS to the document
		$document->addStyleSheet('/media/com_easytablepro/css/easytable.css');

		// Then add JS to the document‚ - make sure all JS comes after CSS
		JHTML::_('behavior.modal');
		$document->addScript('http://www.seepeoplesoftware.com/cpplversions/cppl_et_versions.js');
		// Load this views js
		$jsFile = '/media/com_easytablepro/js/easytabletables.js';
		$document->addScript($jsFile);
		ET_MgrHelpers::loadJSLanguageKeys($jsFile);
	}
}
