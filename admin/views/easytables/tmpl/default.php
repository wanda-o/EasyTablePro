<?php
/**
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

	JHTML::_('behavior.tooltip');
	JToolBarHelper::title(JText::_( 'EASYTABLEPRO' ), 'easytables');
	JToolBarHelper::publishList();
	JToolBarHelper::unpublishList();
	JToolBarHelper::editList();
	JToolBarHelper::deleteList(JText::_( 'ARE_YOU_SURE_YOU_TO_DELETE_THE_TABLE_S__' ));
	JToolBarHelper::addNew();
	JToolBarHelper::preferences( 'com_'._cppl_this_com_name, 420 );
?>
<div id="et-versionCheck" style="text-size:0.9em;text-align:center; color:grey;position:relative;z-index:1;" >
	<?php echo JText::_( 'INSTALLED_EASYTABLE_VERSION' ); ?>: <?php echo ( $this->et_current_version ); ?> | 
	<span id="et-subverinfo" onmouseover="ShowTip('et-subverinfo-panel'); return false;" onMouseOut="HideTip('et-subverinfo-panel'); return false;">
		<?php echo JText::_( 'CURRENT_SUBSCRIBERS_RELEASE_IS' ).'&nbsp;'; ?>: <?php echo ( $this->et_subscriber_version ); if(strcmp($this->et_current_version,$this->et_subscriber_version) != 0 ){echo '<img src="'.JURI::base().'components'.DS.'com_'._cppl_this_com_name.DS.'assets'.DS.'images'.DS.'attention.gif">';} ?>
		<div id="et-subverinfo-panel" class="info-tip" style="left:60%;">
			<?php echo ( $this->et_subscriber_tip ); ?>
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
		$published = $this->publishedIcon($locked, $row, $i);
		
		$searchableFlag = $rowParamsObj->get('searchable_by_joomla');
		$searchableImage  = $this->getSearchableTick( $i, $searchableFlag, $locked );

		?>
		<tr class="<?php echo "row$k"; ?>">
			<td>
				<?php echo $row->id; ?>
			</td>
			<td>
				<?php echo JHTML::_( 'grid.checkedout', $row, $i ); ?>
			</td>
			<td>
				<?php echo $this->getEditorLink($locked,$i,$row->easytablename) ?>
			</td>
			<td>
				<?php echo $this->getDataEditorIcon($locked,$i,$row->id,$row->easytablename) ?>
			</td>
			<td>
				<?php echo $this->getDataUploadIcon($locked,$i,$row->id,$row->easytablename) ?>
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
