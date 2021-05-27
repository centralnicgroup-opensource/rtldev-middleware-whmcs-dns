<div class="modal fade" id="template-modal" tabindex="-1" role="dialog" aria-labelledby="template-modal-label">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{AdminLang::trans('global.close')}"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="template-modal-label"><span id="template-modal-title"></span> {$lang.template}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-danger alert-dismissible" id="template-alert-error" role="alert">
                            <button type="button" class="close" data-hide="alert" aria-label="{AdminLang::trans('global.close')}"><span aria-hidden="true">&times;</span></button>
                            <i class="fas fa-times-circle"></i> <strong>{AdminLang::trans('global.error')}</strong>: <span id="template-error"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12" class="form-group">
                        <label>{AdminLang::trans('addons.name')}</label>
                        <input type="text" name="template-name" id="template-name" class="form-control" />
                        <label>{$lang.zone}</label>
                        <textarea name="template-zone" id="template-zone" rows="8" class="form-control"></textarea>
                        <br/>

                        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                            <div class="panel panel-default">
                                <div class="panel-heading" role="tab" id="headingTwo">
                                    <h4 class="panel-title">
                                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                            {AdminLang::trans('setup.products')}
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
                                    <div class="panel-body">
                                        <p>{$lang.setDefaultProducts}:</p>
                                        {foreach $products as $product}
                                            <label>
                                                <input type="checkbox" name="template-product[{$product->id}]" value="{$product->id}" />
                                                {$product->name}
                                            </label>
                                            <br />
                                        {/foreach}
                                    </div>
                                </div>
                            </div>
                            <div class="panel panel-info">
                                <div class="panel-heading" role="tab" id="headingOne">
                                    <h4 class="panel-title">
                                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                            {$lang.instructions}
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
                                    <div class="panel-body">
                                        {$lang.instructions1}:<br />
                                        <pre>{$lang.instructions2}</pre>
                                        <ul>
                                            <li>{$lang.instructions3}:<br />A, AAAA, MX, MXE, CNAME, TXT, SRV, URL, FRAME</li>
                                            <li>{$lang.instructions4}</li>
                                            <li>{$lang.instructions5}</li>
                                            <li>{$lang.instructions6}</li>
                                        </ul>
                                        {$lang.example}:
                                        <pre>
@ A %ip%
www CNAME @
mail A 127.0.0.1
@ MX mail.@ 10</pre>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <label>
                            <input type="checkbox" name="template-default" id="template-default" value="default" />
                            {$lang.setDefaultGlobal}
                        </label>

                        <input type="hidden" name="template-id" id="template-id" value="0" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btn-edit"><i class="fas fa-save" id="template-icon"></i> {AdminLang::trans('global.save')}</button>
                <button type="button" class="btn btn-danger" id="btn-delete"><i class="fas fa-trash" id="delete-icon"></i> {AdminLang::trans('global.delete')}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fas fa-times-circle"></i> {AdminLang::trans('global.close')}</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        $('#template-modal').on('show.bs.modal', function (event) {
            resetForm();
            let templateId = $(event.relatedTarget).data('template-id');
            $('#template-modal-title').html(templateId ? '{AdminLang::trans('global.edit')}' : '{AdminLang::trans('global.add')}')
            if (templateId) {
                $.ajax({
                    url: '{$modulelink}&page=service&action=getTemplate&id=' + templateId,
                    type: 'GET',
                    dataType: 'json'
                }).done(function(data) {
                    $('#template-id').val(templateId);
                    $('#template-name').val(data[0].name);
                    $('#template-zone').val(data[0].zone);
                    $('#template-default').prop('checked', !!data[0].default);
                    for (const template of data) {
                        $("input:checkbox[name='template-product[" + template.product_id + "]']").prop('checked', true);
                    }
                    $('#btn-delete').show();
                }).fail(function(xhr) {
                    $('#template-alert-error').show();
                    $('#template-error').html(xhr.responseJSON?.error ?? xhr.status);
                });
            }
        }).on('hide.bs.modal', function () {
            resetForm();
        });

        function resetForm() {
            $('#template-id').val(0);
            $('#template-name').val('');
            $('#template-zone').val('');
            $('#template-default').prop('checked', false);
            $("input:checkbox:checked[name^='template-product']").prop('checked', false);
            $('#template-alert-error').hide();
            $('#template-error').html('');
            $('#template-alert-success').hide();
            $('#template-success').html('');
            $('#btn-delete').hide();
        }

        $('#btn-edit').click(function() {
            const templateId = $('#template-id').val();
            const templateName = $('#template-name').val();
            if (!templateName) {
                $('#template-alert-error').show();
                $('#template-error').html('{AdminLang::trans('addons.name')} {AdminLang::trans('global.required')}');
                return;
            }
            $('#btn-edit').prop('disabled', true);
            $('#template-icon').removeClass('fa-save').addClass('fa-spinner fa-spin');
            $.ajax({
                url: '{$modulelink}&page=service',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: templateId > 0 ? 'editTemplate' : 'createTemplate',
                    id: templateId,
                    name: templateName,
                    zone: $('#template-zone').val(),
                    default: $('#template-default').prop('checked'),
                    products: $("input:checkbox:checked[name^='template-product']").map(function(){
                        return $(this).val();
                    }).get()
                }
            }).done(function() {
                $('#template-modal').modal('hide');
                ShowSuccessMessage('{AdminLang::trans('orders.notesSaved')}!');
                tblTemplates.ajax.reload();
            }).fail(function(xhr) {
                $('#template-alert-error').show();
                $('#template-error').html(xhr.responseJSON?.error ?? xhr.status);
            }).always(function() {
                $('#btn-edit').prop('disabled', false);
                $('#template-icon').removeClass('fa-spinner fa-spin').addClass('fa-save');
            });
        });

        $('#btn-delete').click(function() {
            if (!confirm("{$lang.deleteConfirm}")) {
                return;
            }
            $('#delete-icon').removeClass('fa-trash').addClass('fa-spinner fa-spin');
            $('#btn-delete').prop('disabled', true);
            $.ajax({
                url: '{$modulelink}&page=service',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: "deleteTemplate",
                    id: $('#template-id').val()
                }
            }).done(function(data) {
                $('#template-modal').modal('hide');
                ShowSuccessMessage('{AdminLang::trans('status.deleted')}!');
                tblTemplates.ajax.reload();
            }).fail(function(xhr) {
                $('#template-alert-error').show();
                $('#template-error').html(xhr.responseJSON?.error ?? xhr.status);
            }).always(function() {
                $('#btn-delete').prop('disabled', false);
                $('#delete-icon').removeClass('fa-spinner fa-spin').addClass('fa-trash');
            });
        });
    });
</script>
