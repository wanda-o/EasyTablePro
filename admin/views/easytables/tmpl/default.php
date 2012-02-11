<?php
/**
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

	JHTML::_('behavior.tooltip');
?>
<form action="index.php" method="post" name="adminForm">
<div id="editcell">
	<table>
		<tr>
			<td width="40%"><?php echo JText::_( 'COM_EASYTABLEPRO_LABEL_FILTER' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->search; ?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_( 'COM_EASYTABLEPRO_LABEL_GO' ); ?></button>
				<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'COM_EASYTABLEPRO_LABEL_RESET' ); ?></button>
			</td>
			<td class="nowrap et_version_info"><?php echo JText::_( 'COM_EASYTABLEPRO_MGR_INSTALLED_VERSION' ); ?>: <span id="installedVersionSpan"><?php echo ( $this->et_current_version ); ?></span> |
				<span id="et-subverinfo">
				<?php echo JText::_( 'COM_EASYTABLEPRO_MGR_CURRENT_SUBSCRIBERS_RELEASE_IS' ).'&nbsp;'; ?>: <a href="http://seepeoplesoftware.com/release-notes/easytable-pro" target="_blank" title="<?php echo JText::_( 'COM_EASYTABLEPRO_MGR_OPEN_RELEASE_DESC' ); ?>" class="hasTip"><span id="currentVersionSpan">X.x.x (abcdef)</span></a></span>
			</td>			
		</tr>
	</table>
	<table class="adminlist">
	<thead>
		<tr>
			<th width="5">
				<?php echo JText::_( 'COM_EASYTABLEPRO_MGR_ID' ); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->rows ); ?>);" />
			</th>			
			<th>
				<?php echo JText::_( 'COM_EASYTABLEPRO_MGR_TABLE' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'COM_EASYTABLEPRO_MGR_EDIT_DATA' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'COM_EASYTABLEPRO_MGR_UPLOAD_DATA' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'JPUBLISHED' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'COM_EASYTABLEPRO_MGR_SEARCHABLE' ); ?>
			</th>
			<th>
				<?php echo JText::_( 'COM_EASYTABLEPRO_MGR_DESCRIPTION' ); ?>
			</th>
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
	$user = JFactory::getUser();
	$userId = $user->id;

	for ($i=0, $n=count( $this->rows ); $i < $n; $i++)
	{
		$row = &$this->rows[$i];
		$rowParamsObj = new JParameter ($row->params);
		$locked = ($row->checked_out && ($row->checked_out != $user->id));
		if($locked) { $lockedBy = JFactory::getUser($row->checked_out); $lockedByName = $lockedBy->name; } else $lockedByName = '';
		$published = $this->publishedIcon($locked, $row, $i,$this->et_hasTableMgrPermission, $lockedByName);
		$etet = $row->datatablename?true:false;
		
		$searchableFlag = $rowParamsObj->get('searchable_by_joomla');
		$searchableImage  = $this->getSearchableTick( $i, $searchableFlag, $locked, $this->et_hasTableMgrPermission, $lockedByName);

		?>
		<tr class="<?php echo "row$k"; ?>">
			<td>
				<?php echo $row->id; ?>
			</td>
			<td>
				<?php if($this->et_hasTableMgrPermission){echo JHTML::_( 'grid.checkedout', $row, $i );} else {echo str_replace('"checkbox"','"hidden"',JHTML::_( 'grid.checkedout', $row, $i ));echo ('<span class="editlinktip hasTip" title="'.JText::_( 'COM_EASYTABLEPRO_MGR_NO_MGR_PERMISSIONS' ).'"><img src="images/checked_out.png"></span>');} ?>
			</td>
			<td>
				<?php echo $this->getEditorLink($locked,$i,$row->easytablename,$this->et_hasTableMgrPermission, $lockedByName); ?>
			</td>
			<td>
				<?php echo $this->getDataEditorIcon($locked,$i,$row->id,$row->easytablename,$etet,$this->et_hasDataEditingPermission, $lockedByName); ?>
			</td>
			<td>
				<?php echo $this->getDataUploadIcon($locked,$i,$row->id,$row->easytablename,$etet,$this->et_hasDataUploadPermission, $lockedByName); ?>
			</td>
			<td>
				<?php echo $published; ?>
			</td>
			<td>
				<?php echo $searchableImage; ?>
			</td>
			<td>
				<?php echo $row->description; ?>
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
<input type="hidden" name="view" value="easytables" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
</form>
