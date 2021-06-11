{include file="alerts.tpl"}

<table id="tbl-templates" class="datatable stripe hover" width="100%" border="0" cellspacing="2" cellpadding="3">
    <thead>
        <tr>
            <th>{AdminLang::trans('addons.name')}</th>
            <th>{AdminLang::trans('fields.created')}</th>
            <th data-searchable="false" data-orderable="false">{AdminLang::trans('fields.action')}</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<form method="post" action="systemactivitylog.php">
    <button type="button" class="btn btn-default" id="btn-refresh"><i class="fas fa-sync"></i> {AdminLang::trans('global.refresh')}</button>
    <button type="button" class="btn btn-primary" id="btn-add" data-toggle="modal" data-target="#template-modal"><i class="fas fa-plus-circle"></i> {AdminLang::trans('global.add')}</button>
    <input type="hidden" name="description" value="[DNS]" />
    <button type="submit" class="btn btn-info" id="btn-logs"><i class="fas fa-file-alt"></i> {AdminLang::trans('utilities.logs')}</button>
</form>

{include file="modal.tpl"}

<script>
    var tblTemplates;
    $(document).ready(function() {
        tblTemplates = $('#tbl-templates').DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 75, 100], [10, 25, 50, 75, 100]],
            stateSave: false,
            serverSide: false,
            deferRender: true,
            processing: true,
            searching: true,
            ajax: {
                url: '{$modulelink}&page=service&action=getTemplates',
                type: 'GET',
                error: function(xhr) {
                    ShowErrorMessage(xhr.responseJSON.error);
                }
            },
            columns: [
                {
                    data: 'name',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            let html = data;
                            if (row.default) {
                                html += ' <i class="fas fa-check-circle" style="color:green" title="{AdminLang::trans('global.default')}"></i>';
                            }
                            return html;
                        }
                        return data;
                    }
                },
                { data: 'created_at' },
                {
                    render: function(data, type, row) {
                        return '<button class="btn btn-info btn-xs" data-toggle="modal" data-template-id="' + row.id + '" data-target="#template-modal"><i class="fas fa-edit"></i> {AdminLang::trans('global.edit')} </button>';
                    }
                }
            ]
        });

        $('#btn-refresh').click(function() {
            tblTemplates.ajax.reload();
        });
    });
</script>
