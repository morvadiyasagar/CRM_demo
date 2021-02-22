<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">SEO ({{ $seoDetail ? $seoDetail->page_name : '' }})</h4>
</div>
<div class="modal-body">
    <div class="portlet-body">
        {!! Form::open(['id'=>'editSeoDetail','class'=>'ajax-form','method'=>'PUT']) !!}
        <div class="form-body">
            <div class="row">
                <div class="col-xs-12">
                    <div class="form-group">
                        <label for="seo_title">@lang('modules.frontCms.seo_title')</label>
                        <input type="text" class="form-control" id="seo_title" name="seo_title"
                               value="{{ $seoDetail ? $seoDetail->seo_title : '' }}">
                    </div>
                </div>
                <div class="col-xs-12">
                    <div class="form-group">
                        <label for="seo_author">@lang('modules.frontCms.seo_author')</label>
                        <input type="text" class="form-control" id="seo_author" name="seo_author"
                            value="{{ $seoDetail ? $seoDetail->seo_author : '' }}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="form-group">
                        <label for="address">@lang('modules.frontCms.seo_keywords')</label>
                        <textarea class="form-control" id="seo_keywords" rows="5" name="seo_keywords">{{ $seoDetail ? $seoDetail->seo_keywords : '' }}</textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="form-group">
                        <label for="address">@lang('modules.frontCms.seo_description')</label>
                        <textarea class="form-control" id="seo_description" rows="5"
                                  name="seo_description">{{ $seoDetail ? $seoDetail->seo_description : '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-actions">
        <button type="button" id="save-seo-detail" onclick="updateSeoDetail('{{ $seoDetail->id }}');" class="btn btn-success"> <i class="fa fa-check"></i> @lang('app.update')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<script>

    function updateSeoDetail(id) {
        var url = '{{route('super-admin.seo-detail.update', ':id')}}';
        url = url.replace(':id', id)
        $.easyAjax({
            url: url,
            container: '#editSeoDetail',
            type: "POST",
            data: $('#editSeoDetail').serialize(),
        })
    };
</script>
