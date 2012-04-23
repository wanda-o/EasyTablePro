<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die ('Restricted Access');

jimport('joomla.application.component.controller');

/**
 * EasyTables Controller
 *
 * @package    EasyTables
 * @subpackage Controllers
 */
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
jimport('joomla.application.component.controller');

class EasyTableProControllerRecord extends JController
{
	protected $default_view = 'record';
	
	public function __construct($config = array())

	{
		parent::__construct($config);

		$jInput = JFactory::getApplication()->input;
		$jInput->set('view', $this->default_view);

	}

	public function cancel()
	{
		parent::cancel();
	}
}

// class
