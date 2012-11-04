<?php
/**
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');
require_once ''.JPATH_COMPONENT_ADMINISTRATOR.'/helpers/general.php';

	JHTML::_('behavior.tooltip');
	$listOrder	= $this->escape($this->state->get('list.ordering'));
	$listDirn	= $this->escape($this->state->get('list.direction'));
	$user		= JFactory::getUser();
	$userId		= $user->get('id');
?>
<form action="index.php" method="post" name="adminForm">
<div id="editcell">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_EASYTABLE_FILTER_SEARCH_DESC'); ?>" />

			<button type="submit" class="btn"><?php echo JText::_('COM_EASYTABLEPRO_LABEL_GO'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('COM_EASYTABLEPRO_LABEL_RESET'); ?></button>
		</div>
		<div class="filter-select fltrt">
			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions',array('published'=>1, 'unpublished'=>1, 'archived'=>0, 'trash'=>0, 'all'=>1)), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>

			<select name="filter_access" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'));?>
			</select>

			<select name="filter_author_id" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
				<?php echo JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author_id'));?>
			</select>
		</div>
	<div class="et_version_info">
		<?php echo JText::_('COM_EASYTABLEPRO_MGR_INSTALLED_VERSION').'&nbsp;'; ?>:: <span id="installedVersionSpan"><?php echo ( $this->et_current_version ); ?></span><br />
		<span id="et-subverinfo">
		<?php echo JText::_('COM_EASYTABLEPRO_MGR_CURRENT_SUBSCRIBERS_RELEASE_IS').'&nbsp;'; ?>:: <a href="http://seepeoplesoftware.com/release-notes/easytable-pro" target="_blank" title="<?php echo JText::_('COM_EASYTABLEPRO_MGR_OPEN_RELEASE_DESC'); ?>" class="hasTip"><span id="currentVersionSpan">X.x.x (abcdef)</span></a></span>
	</div>
	</fieldset>
	<div class="clr"> </div>
	<table class="adminlist">
	<thead>
		<tr>
			<th width="1%"><input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" /></th>
			<th width="25%"><?php echo JHtml::_('grid.sort', 'COM_EASYTABLEPRO_MGR_TABLE', 't.easytablename', $listDirn, $listOrder); ?></th>
			<th width="5%"><?php echo JText::_('COM_EASYTABLEPRO_MGR_EDIT_DATA'); ?></th>
			<th width="5%"><?php echo JText::_('COM_EASYTABLEPRO_MGR_UPLOAD_DATA'); ?></th>
			<th width="5%"><?php echo JHtml::_('grid.sort', 'JPUBLISHED', 't.published', $listDirn, $listOrder); ?></th>
			<th><?php echo JText::_('COM_EASYTABLEPRO_MGR_DESCRIPTION'); ?></th>
			<th width="1%"><?php echo JText::_('COM_EASYTABLEPRO_MGR_ID'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="13"><?php echo $this->pagination->getListFooter(); ?></td>
		</tr>
	</tfoot>
	<tbody>
	<?php
	$k = 0;

	for ($i=0, $n=count( $this->rows ); $i < $n; $i++)
	{
		$row =$this->rows[$i];

		$canCreate        = $this->canDo->get('core.create',              'com_easytablepro');
		$canEdit          = $this->canDo->get('core.edit',                'com_easytablepro.table.'.$row->id);
		$canCheckin       = $user->authorise('core.manage',               'com_checkin') || $row->checked_out == $userId || $row->checked_out == 0;
		$canEditOwn       = $this->canDo->get('core.edit.own',            'com_easytablepro.table.'.$row->id) && $row->created_by == $userId;
		$canChange        = $this->canDo->get('core.edit.state',          'com_easytablepro.table.'.$row->id) && $canCheckin;
		$canEditRecords   = $this->canDo->get('easytablepro.editrecords', 'com_easytablepro.table.' . $row->id);
		$canImportRecords = $this->canDo->get('easytablepro.import',      'com_easytablepro.table.' . $row->id);

		$rowParamsObj = new JRegistry;

		$rowParamsObj->loadString($row->params);

		$row->params = $rowParamsObj->toArray();
		$locked = ($row->checked_out && ($row->checked_out != $user->id));
		if ($locked)
		{
			$lockedBy = JFactory::getUser($row->checked_out); $lockedByName = $lockedBy->name;
		}
		else
		{
			$lockedByName = '';
		}
		$published = $this->publishedIcon($locked, $row, $i, $canCheckin, $lockedByName);
		$etet = $row->datatablename?true:false;

		?>
		<tr class="<?php echo "row$k"; ?>">
			<td>
				<?php echo JHtml::_('grid.id', $i, $row->id); ?>
			</td>
			<td>
				<?php if ($row->checked_out) : ?>
					<?php echo JHTML::_( 'jgrid.checkedout', $i, $row->editor, $row->checked_out_time, 'tables.', $canCheckin ); ?>
				<?php endif; ?>
				<?php echo $this->getEditorLink($locked,$i,$row->easytablename,$canEdit, $lockedByName); ?><div class="clr"></div>
				<span class="ept_tablelist_table_details"><?php echo JText::sprintf('COM_EASYTABLEPRO_TABLESX_BY_Y', $row->easytablealias, $row->author_name);?></span><div class="clr"></div>
				<span class="ept_tablelist_table_details"><?php echo JText::sprintf('COM_EASYTABLEPRO_TABLES_VIEWABLE_BY',ET_Helper::accessLabel($row->access)); ?></span>
				<span class="et_mgr_hits_counter"><?php echo JText::sprintf('COM_EASYTABLEPRO_MGR_HITS_COUNT', $row->hits); ?></span>
			</td>
			<td>
				<?php echo $this->getDataEditorIcon($locked,$i,$row->id,$row->easytablename,$etet,$canEditRecords, $lockedByName); ?>
			</td>
			<td>
				<?php echo $this->getDataUploadIcon($locked,$i,$row->id,$row->easytablename,$etet,$canImportRecords, $lockedByName); ?>
			</td>
			<td>
				<?php echo $published; ?>
			</td>
			<td>
				<span class="et_mgr_desc"><?php echo $row->description; ?></span>
			</td>
			<td>
				<?php echo $row->id; ?>
			</td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?></tbody>
	</table>
</div>
<?php echo JHTML::_('form.token'); ?>
<input type="hidden" name="option" value="<?php echo JRequest::getCmd('option') ?>" />
<input type="hidden" name="view" value="tables" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
<input type="hidden" name="boxchecked" value="0" />
</form>
