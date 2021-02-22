<div id="event-detail">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">@lang('modules.notices.notice'): {{ $notice->heading }}</h4>
    </div>
    <div class="modal-body">
        <div class="form-body">
            <div class="row">
                <div class="col-md-12 ">
                    <p>{!! $notice->description !!}</p>
                </div>
                @if(!is_null($notice->attachment))
                    <div class="col-xs-12">
                        <a  target="_blank" href="{{ $notice->file_url }}" title="@lang('app.viewAttachment')">
                                        <span class="btn btn-sm btn-info">
                                            @lang('app.viewAttachment')
                                        </span>
                        </a>
                    </div>
                @endif
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-white waves-effect" data-dismiss="modal">@lang('app.close')</button>
        </button>
    </div>
</div>