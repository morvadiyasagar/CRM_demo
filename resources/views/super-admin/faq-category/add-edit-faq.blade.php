<link rel="stylesheet" href="{{ asset('plugins/bower_components/summernote/dist/summernote.css') }}">
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">@if(isset($faq->id)) @lang('app.edit') @else @lang('app.addNew') @endif @lang('app.menu.faq')</h4>
</div>
<div class="modal-body">
    <div class="portlet-body">

        {!! Form::open(['id'=>'addEditFaq','class'=>'ajax-form']) !!}
        @if(isset($faq->id)) <input type="hidden" name="_method" value="PUT"> @endif
        <input type="hidden" name="faq_category_id" value="{{ $faqCategoryId }}">
        <div class="form-body">
            <div class="row">
                <div class="col-xs-12 ">
                    <div class="form-group">
                        <label>@lang('app.title')</label>
                        <input type="text" name="title" class="form-control" value="{{ $faq->title ?? '' }}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 ">
                    <div class="form-group">
                        <label>@lang('app.description')</label>
                        <textarea name="description" class="form-control summernote">{{ $faq->description ?? '' }}</textarea>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="exampleInputPassword1">@lang('app.image')</label>
                <div class="col-xs-12">
                    <div class="fileinput fileinput-new" data-provides="fileinput">
                        <div class="fileinput-new thumbnail"
                             style="width: 200px; height: 150px;">
                            <img src="{{ $faq->image_url }} " alt=""/>
                        </div>
                        <div class="fileinput-preview fileinput-exists thumbnail"
                             style="max-width: 200px; max-height: 150px;"></div>
                        <div>
                        <span class="btn btn-info btn-file">
                            <span class="fileinput-new"> @lang('app.selectImage') </span>
                            <span class="fileinput-exists"> @lang('app.change') </span>
                            <input type="file" name="image" id="image"> </span>
                            <a href="javascript:;" class="btn btn-danger fileinput-exists"
                               data-dismiss="fileinput"> @lang('app.remove') </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-actions">
            <button type="button" id="save-faq-category" onclick="saveFAQ({{ $faqCategoryId }} {{ isset($faq->id) ? ','.$faq->id : '' }});return false;" class="btn btn-success"> <i class="fa fa-check"></i> @lang('app.save')</button>
            @if(isset($faq->id))
                <button type="button" onclick="deleteFAQ({{ $faqCategoryId }}, {{  $faq->id }});return false;" class="btn btn-danger"> <i class="fa fa-trash"></i> @lang('app.delete')</button>
            @endif
        </div>
        {!! Form::close() !!}
    </div>
</div>
<script src="{{ asset('plugins/bower_components/summernote/dist/summernote.min.js') }}"></script>
<script>
    $('.summernote').summernote({
        height: 200,                 // set editor height
        minHeight: null,             // set minimum height of editor
        maxHeight: null,             // set maximum height of editor
        focus: false,
        dialogsInBody: true,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link',  'hr','video']],
            ['view', ['fullscreen', 'codeview']],
            ['help', ['help']]
        ]
    });
</script>
