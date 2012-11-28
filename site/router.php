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
		if (isset($query['view']))
		{
			// store the view for later
			$targetView = $query['view'];

			// If the target view is the tables list then we can just return the current segments
			if ($targetView == 'tables')
			{
				return $segments;
			}

			// Otherwise, we need a few things, so, get a menu item based on Itemid or currently active
			$app		= JFactory::getApplication();
			$db         = JFactory::getDbo();
			$menu		= $app->getMenu();
			$params		= JComponentHelper::getParams('com_easytablepro');

			// we need a menu item.  Either the one specified in the query, or the current active one if none specified
			if (empty($query['Itemid']))
			{
				$menuItem = $menu->getActive();
				$menuItemGiven = false;
			}
			else
			{
				$menuItem = $menu->getItem($query['Itemid']);
				$menuItemGiven = true;
			}
			// Get the current menuItem's settings
			if(isset($menuItem->query['view']))
			{
				$currentView = $menuItem->query['view'];
			}
			else
			{
				$currentView = '';
			}
			// Get the current component
			if(isset($menuItem->query['option']))
			{
				$currentOption = $menuItem->query['option'];
			}
			else
			{
				$currentOption = '';
			}
			// If we have an option it's likely we have a data ID
			if($currentOption && isset($menuItem->query['id']))
			{
				$currentID = $menuItem->query['id'];
			}
			else
			{
				$currentID = 0;
			}

			unset($query['view']);

			switch ($targetView) {
				case 'records':
					// We need the id of the target table, otherwise it's all moot.
					if (isset($query['id']))
					{
						$id = $query['id'];
						// If we're in a `records` view is the target within the same table?
						if ($currentView == 'records' && $id == $currentID)
						{
							unset($query['id']);
						}
						else
						{
							// OK we may need to convert a numeric id to an alias id
							if (is_numeric($id))
							{
								$SQLquery = $db->getQuery(true);
								// Search for the alias of the table of this id
								$SQLquery->select($db->quoteName('easytablealias'));
								$SQLquery->from($db->quoteName('#__easytables'));
								$SQLquery->where($db->quoteName('id') . ' = ' . $id);
								$db->setQuery($SQLquery);
								$idAlias = $db->loadResult();
								// set the next segment to the alias
							}
							else
							{
								$idAlias = $id;
							}
							// Make sure we have a result.
							if($idAlias != '')
							{
								unset($query['id']);
								$segments[] = $idAlias;
							}
						}
					}
				break;

				// Link to Record Detail view requested
				case 'record':
					if (isset($query['id']))
					{
						$id = $query['id'];
						// We have a menu item, what is it pointing to?
						if ($currentView)
						{
							// So the link to a record is from a view (`records` or `record`) under a 'tables' list menu item.
							if ($menuItem->query['view'] == 'tables')
							{
								;
							}
							// The link is from a 'records' list view of a particular table.
							// So, most likely a detail link or a menu/article linking to a specific record.
							elseif ($currentView == 'records')
							{
								// So, is our current view already showing the table of the target URL?
								if((int)$id == $currentID)
								{
									// Hey we're already pointing to the right table... :D
								}
								else
								{
									// We'll need to add an alias to point to the right table...
									$SQLquery = $db->getQuery(true);
									// Search for the alias of the table of this id
									$SQLquery->select($db->quoteName('easytablealias'));
									$SQLquery->from($db->quoteName('#__easytables'));
									$SQLquery->where($db->quoteName('id') . ' = ' . (int)$id);
									$db->setQuery($SQLquery);
									$idAlias = $db->loadResult();
									// set the next segment to the alias
									$segments[] = $idAlias;
								}
								// If yes, we can proceed with building a route
								// If no, what do we do? Search for a menu that links to the table in question? If we find one, what about filters and access levels?
								// If we don't find one look for a menu that links to a tables list. If we find one what about filters and access levels?
							}
							elseif ($menuItem->query['view'] == 'record')
							{
								$segments[] = $id;
								// Check if our target is a record in a different table?
								if($id != $menuItem->query['id'])
								{
									// We are pointing to another table, so adjustments are required

								}
								else
								{

								}
							}
						}
						else
						{
							$segments[] = $id;
						}
						unset($query['id']);
					}
					else
					{
						return array();
					}
					if (isset($query['rid']))
					{
						$segments[] = $query['rid'];
						unset($query['rid']);
					}
					else
					{
						return array();
					}
					if (isset($query['rllabel']))
					{
						$segments[] = $query['rllabel'];
						unset($query['rllabel']);
					}
				break;
				default:
					;
				break;
			}
		}
		// Return segments here, of course it could be empty if no view etc... provided.
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
		if ($count == 0)
		{
			$vars['view'] = 'tables';
			return $vars;
		}

		//OK, we have work to do, lets get the active menu item.
		$app	= JFactory::getApplication();
		$menu	= $app->getMenu();
		$menuItem	= $menu->getActive();
		// It's possible there's no active menu item... e.g. a search result link
		if(!$menuItem)
		{
			$menuItem = $menu->getDefault();
		}
		// And we'll need the component params
		$params = JComponentHelper::getParams('com_easytablepro');
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		if ($count == 1 && ($menuItem->query['view'] != 'records') )
		{
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
		}
		elseif ($count == 1 && ($menuItem->query['view'] == 'records') )
		{
			$count = 2;
		}

		$vars['view'] = 'record';
		if ($count == 2)
		{
			if (isset($menuItem->query['view']))
			{
				if ($menuItem->query['view']=='tables')
				{
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
				}
				elseif ($menuItem->query['view']=='records')
				{
					// Convert the easy table alias to it actual id
					if (isset($menuItem->query['id']))
					{
						$id = $menuItem->query['id'];
						$vars['id'] = $id;
					}
					else
					{
						$vars['id'] = 0;
						$app->enqueueMessage(JText::_('COM_EASYTABLEPRO_SITE_ROUTER_PARSEROUTE_COULDNT_FIND_TABLE_ID'),'Warning');
					}
					$rid = $segments[0];
				}
				elseif ($menuItem->query['view']=='record')
				{
					$id = $segments[0];
					$vars['id'] = $id;
					$rid = $segments[1];
				}
			}
			$vars['rid']  = $rid;
		}

		// Three => id, rid, leaf
		if ($count == 3)
		{
			list($seg_id, $seg_alias) = explode(':', $segments[0],2);
			if(is_numeric($seg_id))
			{
				$segments[0] = $seg_alias;
			}
			else
			{
				$segments[0]=preg_replace('/:/','-',$segments[0],1);
			}

			// Convert the easy table alias to it actual id
			if (!empty($segments[0]))
			{
				$alias = $segments[0];
				$query->select($db->quoteName('id'));
				$query->from($db->quoteName('#__easytables'));
				$query->where($db->quoteName('easytablealias') . '=' . $db->quote($alias));
				$db->setQuery($query);
				$id = $db->loadResult();
				$vars['id'] = $id;
			}
			else
			{
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
