@extends('layouts.app')

@section('page-title')
    <div class="row bg-title">
        <!-- .page title -->
        <div class="col-lg-6 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"><i class="{{ $pageIcon }}"></i> {{ __($pageTitle) }}</h4>
        </div>
        <!-- /.page title -->
        <!-- .breadcrumb -->
        <div class="col-lg-6 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="{{ route('admin.dashboard') }}">@lang('app.menu.home')</a></li>
                <li><a href="{{ route('admin.leads.index') }}">{{ __($pageTitle) }}</a></li>
                <li class="active">@lang('app.edit')</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/custom-select/custom-select.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/summernote/dist/summernote.css') }}">


@endpush

@section('content')

    <div class="row">
        <div class="col-xs-12">

            <div class="panel panel-inverse">
                <div class="panel-heading"> @lang('modules.lead.updateTitle')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        {!! Form::open(['id'=>'updateLead','class'=>'ajax-form','method'=>'PUT']) !!}
                        <div class="form-body">
                            <h3 class="box-title required">@lang('modules.lead.companyDetails')</h3>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">@lang('modules.lead.companyName')</label>
                                        <input type="text" id="company_name" name="company_name" class="form-control"  value="{{ $lead->company_name ?? '' }}">
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">@lang('modules.lead.website')</label>
                                        <input type="text" id="website" name="website" class="form-control" value="{{ $lead->website ?? '' }}" >
                                    </div>
                                </div>
                                <!--/span-->
                            </div>
                            <!--/row-->
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="form-group">
                                        <label class="control-label">@lang('app.address')</label>
                                        <textarea name="address"  id="address"  rows="5" class="form-control">{{ $lead->address ?? '' }}</textarea>
                                    </div>
                                </div>
                                <!--/span-->

                            </div>
                            <!--/row-->
                            <div class="row">
                                <div class="col-md-3">
                                        <div class="form-group">
                                            <label>@lang('modules.lead.mobile')</label>
                                            <input type="tel" name="mobile" id="mobile" value="{{ $lead->mobile }}" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3 ">
                                        <div class="form-group">
                                            <label>@lang('modules.clients.officePhoneNumber')</label>
                                            <input type="text" name="office_phone" id="office_phone" value="{{ $lead->office_phone }}"  class="form-control">
                                        </div>
                                    </div>
                                <div class="col-md-3 ">
                                        <div class="form-group">
                                            <label>@lang('modules.stripeCustomerAddress.city')</label>
                                            <input type="text" name="city" id="city"  value="{{ $lead->city }}" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3 ">
                                        <div class="form-group">
                                            <label>@lang('modules.stripeCustomerAddress.state')</label>
                                            <input type="text" name="state" id="state"  value="{{ $lead->state }}" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                <div class="col-md-3 ">
                                        <div class="form-group">
                                            <label>@lang('modules.stripeCustomerAddress.country')</label>
                                            <input type="text" name="country" id="country" value="{{ $lead->country }}" class="form-control">
                                        </div>
                                    </div>
                                <div class="col-md-3 ">
                                        <div class="form-group">
                                            <label>@lang('modules.stripeCustomerAddress.postalCode')</label>
                                            <input type="text" name="postal_code" id="postalCode" value="{{ $lead->postal_code }}"class="form-control">
                                        </div>
                                    </div>
                                </div>

                            <h3 class="box-title m-t-40">@lang('modules.lead.leadDetails')</h3>
                            <hr>
                            <div class="row">
                                <div class="col-md-6 ">
                                    <div class="form-group">
                                        <label for="">@lang('modules.tickets.chooseAgents') <a href="javascript:;"
                                                                                              id="addLeadAgent"
                                                                                              class="btn btn-sm btn-outline btn-success"><i
                                                        class="fa fa-plus"></i> @lang('app.add') @lang('app.leadAgent')</a></label>
                                        <select class="select2 form-control" data-placeholder="@lang('modules.tickets.chooseAgents')" id="agent_id" name="agent_id">
                                            <option value="">@lang('modules.tickets.chooseAgents')</option>
                                            @foreach($leadAgents as $emp)
                                                <option  @if($emp->id == $lead->agent_id) selected @endif  value="{{ $emp->id }}">{{ ucwords($emp->user->name) }} @if($emp->user->id == $user->id)
                                                        (YOU) @endif</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                </div>
                                <div class="col-md-6 ">
                                             <div class="form-group" style="margin-top: 7px;">
                                              <label >@lang('modules.lead.leadCategory')
                                                <a href="javascript:;" id="addLeadCategory" class="btn btn-xs btn-success btn-outline"><i class="fa fa-plus"></i></a>
                                                 </label>
                                            <select class="select2 form-control" name="category_id" id="category_id"
                                                data-style="form-control">
                                                @forelse($categories as $category)
                                            <option value="{{ $category->id }}"
                                                    @if($lead->category_id == $category->id)
                                                    selected
                                                    @endif
                                            >{{ ucwords($category->category_name) }}</option>
                                        @empty
                                            <option value="">@lang('messages.noCategoryAdded')</option>
                                        @endforelse
                                             </select>
                                         </div>
                            </div>
                                <!--/span-->
                            </div>
                            <div class="row">
                                <div class="col-md-4 ">
                                    <div class="form-group">
                                        <label required>@lang('modules.lead.clientName')</label>
                                        <input type="text" name="client_name" id="client_name" class="form-control" value="{{ $lead->client_name }}">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label required>@lang('modules.lead.clientEmail')</label>
                                        <input type="email" name="client_email" id="client_email" class="form-control" value="{{ $lead->client_email }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>@lang('modules.lead.mobile')</label>
                                        <input type="tel" name="mobile" id="mobile" value="{{ $lead->mobile }}" class="form-control">
                                    </div>
                                </div>                
                            
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>@lang('app.lead') @lang('app.value')</label>
                                        <input type="number" min="0"  name="value" value="{{ $lead->value }}"  class="form-control">
                                    </div>
                                </div>
                          
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>@lang('app.next_follow_up')</label>
                                        <select name="next_follow_up" id="next_follow_up" class="form-control">
                                            <option @if($lead->next_follow_up == 'yes') selected
                                                    @endif value="yes"> @lang('app.yes')</option>
                                           <option @if($lead->next_follow_up == 'no') selected
                                                    @endif value="no"> @lang('app.no')</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>@lang('app.status')</label>
                                        <select name="status" id="status" class="form-control">
                                            @forelse($status as $sts)
                                                <option @if($lead->status_id == $sts->id) selected
                                                        @endif value="{{ $sts->id }}"> {{ ucfirst($sts->type) }}</option>
                                            @empty

                                            @endforelse
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>@lang('app.source')</label>
                                        <select name="source" id="source" class="form-control">
                                            @forelse($sources as $source)
                                                <option @if($lead->source_id == $source->id) selected
                                                        @endif value="{{ $source->id }}"> {{ ucfirst($source->type) }}</option>
                                            @empty

                                            @endforelse
                                        </select>
                                    </div>
                                </div>
                                <!--/span-->
                            </div>

                            <!--/row-->

                            <div class="row">
                                @foreach($fields as $field)
                                    <div class="col-md-4">
                                        <label>{{ ucfirst($field->label) }}</label>
                                        <div class="form-group">
                                            @if( $field->type == 'text')
                                                <input type="text" name="custom_fields_data[{{$field->name.'_'.$field->id}}]" class="form-control" placeholder="{{$field->label}}" value="{{$lead->custom_fields_data['field_'.$field->id] ?? ''}}">
                                            @elseif($field->type == 'password')
                                                <input type="password" name="custom_fields_data[{{$field->name.'_'.$field->id}}]" class="form-control" placeholder="{{$field->label}}" value="{{$lead->custom_fields_data['field_'.$field->id] ?? ''}}">
                                            @elseif($field->type == 'number')
                                                <input type="number" name="custom_fields_data[{{$field->name.'_'.$field->id}}]" class="form-control" placeholder="{{$field->label}}" value="{{$lead->custom_fields_data['field_'.$field->id] ?? ''}}">

                                            @elseif($field->type == 'textarea')
                                                <textarea name="custom_fields_data[{{$field->name.'_'.$field->id}}]" class="form-control" id="{{$field->name}}" cols="3">{{$lead->custom_fields_data['field_'.$field->id] ?? ''}}</textarea>

                                            @elseif($field->type == 'radio')
                                                <div class="radio-list">
                                                    @foreach($field->values as $key=>$value)
                                                        <label class="radio-inline @if($key == 0) p-0 @endif">
                                                            <div class="radio radio-info">
                                                                <input type="radio" name="custom_fields_data[{{$field->name.'_'.$field->id}}]" id="optionsRadios{{$key.$field->id}}" value="{{$value}}" @if(isset($lead) && $lead->custom_fields_data['field_'.$field->id] == $value) checked @elseif($key==0) checked @endif>>
                                                                <label for="optionsRadios{{$key.$field->id}}">{{$value}}</label>
                                                            </div>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @elseif($field->type == 'select')
                                                {!! Form::select('custom_fields_data['.$field->name.'_'.$field->id.']',
                                                        $field->values,
                                                         isset($lead)?$lead->custom_fields_data['field_'.$field->id]:'',['class' => 'form-control gender'])
                                                 !!}

                                            @elseif($field->type == 'checkbox')
                                                <div class="mt-checkbox-inline">
                                                    @foreach($field->values as $key => $value)
                                                        <label class="mt-checkbox mt-checkbox-outline">
                                                            <input name="custom_fields_data[{{$field->name.'_'.$field->id}}][]" type="checkbox" value="{{$key}}"> {{$value}}
                                                            <span></span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @elseif($field->type == 'date')
                                            <input type="text" class="form-control date-picker" size="16" name="custom_fields_data[{{$field->name.'_'.$field->id}}]"
                                            value="{{ ($lead->custom_fields_data['field_'.$field->id] != '') ? \Carbon\Carbon::parse($lead->custom_fields_data['field_'.$field->id])->format($global->date_format) : \Carbon\Carbon::now()->format($global->date_format)}}">
                                            @endif
                                            <div class="form-control-focus"> </div>
                                            <span class="help-block"></span>

                                        </div>
                                    </div>
                                @endforeach

                            </div>

                            <div class="row">
                                <div class="col-xs-12">
                                    <label>@lang('app.note')</label>
                                    <div class="form-group">
                                        <textarea name="note" id="note" class="form-control summernote" rows="5">{{ $lead->note ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="form-actions">
                            <button type="submit" id="save-form" class="btn btn-success"> <i class="fa fa-check"></i> @lang('app.update')</button>
                            <a href="{{ route('admin.leads.index') }}" class="btn btn-default">@lang('app.back')</a>
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
<script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/custom-select/custom-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/summernote/dist/summernote.min.js') }}"></script>

<script type="text/javascript">

    $(".select2").select2({
        formatNoMatches: function () {
            return "{{ __('messages.noRecordFound') }}";
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
    $(".date-picker").datepicker({
        todayHighlight: true,
        autoclose: true,
        weekStart:'{{ $global->week_start }}',
    });

    $('#updateLead').on('click', '#addLeadAgent', function () {
        var url = '{{ route('admin.lead-agent-settings.create')}}';
        $('#modelHeading').html('Manage Lead Agent');
        $.ajaxModal('#projectCategoryModal', url);
    })

    $('#save-form').click(function () {
        $.easyAjax({
            url: '{{route('admin.leads.update', [$lead->id])}}',
            container: '#updateLead',
            type: "POST",
            redirect: true,
            data: $('#updateLead').serialize()
        })
    });
    $('#addLeadCategory').click(function () {
        var url = '{{ route('admin.leadCategory.create-cat')}}';
        $('#modelHeading').html('...');
        $.ajaxModal('#projectCategoryModal', url);
    })
</script>
@endpush
