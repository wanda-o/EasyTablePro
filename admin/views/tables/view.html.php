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
require_once ''.JPATH_COMPONENT_ADMINISTRATOR.'/helpers/general.php';


/**
 * HTML View class for the EasyTables Component
 *
 * @package    EasyTables
 * @subpackage Views
 */

class EasyTableProViewTables extends JView
{
	protected $state;
	protected $items;
	protected $pagination;

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
			$theBtn = "<span class=\"hasTip\" title=\"$btn_text\" style=\"margin-left:15px;\" >".JHTML::_( 'grid.published',  $row->published, $i, 'tick.png', 'publish_x.png', 'tables.').'</span>';
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
			$theEditBtn = '<a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$i.'\',\'records.listAll\');" title="'.$btn_text.'" >'.$theEditBtn.'</a>';
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
			$theImageURL = '/media/com_easytablepro/images/'.( ($locked || !$hasPermission) ? 'disabled_' : '' ).'upload_16x16.png';
		}

		$theBtn = '<span class="hasTip" title="'.JText::_( 'COM_EASYTABLEPRO_MGR_UPLOAD_DATA' ).'::'.$btn_text.'" style="margin-left:10px;" ><img src="'.$theImageURL.'" style="text-decoration: none; color: #333;" alt="'.$btn_text.'" /></span>';

		if( !$locked && !$extTable && $hasPermission)
		{
			if(JDEBUG){
				$theBtn = '<a href="/administrator/index.php?option=com_easytablepro&amp;task=upload&amp;view=upload&amp;cid='.$rowId.'&amp;tmpl=component" class="modal" title="'.$btn_text.'" rel="{handler: \'iframe\', size: {x: 700, y: 495}}">'.$theBtn.'</a>';
			} else {
				$theBtn = '<a href="/administrator/index.php?option=com_easytablepro&amp;task=upload&amp;view=upload&amp;cid='.$rowId.'&amp;tmpl=component" class="modal" title="'.$btn_text.'" rel="{handler: \'iframe\', size: {x: 700, y: 425}}">'.$theBtn.'</a>';
			}
		}

		return($theBtn);
	}

	/**
	 * EasyTables view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
		// Get the settings meta record
		$canDo = ET_Helper::getActions();

		// Setup toolbar, js, css
		$this->addToolbar($canDo);
		$this->addCSSEtc();

		// Get data from the model
		$rows = $this->get('Items');
		$state = $this->get('State');
		$pagination = $this->get('Pagination');

		
		// A little pagination for our users with *lots* of tables.
		$pagination = $this->get('Pagination');

		$this->state = $state;
		$this->assignRef('rows',$rows);
		$this->assignRef('pagination',$pagination);
		$this->assign('canDo',$canDo);
		$this->assign('et_current_version',ET_VHelper::current_version());
		parent::display($tpl);
	}

	private function addToolbar($canDo)
	{
		/*
		 *	Setup the Toolbar
		 */
		JToolBarHelper::title(JText::_( 'COM_EASYTABLEPRO' ), 'easytablepro');

		// Add New Table
		if($canDo->get('core.create'))
		{
			$addTableURL = 'index.php?option=com_easytablepro&amp;view=upload&amp;step=new&amp;task=upload.new&amp;tmpl=component';
			$toolbar = JToolBar::getInstance( 'toolbar' );
			if(JDEBUG) {
				$toolbar->appendButton( 'Popup', 'new', 'COM_EASYTABLEPRO_TABLE_VIEW_TITLE_NEW', $addTableURL, 700, 495 );
			} else {
				$toolbar->appendButton( 'Popup', 'new', 'COM_EASYTABLEPRO_TABLE_VIEW_TITLE_NEW', $addTableURL, 700, 425 );
			}
		}

		if($canDo->get('easytablepro.link'))
		{
			$linkURL = 'index.php?option=com_easytablepro&amp;view=link&amp;task=link&amp;tmpl=component';
			$toolbar = JToolBar::getInstance( 'toolbar' );
			$toolbar->appendButton( 'Popup', 'easytablpro-linkTable', 'COM_EASYTABLEPRO_LABEL_LINK_TABLE', $linkURL, 500, 280 );
		}
		if($canDo->get('core.edit'))
		{
			JToolBarHelper::editList('table.edit');
		}
		JToolBarHelper::divider();
		
		if($canDo->get('core.edit.state'))
		{
			JToolBarHelper::publishList('tables.publish');
			JToolBarHelper::unpublishList('tables.unpublish');
		}
		JToolBarHelper::divider();


		if($canDo->get('core.delete'))
		{
			JToolBarHelper::deleteList( 'COM_EASYTABLEPRO_MGR_DELETE_TABLE_BTN', 'tables.delete' );
		}
		JToolBarHelper::divider();

		JToolBarHelper::preferences( 'com_easytablepro', 425 );
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
		// Tools first

		$jsFile = ('/media/com_easytablepro/js/atools.js');

		$document->addScript($jsFile);

		ET_Helper::loadJSLanguageKeys($jsFile);


		// Get the remote version data
		$document->addScript('http://www.seepeoplesoftware.com/cpplversions/cppl_et_versions.js');
		// Load this views js
		$jsFile = '/media/com_easytablepro/js/easytabletables.js';
		$document->addScript($jsFile);
		ET_Helper::loadJSLanguageKeys($jsFile);
	}
}
