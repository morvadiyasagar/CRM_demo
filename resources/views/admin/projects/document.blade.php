@extends('layouts.app')

@section('page-title')
    <div class="row bg-title">
        <!-- .page title -->
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"><i class="fa fa-file"></i>Documents</h4>
        </div>
        <!-- /.page title -->
        <!-- .breadcrumb -->
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="{{ route('admin.dashboard') }}">@lang('app.menu.home')</a></li>
                <li><a href="{{ route('admin.projects.index') }}">Documents</a></li>

            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')
<link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/summernote/dist/summernote.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/custom-select/custom-select.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/dropzone-master/dist/dropzone.css') }}">

<style>
    .panel-black .panel-heading a, .panel-inverse .panel-heading a {
        color: unset!important;
    }
    .bootstrap-select.btn-group .dropdown-menu li a span.text {
        color: #000;
    }
    .panel-black .panel-heading a:hover, .panel-inverse .panel-heading a:hover {
        color: #000 !important;
    }
    .panel-black .panel-heading a, .panel-inverse .panel-heading a {
        color: #000 !important;
    }
    .btn-info.active, .btn-info:active, .open>.dropdown-toggle.btn-info {
        background-color:unset !important; ;
        border-color: #269abc;
    }
    .note-editor{
        border: 1px solid #e4e7ea !important;
    }
    .btn-info.active.focus, .btn-info.active:focus, .btn-info.active:hover, .btn-info.focus, .btn-info.focus:active, .btn-info:active:focus, .btn-info:active:hover, .btn-info:focus, .open>.dropdown-toggle.btn-info.focus, .open>.dropdown-toggle.btn-info:focus, .open>.dropdown-toggle.btn-info:hover {
        background-color: #03a9f3;
        border: 1px solid #03a9f3;
        color: #000;
    }
</style>
@endpush

