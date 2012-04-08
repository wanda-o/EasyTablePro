<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');


/**
 * EasyTable Table class
 *
 * 
 */
class EasyTableProTableTable extends JTable
{
	/**
		Check function
	 */
	 function check()
	{
		/* Make sure we have an alias for the table - nicer for linking, css etc */
	    jimport( 'joomla.filter.output' );
	    if(empty($this->easytablealias)) {
	            $this->easytablealias = $this->easytablename;
	    }
	    $this->easytablealias = JFilterOutput::stringURLSafe($this->easytablealias);
	 
	    /* Any other checks ?
           Not yet Bob, but ya never know! */
	    return true;
	}

	/**
		Bind function - to support table specific params
	 */
	function bind($array, $ignore = '')
	{
	        if (key_exists( 'params', $array ) && is_array( $array['params'] ))
	        {
	                $registry = new JRegistry();
	                $registry->loadArray($array['params']);
	                $array['params'] = $registry->toString();
	        }
	        return parent::bind($array, $ignore);
	}

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function __construct(& $db) {
		parent::__construct('#__easytables', 'id', $db);
	}
}
?>
