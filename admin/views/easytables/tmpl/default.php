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
<div id="et-versionCheck" style="text-size:0.9em;text-align:center; color:grey;position:relative;z-index:1;" >
	<?php echo JText::_( 'INSTALLED_EASYTABLE_VERSION' ); ?>: <span id="installedVersionSpan"><?php echo ( $this->et_current_version ); ?></span> | 
	<span id="et-subverinfo">
		<?php echo JText::_( 'CURRENT_SUBSCRIBERS_RELEASE_IS' ).'&nbsp;'; ?>: <a href="http://seepeoplesoftware.com/release-notes/easytable-pro" target="_blank" title="<?php echo JText::_( 'OPEN_RELEASE_DESC' ); ?>" class="hasTip"><span id="currentVersionSpan">X.x.x (abcdef)</span></a>
		<div id="et-subverinfo-panel" class="info-tip" >

		</div>
	</span>
</div>
<form action="index.php" method="post" name="adminForm">
<div id="editcell">
	<table class="adminlist">
	<thead>
		<tr>
			<th width="5">
				<?php echo JText::_( 'ID' ); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->rows ); ?>);" />
			</th>			
			<th>
				<?php echo JText::_( 'TABLE' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'EDIT_DATA' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'UPLOAD_DATA' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'PUBLISHED' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'SEARCHABLE' ); ?>
			</th>
			<th>
				<?php echo JText::_( 'DESCRIPTION' ); ?>
			</th>
		</tr>
	</thead>
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
				<?php if($this->et_hasTableMgrPermission){echo JHTML::_( 'grid.checkedout', $row, $i );} else {echo ('<span class="editlinktip hasTip" title="'.JText::_( 'TABLE_MANAGER_DESC' ).'"><img src="images/checked_out.png"></span>');} ?>
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
	?>
	</table>
</div>
<?php echo JHTML::_('form.token'); ?>
<input type="hidden" name="option" value="<?php echo $option ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
</form>