@section('content')

    <div class="row">
        <div class="col-xs-12">

            <div class="panel panel-inverse">
                
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        {!! Form::open(['id'=>'createProject','class'=>'ajax-form','method'=>'POST']) !!}
                        <div class="form-body">
                            <!-- <h3 class="box-title m-b-10">@lang('modules.projects.projectInfo')</h3> -->
                            <div class="row">
                               
                                <div class="col-xs-12 col-md-4 ">
                                    <div class="form-group">
                                    <label class="required">@lang('modules.projects.selectClient')</label>

                                        <select class="select2 form-control" name="client_id" id="client_id"
                                                data-style="form-control" onchange="myFunction()">
                                                <option value="">@lang('modules.projects.selectClient')</option>
                                            @foreach($clients as $client)
                                                <option value="{{ $client->id }}">{{ ucwords($client->name) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                  
                                </div>
                                
                                <div class="col-xs-12 col-md-4 ">
                                
                                    <div class="form-group">
                                    <label class="required">@lang('modules.projects.projectName')</label>

                                    <select class="select2 form-control" name="project_nm" id="project_nm"
                                                data-style="form-control"  onchange="getprojectfle()">   
                                                </select>          
                                    </div>
                                </div>
                            </div>
                          
                          
                           
                            <ul class="list-group" id="files-list">
                                                  
                                                       
                                                   

                                                  </ul>
                            
                

                              

                            </div>

                           
                           

                            <div class="row m-b-20">
                                <div class="col-xs-12">
                                    @if($upload)
                                    <button type="button" class="btn btn-block btn-outline-info btn-sm col-md-2 select-image-button" style="margin-bottom: 10px;display: none "><i class="fa fa-upload"></i> File Select Or Upload</button>
                                    <div id="file-upload-box" >
                                        <div class="row" id="file-dropzone">
                                            <div class="col-xs-12">
                                                <div class="dropzone"
                                                     id="file-upload-dropzone">
                                                    {{ csrf_field() }}
                                                    <div class="fallback">
                                                        <input name="file" type="file" multiple/>
                                                    </div>
                                                    <input name="image_url" id="image_url"type="hidden" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="projectID" id="projectID">
                                    @else
                                        <div class="alert alert-danger">@lang('messages.storageLimitExceed', ['here' => '<a href='.route('admin.billing.packages'). '>Here</a>'])</div>
                                    @endif
                                </div>
                            </div>
                            <!--/span-->

                          

                        </div>
                        <div class="form-actions">
                            <button type="submit" id="save-form" class="btn btn-success"><i class="fa fa-check"></i>
                                @lang('app.save')
                            </button>

                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>    <!-- .row -->

                                                
                                            

    {{--Ajax Modal--}}
    <div class="modal fade bs-modal-md in" id="projectCategoryModal" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-md" id="modal-data-application">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <span class="caption-subject font-red-sunglo bold uppercase" id="modelHeading"></span>
                </div>
                <div class="modal-body">
                    Loading...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn blue">Save changes</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    {{--Ajax Modal Ends--}}
@endsection

@push('footer-script')
<script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/summernote/dist/summernote.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/custom-select/custom-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/dropzone-master/dist/dropzone.js') }}"></script>

<script>

    

    projectID = '';
    Dropzone.autoDiscover = false;
    //Dropzone class
    myDropzone = new Dropzone("div#file-upload-dropzone", {
        url: "{{ route('admin.files.multiple-upload') }}",
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        paramName: "file",
        maxFilesize: 10,
        maxFiles: 10,
        acceptedFiles: "image/*,application/pdf",
        autoProcessQueue: false,
        uploadMultiple: true,
        addRemoveLinks:true,
        parallelUploads:10,
        init: function () {
            myDropzone = this;
        }
    });
    myDropzone.on('sending', function(file, xhr, formData) {
        console.log([formData, 'formData']);
        var ids = $('#projectID').val();
        formData.append('project_id', ids);
    });
    myDropzone.on('completemultiple', function () {
        var msgs = "@lang('modules.projects.projectUpdated')";
        $.showToastr(msgs, 'success');
        $('#file-upload-dropzone').html('');
        getprojectfle();
    });
    $(".select2").select2({
        formatNoMatches: function () {
            return "{{ __('messages.noRecordFound') }}";
        }
    });
    $('#clientNotification').hide();
   

    // check client view task checked
    function checkTask()
    {
        var chVal = $('#client_view_task').is(":checked") ? true : false;
        if(chVal == true){
            $('#clientNotification').show();
        }
        else{
            $('#clientNotification').hide();
        }
    }

    $('#without_deadline').click(function () {
        var check = $('#without_deadline').is(":checked") ? true : false;
        if(check == true){
            $('#deadlineBox').hide();
        }
        else{
           $('#deadlineBox').show();
        }
    });

    // Set selected Template
    function setTemplate(id){
        var url = "{{ route('admin.projects.template-data',':id') }}";
        url = url.replace(':id', id);
        $.easyAjax({
            url: url,
            container: '#createProject',
            type: "GET",
            success: function(response){
                var selectedTemplate = [];
                if(id != null && id != undefined && id != ""){
                    selectedTemplate = response.templateData;

                    if(response.member){
                        $('#selectEmployee').val(response.member);
                        $('#selectEmployee').trigger('change');
                    }

                    $('#project_name').val(selectedTemplate['project_name']);
                    $('#category_id').selectpicker('val', selectedTemplate['category_id']);
                    $('#project_summary').summernote('code', selectedTemplate['project_summary']);
                    $('#notes').summernote('code', selectedTemplate['notes']);
                    $('#template_id').val(selectedTemplate['id']);

                    if(selectedTemplate['client_view_task'] == 'enable'){
                        $("#client_view_task").prop('checked', true);
                        $('#clientNotification').show();
                        if(selectedTemplate['allow_client_notification'] == 'enable'){
                            $("#client_task_notification").prop('checked', 'checked');
                        }
                        else{
                            $("#client_task_notification").prop('checked', false);
                        }
                    }
                    else{
                        $("#client_view_task").prop('checked', false);
                        $("#client_task_notification").prop('checked', false);
                        $('#clientNotification').hide();
                    }
                    if(selectedTemplate['manual_timelog'] == 'enable'){
                        $("#manual_timelog").prop('checked', true);
                    }
                    else{
                        $("#manual_timelog").prop('checked', false);
                    }
                }
            }
        })

    }


   

    

    $('#save-form').click(function () {
       
                @if($upload)
                    dropzone = myDropzone.getQueuedFiles().length;
                @endif

                if(dropzone > 0){
                  
                     projectID = document.getElementById("project_nm").value;
                    $('#projectID').val(projectID);
                    myDropzone.processQueue();
                    var msgs = "@lang('modules.projects.projectUpdated')";
                    $.showToastr(msgs, 'success');
                  
                }
                else{
                    var msgs = "@lang('modules.projects.projectUpdated')";
                    $.showToastr(msgs, 'success');
                    
                }
          
    });

    $('.summernote').summernote({
        height: 200,                 // set editor height
        minHeight: null,             // set minimum height of editor
        maxHeight: null,             // set maximum height of editor
        focus: false,
        toolbar: [
            // [groupName, [list of button]]
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough']],
            ['fontsize', ['fontsize']],
            ['para', ['ul', 'ol', 'paragraph']],
            ["view", ["fullscreen"]]
        ]
    });

    $(':reset').on('click', function(evt) {
        evt.preventDefault()
        $form = $(evt.target).closest('form')
        $form[0].reset()
        $form.find('select').selectpicker('render')
    });
</script>

<script>
    $('#createProject').on('click', '#addProjectCategory', function () {
        var url = '{{ route('admin.projectCategory.create-cat')}}';
        $('#modelHeading').html('Manage Project Category');
        $.ajaxModal('#projectCategoryModal', url);
    })

    $('#add-employee').click(function () {
        var url = '{{ route('admin.employees.create')}}';
        $('#modelHeading').html("@lang('app.add') @lang('app.employee')");
        $.ajaxModal('#projectTimerModal', url);
    });

    $('#add-client').click(function () {
        var url = '{{ route('admin.clients.create')}}';
        $('#modelHeading').html("@lang('app.add') @lang('app.client')");
        $.ajaxModal('#projectTimerModal', url);
    });
</script>
<script>
function myFunction(){
            var x = document.getElementById("client_id").value;
             $.easyAjax({
            type: 'GET',
            url: "{{ url('getclientproject') }}",
            data: {
              id: x
            },
            success: function (response) {
              
                var html="";
              

for (i = 0; i < response.length; i++) {
    var id = '';
    var name = '';
    // alert(data.length);
    name = response[i].project_name;
    id = response[i].id;

    html += '<option  value="' + id + '" >' + name + '</option>';


}
$("#project_nm").html(html);
            }
        });

//         $.ajax({
//             data: {
//                 qustext: qustext,
//                 save_update: save_update,



//             },
//             url: "{{ url('getclientproject') }}",
//             type: "POST",
//             dataType: 'json',
//             // async: false,
//             success: function(data) {
//  }
//         });
               
        }

function getprojectfle(){
    var project_nm = document.getElementById("project_nm").value;
    $.easyAjax({
            type: 'GET',
            url: "{{ url('getprojectfiles') }}",
            data: {
              id: project_nm
            },
            success: function (response) {
                var html="";
                for (i = 0; i < response.length; i++) {

                html+='<li class="list-group-item">'+
                                                            '<div class="row">'+
                                                                '<div class="col-md-9">'+ response[i].filename+'</div>'+
                                                                '<div class="col-md-3">'+
                                                                        '<a id="'+response[i].fileid+'" href="#" data-toggle="tooltip" data-original-title="View" class="btn btn-info btn-circle "><i class="fa fa-search"></i></a>'+

                                                                  
                                                                    
                                                                    '<a  href="{{ url('downloadfile') }}/'+response[i].fileid+'"  id="'+response[i].fileid+'"  data-toggle="tooltip" data-original-title="Download" class="btn btn-default btn-circle download"><i class="fa fa-download"></i></a>'+
                                                                   
                                                                    
                                                                    '<a href="javascript:;" data-toggle="tooltip" data-original-title="Delete" data-file-id="'+response[i].id+'" class="btn btn-danger btn-circle sa-params" data-pk="list"><i class="fa fa-times"></i></a>'+
                                                               '</div>'+
                                                            '</div>'+
                                                        '</li>';
                }
                $('#files-list').html(html);
            }
        });

}
   function downloadfile(){
   var id= $(this).attr('id');
  

    $.easyAjax({
            type: 'GET',
            url: "{{ url('downloadfile') }}",
            data: {
              id: project_nm
            },
            success: function (response) {
            }
        });
   }

   $('body').on('click', '.sa-params', function () {
        var id = $(this).data('file-id');
        var deleteView = $(this).data('pk');
        swal({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.confirmation.deleteFile')",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "@lang('messages.deleteConfirmation')",
            cancelButtonText: "@lang('messages.confirmNoArchive')",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {

                var url = "{{ route('admin.files.destroy',':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                            url: url,
                            data: {'_token': token, '_method': 'DELETE', 'view': deleteView},
                    success: function (response) {
                        console.log(response);
                        if (response.status == "success") {
                            $.unblockUI();
                            getprojectfle()
                            // if(deleteView == 'list') {
                            //     $('#files-list-panel ul.list-group').html(response.html);
                            // } else {
                            //     $('#thumbnail').empty();
                            //     $(response.html).hide().appendTo("#thumbnail").fadeIn(500);
                            // }
                        }
                    }
                });
            }
        });
    });
$( document ).ready(function() {
        
  
        $(document).on('change', "#client_id", function(e) {
        e.preventDefault();
       
        var clientid=$(this).val();
      
       
       
    });
});
</script>
@endpush

