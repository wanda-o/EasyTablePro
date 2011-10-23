<?php
/**
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

jimport( 'joomla.application.component.view');

$pvf = ''.JPATH_COMPONENT_ADMINISTRATOR.'/views/viewfunctions.php';
require_once $pvf;

/**
 * HTML View class for the EasyTables Component
 *
 * @package    EasyTables
 * @subpackage Views
 */

class EasyTableViewEasyTables extends JView
{
	function getEditorLink ($locked, $rowId, $tableName, $hasPermission,$userName='')
	{
		$lockText = ($hasPermission ? ($locked ? JText::sprintf( 'DISABLED_BECAUSE_THE_TABLE_IS_LOCKED_',$userName) : '') : JText::_( 'DISABLED_BECAUSE_YOU_DONT_HAVE_PERM' ));
		$link_text = JText::_( 'EDIT_PROPERTIES_AND_STRUCTURE_OF' ).' \''.$tableName.'\' '.$lockText ;
		$theEditLink = '<span class="hasTip" title="'.$link_text.'" style="margin-left:10px;" >'.$tableName.'</span>';

		if( !$locked && $hasPermission)
		{
			$theEditLink = '<span class="hasTip" title="'.$link_text.'" style="margin-left:10px;" >'.'<a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$rowId.'\',\'edit\');" title="'.$link_text.'" >'.$tableName.'</a></span>';
		}

		return($theEditLink);
	}

	function publishedIcon ($locked, $row, $i, $hasPermission,$userName='')
	{
		$lockText = ($hasPermission ? ($locked ? JText::sprintf( 'DISABLED_BECAUSE_THE_TABLE_IS_LOCKED_',$userName) : '') : JText::_( 'DISABLED_BECAUSE_YOU_DONT_HAVE_PERM' ));
		$btn_text = JText::_( ( $row->published ? 'PUBLISHED_BTN':'UNPUBLISHED_BTN') ).' \''.$row->easytablename.'\' '.$lockText;
		$theImageURL = 'components/com_'._cppl_this_com_name.'/assets/images/'.( ($locked || !$hasPermission) ? 'disabled_' : '' ).($row->published?'publish_g.png':'publish_x.png');
		$theBtn = '<span  class="hasTip" title="'.$btn_text.'" style="margin-left:15px;" ><img src="'.$theImageURL.'" border="0" alt="'.$btn_text.'"></span>';

		if( !$locked && $hasPermission )
		{
			$theBtn = "<span class=\"hasTip\" title=\"$btn_text\" style=\"margin-left:15px;\" >".JHTML::_( 'grid.published',  $row, $i, '../'.$theImageURL ).'</span>';
		}

		return $theBtn;
	}


	function getDataEditorIcon ($locked, $i, $rowId, $tableName, $extTable, $hasPermission,$userName='')
	{
		if($extTable)
		{
			$btn_text = JText::sprintf ( 'LINKED_TABLE_NO_DATA_EDITING' , $tableName);
			$theImageURL = 'components/com_'._cppl_this_com_name.'/assets/images/disabled_edit.png';
		}
		else
		{
			$lockText = ($hasPermission ? ($locked ? JText::sprintf( 'DISABLED_BECAUSE_THE_TABLE_IS_LOCKED_',$userName) : '') : JText::_( 'DISABLED_BECAUSE_YOU_DONT_HAVE_DATA_EDIT_PERM' ));
			$btn_text = JText::_( 'EDIT_TABLE_DATA_IN_' ).' \''.$tableName.'\' '.$lockText;
			$theImageURL = 'components/com_'._cppl_this_com_name.'/assets/images/'.( ($locked || !$hasPermission) ? 'disabled_' : '' ).'edit.png';
		}

		$theEditBtn = '<span class="hasTip" title="'.JText::_( 'EDIT_RECORDS' ).'::'.$btn_text.'" style="margin-left:4px;" ><img src="'.$theImageURL.'" style="text-decoration: none; color: #333;" alt="'.$btn_text.'" /></span>';

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
			$btn_text = JText::sprintf ( 'LINKED_TABLE_NO_UPLOAD' , $tableName);
			$theImageURL = 'components/com_'._cppl_this_com_name.'/assets/images/disabled_upload.png';
		}
		else
		{
			$lockText = ($hasPermission ? ($locked ? JText::sprintf( 'DISABLED_BECAUSE_THE_TABLE_IS_LOCKED_',$userName) : '') : JText::_( 'DISABLED_BECAUSE_YOU_DONT_HAVE_UPLOAD_PERM' ));
			$btn_text = JText::_( 'UPLOAD_NEW_DESC' ).' \''.$tableName.'\' '.$lockText;
			$theImageURL = 'components/com_'._cppl_this_com_name.'/assets/images/'.( ($locked || !$hasPermission) ? 'disabled_' : '' ).'upload.png';
		}

		$theBtn = '<span class="hasTip" title="'.JText::_( 'UPLOAD_DATA' ).'::'.$btn_text.'" style="margin-left:10px;" ><img src="'.$theImageURL.'" style="text-decoration: none; color: #333;" alt="'.$btn_text.'" /></span>';

