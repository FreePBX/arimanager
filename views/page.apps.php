<div class="container-fluid">
	<h1><?php echo _('Asterisk REST Interface Applications')?></h1>
	<div class = "display full-border">
		<div class="row">
			<div class="col-sm-12">
				<div class="fpbx-container">
					<div>
						<div class="alert alert-info">
                            <b><?php echo _("The following applications have registered with Asterisk.")?></b>
                        </div>
                        <table id="appgrid"
                            data-url="ajax.php?module=arimanager&command=listApps"
                            data-cache="false"
                            data-maintain-selected="true"
                            data-toggle="table"
                            data-pagination="true"
                            data-detail-view="true"
                            data-detail-formatter="moreDetails"
                            data-show-refresh="true"
                            data-search="true"
                            class="table table-striped">
                            <thead>
                                <tr>
                                    <th data-field="name" data-sortable='true'><?php echo _("Application Name")?></th>
                                    <th data-formatter="CHFormatter"><?php echo _("Channel Count")?></th>
                                    <th data-formatter="BRFormatter"><?php echo _("Bridge Count")?></th>
                                    <th data-formatter="EPFormatter"><?php echo _("Endpoint Count")?></th>
                                    <th data-formatter="DEVFormatter"><?php echo _("Device Count")?></th>
                                </tr>
                            </thead>
                        </table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>