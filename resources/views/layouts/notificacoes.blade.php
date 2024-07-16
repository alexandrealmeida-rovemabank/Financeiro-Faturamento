@if(session('success'))
    <div class="alert alert-success alert-dismissible" id="alert-success">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <span id="alert-success-message">{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible" id="alert-error">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <span id="alert-error-message">{{ session('error') }}</span>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible" id="alert-warning">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <span id="alert-warning-message">{{ session('warning') }}</span>
    </div>
@endif

<div class="alert alert-success alert-dismissible" id="alert-success-v2" style="display:none;">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <span id="alert-success-message-v2"></span>
</div>

<div class="alert alert-danger alert-dismissible" id="alert-error-v2" style="display:none;">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <span id="alert-error-message-v2"></span>
</div>

<div class="alert alert-warning alert-dismissible" id="alert-warning-v2" style="display:none;">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <span id="alert-warning-message-v2"></span>
</div>
