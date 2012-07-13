<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

defined('_JEXEC') or die;

jimport('joomla.application.categories');

/**
 * Build the route for the EasyStaging Pro component
 *
 * @param	array	An array of URL arguments
 * @return	array	The URL arguments to use to assemble the subsequent URL.
 */
	function EasyTableProBuildRoute(&$query)
	{
		$segments = array();
		// 
		if(isset($query['view'])){
			// get a menu item based on Itemid or currently active
			$app		= JFactory::getApplication();
			$db         = JFactory::getDbo();
			$menu		= $app->getMenu();
			$params		= JComponentHelper::getParams('com_easytablepro');
			
			// we need a menu item.  Either the one specified in the query, or the current active one if none specified
			if (empty($query['Itemid'])) {
				$menuItem = $menu->getActive();
				$menuItemGiven = false;
			} else {
				$menuItem = $menu->getItem($query['Itemid']);
				$menuItemGiven = true;
			}
			// store the view for later
			$view = $query['view'];
			unset($query['view']);

			if($view == 'tables') {
				return $segments;
			}

			if($view == 'records') {
				if (isset($query['id'])) {
					$id = $query['id'];
					if($menuItemGiven && $menuItem->query['view'] == 'records' && $id == $menuItem->query['id']) {
						unset($query['id']);
						return $segments;
					} 
					// OK we may need to convert a numeric id to an alias id
					if(is_numeric($id))
					{
						$SQLquery = $db->getQuery(true);
						// Search for the alias of the table of this id
						$SQLquery->select($db->quoteName('easytablealias'));
						$SQLquery->from($db->quoteName('#__easytables'));
						$SQLquery->where($db->quoteName('id') . ' = ' . $id);
						$db->setQuery($SQLquery);
						$idAlias = $db->loadResult();
						// set the next segment to the alias
					} else {
						$idAlias = $id;
					}
					unset($query['id']);
					$segments[] = $idAlias;
					
					return $segments;
				}
			}

			if($view == 'record') {
				if(isset($query['id']))
				{
					$id = $query['id'];
					// We have a menu item is it pointing ?to a record/records view
					if($menuItemGiven && isset($menuItem->query['view']))
					{
						if( ($menuItem->query['view'] == 'tables')  )
						{
							// OK we may need to convert a numeric id to an alias id
							if(is_numeric($id))
							{
								$SQLquery = $db->getQuery(true);
								// Search for the alias of the table of this id
								$SQLquery->select($db->quoteName('easytablealias'));
								$SQLquery->from($db->quoteName('#__easytables'));
								$SQLquery->where($db->quoteName('id') . ' = ' . $id);
								$db->setQuery($SQLquery);
								$idAlias = $db->loadResult();
								// set the next segment to the alias
							} else {
								$idAlias = $id;
							}

							$segments[] = $idAlias;
						}
						elseif ($menuItem->query['view'] == 'records')
						{
							// do nothing as we're already on a table?
						}
						elseif ($menuItem->query['view'] == 'record')
						{
							$segments[] = $id;
						}
					}
					else
					{
						$segments[] = $id;
					}
					unset($query['id']);
				} else {
					return array();
				}
				if(isset($query['rid']))
				{
					$segments[] = $query['rid'];
					unset($query['rid']);
				} else {
					return array();
				}
				if(isset($query['rllabel']))
				{
					$segments[] = $query['rllabel'];
					unset($query['rllabel']);
				}
			}
		}
		return $segments;
	}



/**
 * Parse the segments of a URL.
 *
 * @param	array	The segments of the URL to parse.
 *
 * @return	array	The URL attributes to be used by the application.
 * @since	1.5
 */
	function EasyTableProParseRoute ($segments)
	{
		$vars = array();
	
		// Count route segments
		$count = count($segments);
		
		// Time to bail
		if($count == 0) {
			$vars['view'] = 'tables';
			return $vars;
		}
		
		//OK, we have work to do, lets get the active menu item.
		$app	= JFactory::getApplication();
		$menu	= $app->getMenu();
		$item	= $menu->getActive();
		// And we'll need the menu params
		$params = JComponentHelper::getParams('com_easytablepro');
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		if($count == 1 && ($item->query['view'] != 'records') ) {
			$segments[0]=preg_replace('/:/','-',$segments[0],1);
			$vars['view'] = 'records';
			// Convert the easy table alias to its actual id
			$alias = $segments[0];
			$query->select($db->quoteName('id'));
			$query->from($db->quoteName('#__easytables'));
			$query->where($db->quoteName('easytablealias') . '=' . $db->quote($alias));
			$db->setQuery($query);
			$id = $db->loadResult();
			$vars['id'] = $id;
			return $vars;
		} elseif($count == 1 && ($item->query['view'] == 'records') ) {
			$count = 2;
		}
		
		$vars['view'] = 'record';
		if($count == 2) {
			if(isset($item->query['view'])){
				if( $item->query['view']=='tables'){
					// Remove the stupid colon
					$segments[0]=preg_replace('/:/','-',$segments[0],1);
					$alias = $segments[0];
					$query->select($db->quoteName('id'));
					$query->from($db->quoteName('#__easytables'));
					$query->where($db->quoteName('easytablealias') . '=' . $db->quote($alias));
					$db->setQuery($query);
					$id = $db->loadResult();
					$vars['id'] = $id;

					$rid = $segments[1];
			} elseif ($item->query['view']=='records') {
					// Convert the easy table alias to it actual id
					if(isset($item->query['id'])) {
						$id = $item->query['id'];
						$vars['id'] = $id;
					} else {
						$vars['id'] = 0;
						$app->enqueueMessage(JText::_('COM_EASYTABLEPRO_SITE_ROUTER_PARSEROUTE_COULDNT_FIND_TABLE_ID'),'Warning');
					}
					$rid = $segments[0];
				}
			}
			$vars['rid']  = $rid;
			// Remove the stupid colon that J! core inserts...
			if(isset($segments[1])) {
				$segments[1]=preg_replace('/:/','-',$segments[1],1);
				$leaf = $segments[1];
				$vars['leaf'] = $leaf;
			}
		}
		
		if($count == 3) {
			$segments[0]=preg_replace('/:/','-',$segments[0],1);

			// Convert the easy table alias to it actual id
			if(isset($item->query['id'])) {
				$id = $item->query['id'];
				$vars['id'] = $id;
			} elseif (!empty($segments[0])) {
				$alias = $segments[0];
				$query->select($db->quoteName('id'));
				$query->from($db->quoteName('#__easytables'));
				$query->where($db->quoteName('easytablealias') . '=' . $db->quote($alias));
				$db->setQuery($query);
				$id = $db->loadResult();
				$vars['id'] = $id;
			} else {
				$vars['id'] = 0;
				$app->enqueueMessage(JText::_('COM_EASYTABLEPRO_SITE_ROUTER_PARSEROUTE_COULDNT_FIND_TABLE_ID'),'Warning');
			}
			// Remove the stupid colon that J! core inserts...
			$segments[2]=preg_replace('/:/','-',$segments[2],1);
			$rid = $segments[1];
			$leaf = $segments[2];

			$vars['rid']  = $rid;
			$vars['leaf'] = $leaf;
		}
		
		return $vars;
	}