		if( !$locked && !$extTable && $hasPermission)
		{
			$theBtn = '<a href="/administrator/index.php?option=com_easytablepro&amp;task=presentUploadScreen&amp;view=easytableupload&amp;cid='.$rowId.'&amp;tmpl=component" class="modal" title="'.$btn_text.'" rel="{handler: \'iframe\', size: {x: 700, y: 495}}">'.$theBtn.'</a>';
		}

		return($theBtn);
	}

	function getSearchableTick ($rowId, $flag, $locked=true, $hasPermission,$userName='')
	{
		$btn_text = '';
		$theImageString = 'components/com_'._cppl_this_com_name.'/assets/images/'.( ($locked || !$hasPermission) ? 'disabled_' : '' );
		$lockText = ($hasPermission ? ($locked ? JText::sprintf( 'DISABLED_BECAUSE_THE_TABLE_IS_LOCKED_',$userName) : '') : JText::_( 'DISABLED_BECAUSE_YOU_DONT_HAVE_PERM' ));

		if( $flag == '' )
		{
			$theImageString .= 'GlobalIcon16x16.png';
			$btn_text = JText::_( "CLICK_HERE_TO_ALLOW_ACCESS_BY_JOOMLA_S_BUILT_IN_SEARCH_FUNCTION_" ).$lockText;
		}
		else if($flag)
		{
			$theImageString .= 'tick.png';
			$btn_text = JText::_( "CLICK_HERE_TO_PREVENT_ACCESS_TO_THIS_TABLE_BY_JOOMLA_S_BUILT_IN_SEARCH_FUNCTION_" ).$lockText;
		}
		else
		{
			$theImageString .= 'publish_x.png';
			$btn_text = JText::_( "CLICK_HERE_TO_USE_THE_GLOBAL_PREFERENCES_TO_CONTROL_JOOMLA_S_BUILT_IN_SEARCH_FUNCTION_TO_ACCESS_THIS_TABLE_" ).$lockText;
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
		$doc =& JFactory::getDocument();
		$doc->addStyleSheet(JURI::base().'components/com_'._cppl_this_com_name.'/'._cppl_base_com_name.'.css');
		$doc->addScript(JURI::base().'components/com_'._cppl_this_com_name.'/assets/js/easytablemanager.js');
		$doc->addScript('http://seepeoplesoftware.com/cpplversions/cppl_et_versions.js');
		JHTML::_('behavior.modal');
		// Get the current user
		$user =& JFactory::getUser();

		// Get the settings meta record
		$settings = ET_MgrHelpers::getSettings();
		// Allow Access settings
		$aaSettings = explode(',', $settings->get('allowAccess'));
		// Allow Table Linking
		$alaSettings = explode(',', $settings->get('allowLinkingAccess'));
		// Allow Table Management
		$atmSettings = explode(',', $settings->get('allowTableManagement'));
		// Allow Data Upload
		$aduSettings = explode(',', $settings->get('allowDataUpload'));
		// Allow Data Editing
		$adeSettings = explode(',', $settings->get('allowDataEditing'));
		/*
			Setup the Toolbar
		*/
		JToolBarHelper::title(JText::_( 'EASYTABLEPRO' ), 'easytables');
		if(in_array($user->usertype, $atmSettings))
		{
			$hasTableMgrPermission = TRUE;
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::editList();
			JToolBarHelper::deleteList( 'ARE_YOU_SURE_YOU_TO_DELETE_THE_TABLE_S__' );
			JToolBarHelper::addNew();
		}
		else $hasTableMgrPermission = FALSE;

		if(in_array($user->usertype, $alaSettings))
		{
			$toolbar=& JToolBar::getInstance( 'toolbar' );
			$toolbar->appendButton( 'Popup', 'linkTable', 'Link Table', 'index.php?option=com_easytablepro&amp;view=easytablelink&amp;tmpl=component', 500, 280 );
		}
		JToolBarHelper::preferences( 'com_'._cppl_this_com_name, 425 );
		if(in_array($user->usertype, $aaSettings))
		{
			JToolBarHelper::custom( 'settings','Gear_Icon_48x48.png','',JText::_('SETTINGS'), FALSE );
		}

		// Search
		$search = mysql_real_escape_string($this->get('search'));

		// Get data from the model
		$rows =& $this->get('data');
		// A little pagination for our users with *lots* of tables.
		$pagination = & $this->get('Pagination');

		$this->assignRef('rows',$rows);
		$this->assignRef('pagination',$pagination);
		$this->assign('search',$search);
		$this->assign('et_hasTableMgrPermission',$hasTableMgrPermission);
		$this->assign('et_hasDataUploadPermission',in_array($user->usertype, $aduSettings));
		$this->assign('et_hasDataEditingPermission',in_array($user->usertype, $adeSettings));
		$this->assign('et_current_version',ET_VHelpers::current_version());
		parent::display($tpl);
	}
}
