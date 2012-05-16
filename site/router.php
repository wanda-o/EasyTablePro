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
					// OK we may need to convert a numeric id to an alias id
					$id = $query['id'];
					if(is_numeric($id))
					{
						// Get db
						$db = JFactory::getDbo();
						$SQLquery = $db->getQuery(true);
						// Search for the alias of the table of this id
						$SQLquery->select($db->quoteName('easytablealias'));
						$SQLquery->from($db->quoteName('#__easytables'));
						$SQLquery->where($db->quoteName('id') . ' = ' . $id);
						$db->setQuery($SQLquery);
						$idAlias = $db->loadResult();
						// set the next segment to the alias
						$segments[] = $idAlias; 
					} else {
						$segments[] = $id;
					}
					unset($query['id']);
					return $segments;
				}
			}

			if($view == 'record') {
				if(isset($query['id']))
				{
					if(! $menuItem->query['view'] == 'records') {
						$segments[] = $query['id'];
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
		if(isset($segments[0])) $segments[0]=preg_replace('/:/','-',$segments[0],1);
		$vars = array();
		//Get the active menu item.
		$app	= JFactory::getApplication();
		$menu	= $app->getMenu();
		$item	= $menu->getActive();
		$params = JComponentHelper::getParams('com_easytablepro');
		$db = JFactory::getDBO();
	
		// Count route segments
		$count = count($segments);
		
		if($count == 0) $vars['view'] = 'tables';
		
		if($count == 1) {
			$vars['view'] = 'records';
			// Convert the easy table alias to its actual id
			$alias = $segments[0];
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->quoteName('id'));
			$query->from($db->quoteName('#__easytables'));
			$query->where($db->quoteName('easytablealias') . '=' . $db->quote($alias));
			$db->setQuery($query);
			$id = $db->loadResult();
			$vars['id'] = $id;
		}
		
		if($count == 2) {
			$vars['view'] = 'record';
			// Convert the easy table alias to it actual id
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->quoteName('id'));
			$query->from($db->quoteName('#__easytables'));
			$db->setQuery($query);
			$id = $db->loadResult();
			$vars['id'] = $id;
		}

		return $vars;

	
		// Standard routing for articles.  If we don't pick up an Itemid then we get the view from the segments
		// the first segment is the view and the last segment is the id of the article or category.
		if (!isset($item)) {
			$vars['view']	= $segments[0];
			$vars['id']		= $segments[$count - 1];
	
			return $vars;
		}
	
		// if there is only one segment, then it points to either an article or a category
		// we test it first to see if it is a category.  If the id and alias match a category
		// then we assume it is a category.  If they don't we assume it is an article
		if ($count == 1) {
			// we check to see if an alias is given.  If not, we assume it is an article
			if (strpos($segments[0], ':') === false) {
				$vars['view'] = 'article';
				$vars['id'] = (int)$segments[0];
				return $vars;
			}
	
			list($id, $alias) = explode(':', $segments[0], 2);
	
			// first we check if it is a category
			$category = JCategories::getInstance('Content')->get($id);
	
			if ($category && $category->alias == $alias) {
				$vars['view'] = 'category';
				$vars['id'] = $id;
	
				return $vars;
			} else {
				$query = 'SELECT alias, catid FROM #__content WHERE id = '.(int)$id;
				$db->setQuery($query);
				$article = $db->loadObject();
	
				if ($article) {
					if ($article->alias == $alias) {
						$vars['view'] = 'article';
						$vars['catid'] = (int)$article->catid;
						$vars['id'] = (int)$id;
	
						return $vars;
					}
				}
			}
		}
	
		// if there was more than one segment, then we can determine where the URL points to
		// because the first segment will have the target category id prepended to it.  If the
		// last segment has a number prepended, it is an article, otherwise, it is a category.
		if (!$advanced) {
			$cat_id = (int)$segments[0];
	
			$article_id = (int)$segments[$count - 1];
	
			if ($article_id > 0) {
				$vars['view'] = 'article';
				$vars['catid'] = $cat_id;
				$vars['id'] = $article_id;
			} else {
				$vars['view'] = 'category';
				$vars['id'] = $cat_id;
			}
	
			return $vars;
		}
	
		// we get the category id from the menu item and search from there
		$id = $item->query['id'];
		$category = JCategories::getInstance('Content')->get($id);
	
		if (!$category) {
			JError::raiseError(404, JText::_('COM_CONTENT_ERROR_PARENT_CATEGORY_NOT_FOUND'));
			return $vars;
		}
	
		$categories = $category->getChildren();
		$vars['catid'] = $id;
		$vars['id'] = $id;
		$found = 0;
	
		foreach($segments as $segment)
		{
			$segment = str_replace(':', '-', $segment);
	
			foreach($categories as $category)
			{
				if ($category->alias == $segment) {
					$vars['id'] = $category->id;
					$vars['catid'] = $category->id;
					$vars['view'] = 'category';
					$categories = $category->getChildren();
					$found = 1;
					break;
				}
			}
	
			if ($found == 0) {
				if ($advanced) {
					$db = JFactory::getDBO();
					$query = 'SELECT id FROM #__content WHERE catid = '.$vars['catid'].' AND alias = '.$db->Quote($segment);
					$db->setQuery($query);
					$cid = $db->loadResult();
				} else {
					$cid = $segment;
				}
	
				$vars['id'] = $cid;
	
				if ($item->query['view'] == 'archive' && $count != 1){
					$vars['year']	= $count >= 2 ? $segments[$count-2] : null;
					$vars['month'] = $segments[$count-1];
					$vars['view']	= 'archive';
				}
				else {
					$vars['view'] = 'article';
				}
			}
	
			$found = 0;
		}
	
		return $vars;
	}
