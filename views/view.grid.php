<div id="toolbar-all">
    <a href="#" class="btn btn-default" data-toggle="modal" data-target="#userForm">
        <i class="fa fa-plus"></i> <?php echo _("Add User") ?>
    </a>
</div>
<table 
    id="ariusergrid"
    data-escape="true"
    data-url="ajax.php?module=arimanager&command=grid"
    data-cache="false"
    data-toolbar="#toolbar-all"
    data-maintain-selected="true"
    data-show-refresh="true"
    data-toggle="table"
    data-pagination="true"
    data-search="true"
    class="table table-striped">
    <thead>
        <tr>
            <th data-field="name" class="col-md-10"><?php echo _("Username")?></th>
            <th data-field="read_only" data-formatter="roFormatter" class="col-md-1 text-center"><?php echo _("Read Only")?></th>
            <th data-field="id" data-formatter="linkFormatter" class="col-md-1 text-center"><?php echo _("Actions")?></th>
        </tr>
    </thead>
</table>