<?php
$info = '';
if(!$httpenabled) {
	$info = '<div class="alert alert-warning">'. _("The Asterisk mini-HTTP Server is Currently Disabled in Advanced Settings").'</div>';
} else if(!$arienabled) {
	$info = '<div class="alert alert-warning">'. _('The Asterisk REST Interface is Currently Disabled in Advanced Settings').'</div>';
}
?>
<div class="container-fluid">
	<h1><?php echo _('Asterisk Rest Interface Users')?></h1>
		<?php echo $info?>
	<div class = "display full-border">
		<div class="row">
			<div class="col-sm-9">
				<div class="fpbx-container">
					<div class="display full-border">
						<?php echo $content ?>
					</div>
				</div>
			</div>
			<div class="col-sm-3 hidden-xs bootnav <?php  echo isset($_REQUEST['view'])?'':'hidden'?>">
				<div class="list-group">
					<a href="?display=arimanager" class="list-group-item"><i class="fa fa-list"></i> <?php echo _("List Users")?></a>
					<a href="?display=arimanager&view=form" class="list-group-item <?php  echo isset($_REQUEST['user'])?'':'hidden'?>"><i class="fa fa-plus"></i> <?php echo _("Add User")?></a>
				</div>
			</div>
		</div>
	</div>
</div>
