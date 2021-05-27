
<div id="info-alert" class="alert alert-info alert-dismissible" role="alert">
    <button type="button" class="close" data-hide="alert" aria-label="{AdminLang::trans('global.close')}"><span aria-hidden="true">&times;</span></button>
    <i class="fas fa-check-circle"></i> <strong><span id="info-title"></span></strong>: <span id="info-message"></span>
</div>
<div id="success-alert" class="alert alert-success alert-dismissible" role="alert">
    <button type="button" class="close" data-hide="alert" aria-label="{AdminLang::trans('global.close')}"><span aria-hidden="true">&times;</span></button>
    <i class="fas fa-check-circle"></i> <strong>{AdminLang::trans('global.success')}</strong>: <span id="success-message"></span>
</div>
<div id="error-alert" class="alert alert-danger alert-dismissible" role="alert">
    <button type="button" class="close" data-hide="alert" aria-label="{AdminLang::trans('global.close')}"><span aria-hidden="true">&times;</span></button>
    <i class="fas fa-times-circle"></i> <strong>{AdminLang::trans('global.error')}</strong>: <span id="error-message"></span>
</div>

<script>
    function ShowInfoMessage(title, message) {
        $('#info-title').html(title);
        $('#info-message').html(message);
        $('#info-alert').show();
    }
    function ShowSuccessMessage(message) {
        $('#error-message').html('');
        $('#error-alert').hide();
        $('#success-message').html(message);
        $('#success-alert').show();
    }
    function ShowErrorMessage(message) {
        $('#success-message').html('');
        $('#success-alert').hide();
        $('#error-message').html(message);
        $('#error-alert').show();
    }

    $(document).ready(function(){
        $('#info-alert').hide();
        $('#success-alert').hide();
        $('#error-alert').hide();
        $("[data-hide]").on("click", function(){
            $(this).closest("." + $(this).attr("data-hide")).hide();
        });
    });
</script>
