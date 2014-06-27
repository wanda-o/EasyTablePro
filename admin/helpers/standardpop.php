<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

defined('JPATH_PLATFORM') or die;

/**
 * Renders a modal window button with extra styling options
 *
 * @package     Joomla.Libraries
 * @subpackage  Toolbar
 * @since       3.0
 */
class JToolbarButtonStandardpop extends JToolbarButton
{
	/**
	 * Button type
	 *
	 * @var    string
	 */
	protected $_name = 'Standardpop';

	/**
	 * Fetch the HTML for the button
	 *
	 * @param   string   $type      Unused string.
	 * @param   string   $name      Modal name, used to generate element ID
	 * @param   string   $text      The link text
	 * @param   string   $url       URL for popup
	 * @param   integer  $width     Width of popup
	 * @param   integer  $height    Height of popup
	 * @param   string   $onClose   JavaScript for the onClose event.
	 * @param   string   $title     The title text
	 * @param   string   $btnClass  The CSS class for the button, defaults to standard if  an apply/new btn.
	 * @param   string   $class     Used to specify the icon class.
	 *
	 * @return  string  HTML string for the button
	 *
	 * @since   3.0
	 */
	public function fetchButton($type = 'Modal', $name = '', $text = '', $url = '', $width = 640, $height = 480,
		$onClose = '', $title = '', $btnClass = '', $class = '')
	{
		// If no $title is set, use the $text element
		if (strlen($title) == 0)
		{
			$title = $text;
		}

		// Store all data to the options array for use with JLayout
		$options = array();
		$options['name'] = trim(JText::_($name), '*?');
		$options['text'] = JText::_($text);
		$options['title'] = JText::_($title);
		$options['class'] = $this->fetchIconClass($name);
		$options['doTask'] = $this->_getCommand($url);

		if (($name == 'apply' || $name == 'new') && $btnClass == '')
		{
			$options['btnClass'] = 'btn btn-small btn-success';
			$options['class'] .= ' icon-white';
		}
		elseif ($btnClass != '')
		{
			$options['btnClass'] = $btnClass;
			$options['class'] .= $class;
		}
		else
		{
			$options['btnClass'] = 'btn btn-small';
			$options['class'] .= $class;
		}

		// Instantiate a new JLayoutFile instance and render the layout
		$layout = new JLayoutFile('toolbar.standardpop', JPATH_COMPONENT_ADMINISTRATOR . '/layouts');

		$html = array();
		$html[] = $layout->render($options);

		// Place modal div and scripts in a new div
		$html[] = '<div class="btn-group" style="width: 0; margin: 0">';

		// Build the options array for the modal
		$params = array();
		$params['title']  = $options['title'];
		$params['url']    = $options['doTask'];
		$params['height'] = $height;
		$params['width']  = $width;
		$html[] = JHtml::_('bootstrap.renderModal', 'modal-' . $name, $params);

		// If an $onClose event is passed, add it to the modal JS object
		if (strlen($onClose) >= 1)
		{
			$html[] = '<script>'
				. 'jQuery(\'#modal-' . $name . '\').on(\'hide\', function () {' . $onClose . ';});'
				. '</script>';
		}

		$html[] = '</div>';

		return implode("\n", $html);
	}

	/**
	 * Get the button CSS Id
	 *
	 * @param   string  $type  Button type
	 * @param   string  $name  Button name
	 *
	 * @return  string  Button CSS Id
	 *
	 * @since   3.0
	 */
	public function fetchId($type, $name)
	{
		return $this->_parent->getName() . '-standardpop-' . $name;
	}

	/**
	 * Get the JavaScript command for the button
	 *
	 * @param   string  $url  URL for popup
	 *
	 * @return  string   JavaScript command string
	 *
	 * @since   3.0
	 */
	private function _getCommand($url)
	{
		if (substr($url, 0, 4) !== 'http')
		{
			$url = JUri::base() . $url;
		}

		return $url;
	}
}
