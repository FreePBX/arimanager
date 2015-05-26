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
			<div class="col-sm-12">
				<div class="fpbx-container">
					<div class="display full-border">
						<?php echo $content ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
