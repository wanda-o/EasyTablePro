<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

defined('_JEXEC') or die;

jimport('joomla.application.categories');

/**
 * Build the route for the EasyStaging Pro component
 *
 * @param   array  &$query  An array of URL arguments
 *
 * @return	array  The URL arguments to use to assemble the subsequent URL.
 */
	function easyTableProBuildRoute(&$query)
	{
		$segments = array();

		if (isset($query['view']))
		{
			// Store the view for later
			$targetView = $query['view'];
			$targetID   = isset($query['id'])?$query['id']:0;

			// We need a few things, so, get a menu item based on Itemid or currently active
			$app		= JFactory::getApplication();
			$db         = JFactory::getDbo();
			$menu		= $app->getMenu();

			// We need a menu item.  Either the one specified in the query, or the current active one if none specified
			if (empty($query['Itemid']))
			{
				$menuItem = $menu->getActive();
			}
			else
			{
				$menuItem = $menu->getItem($query['Itemid']);
			}
			// Get the current menuItem's settings
			if (isset($menuItem->query['view']))
			{
				$menusCurrentView = $menuItem->query['view'];
			}
			else
			{
				$menusCurrentView = '';
			}
			// Get the menuItem's current component
			if (isset($menuItem->query['option']))
			{
				$menusCurrentOption = $menuItem->query['option'];
			}
			else
			{
				$menusCurrentOption = '';
			}
			// If we have an option it's likely we have a data ID
			if ($menusCurrentOption && isset($menuItem->query['id']))
			{
				$menusCurrentID = $menuItem->query['id'];
			}
			else
			{
				$menusCurrentID = 0;
			}

			unset($query['view']);

			switch ($targetView)
			{
				case 'records':
					// We need the id of the target table, otherwise it's all moot.
					if (isset($query['id']))
					{
						// If we're in a `records` view is the target within the same table?
						if ($menusCurrentView == 'records' && $targetID == $menusCurrentID)
						{
							unset($query['id']);
						}
						else
						{
							// OK we may need to convert a numeric id to an alias id
							if (is_numeric($targetID))
							{
								$SQLquery = $db->getQuery(true);

								// Search for the alias of the table of this id
								$SQLquery->select($db->quoteName('easytablealias'));
								$SQLquery->from($db->quoteName('#__easytables'));
								$SQLquery->where($db->quoteName('id') . ' = ' . $targetID);
								$db->setQuery($SQLquery);
								$idAlias = $db->loadResult();

								// Set the next segment to the alias
							}
							else
							{
								$idAlias = $targetID;
							}
							// Make sure we have a result.
							if ($idAlias != '')
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
						// So, is our current view already showing the table of the target URL?
						if ((($menusCurrentOption == 'com_easytablepro') && ($targetID != $menusCurrentID)) || $menusCurrentOption == '')
						{
							// We'll need to add an alias to point to the right table...
							$SQLquery = $db->getQuery(true);

							// Search for the alias of the table of this id
							$SQLquery->select($db->quoteName('easytablealias'));
							$SQLquery->from($db->quoteName('#__easytables'));
							$SQLquery->where($db->quoteName('id') . ' = ' . (int) $targetID);
							$db->setQuery($SQLquery);
							$idAlias = $db->loadResult();

							// Set the next segment to the alias
							$segments[] = $idAlias;
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
				default:;
				break;
			}
		}
		// Return segments here, of course it could be empty if no view etc... e.g. a core menu.
		return $segments;
	}



/**
 * Parse the segments of a URL.
 *
 * @param   array  $segments  The segments of the URL to parse.
 *
 * @return  array	The URL attributes to be used by the application.
 *
 * @since	1.0
 */
	function easyTableProParseRoute ($segments)
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

		// OK, we have work to do, lets get the active menu item.
		$app	= JFactory::getApplication();
		$menu	= $app->getMenu();
		$menuItem	= $menu->getActive();

		// It's possible there's no active menu item... e.g. a search result link
		if (!$menuItem)
		{
			$menuItem = $menu->getDefault();
		}
		// And we'll need the component params
		$params = JComponentHelper::getParams('com_easytablepro');
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		if ($count == 1 && ($menuItem->query['view'] != 'records') )
		{
			$segments[0] = preg_replace('/:/', '-', $segments[0], 1);
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
				if ($menuItem->query['view'] == 'tables')
				{
					// Remove the stupid colon
					$segments[0] = preg_replace('/:/', '-', $segments[0], 1);
					$alias = $segments[0];
					$query->select($db->quoteName('id'));
					$query->from($db->quoteName('#__easytables'));
					$query->where($db->quoteName('easytablealias') . '=' . $db->quote($alias));
					$db->setQuery($query);
					$id = $db->loadResult();
					$vars['id'] = $id;

					$rid = $segments[1];
				}
				elseif ($menuItem->query['view'] == 'records')
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
						$app->enqueueMessage(JText::_('COM_EASYTABLEPRO_SITE_ROUTER_PARSEROUTE_COULDNT_FIND_TABLE_ID'), 'Warning');
					}
					$rid = $segments[0];
				}
				elseif ($menuItem->query['view'] == 'record')
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
			list($seg_id, $seg_alias) = explode(':', $segments[0], 2);

			if (is_numeric($seg_id))
			{
				$segments[0] = $seg_alias;
			}
			else
			{
				$segments[0] = preg_replace('/:/', '-', $segments[0], 1);
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
				$app->enqueueMessage(JText::_('COM_EASYTABLEPRO_SITE_ROUTER_PARSEROUTE_COULDNT_FIND_TABLE_ID'), 'Warning');
			}
			// Remove the stupid colon that J! core inserts...
			$segments[2] = preg_replace('/:/', '-', $segments[2], 1);
			$rid = $segments[1];
			$leaf = $segments[2];

			$vars['rid']  = $rid;
			$vars['leaf'] = $leaf;
		}

		return $vars;
	}
