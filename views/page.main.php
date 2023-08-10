<?php
	$view = isset($request['view']) ? $request['view'] : '';
	switch($view)
	{
		case 'form':
			$content = $arimanager->showPage('form');
			break;

		default:
			$content = $arimanager->showPage('grid');
			break;
	}

	$info = '';
	if(! $config['httpenabled'])
	{
		$info = sprintf('<div class="alert alert-warning">%s</div>', _("The Asterisk mini-HTTP Server is Currently Disabled in Advanced Settings"));
	}
	else if(! $config['arienabled'])
	{
		$info = sprintf('<div class="alert alert-warning">%s</div>', _('The Asterisk REST Interface is Currently Disabled in Advanced Settings'));
	}
?>
<div class="container-fluid">
	<h1><?php echo _('Asterisk Rest Interface Users')?></h1>
	<?php echo $info?>
	<div class="row">
		<div class="col-sm-12">
			<div class="fpbx-container">
				<div class="display <?php echo !isset($_GET['view']) ? 'no' : 'full'?>-border">
					<?php echo $content ?>
				</div>
			</div>
		</div>
	</div>
</div>