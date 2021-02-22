@extends('layouts.app')
@section('page-title')
    <div class="row bg-title">
        <!-- .page title -->
        <div class="col-lg-6 col-sm-8 col-md-8 col-xs-12">
            <h4 class="page-title"><i class="{{ $pageIcon }}"></i> @lang($pageTitle) - {{ ucwords($expense->item_name) }}</h4>
        </div>
        <!-- /.page title -->
        <!-- .breadcrumb -->
        <div class="col-lg-6 col-sm-8 col-md-8 col-xs-12 text-right">
            <ol class="breadcrumb">
                <li><a href="{{ route('admin.dashboard') }}">@lang('app.menu.home')</a></li>
                <li><a href="{{ route('admin.expenses-recurring.index') }}">@lang($pageTitle)</a></li>
                <li class="active">@lang('modules.projects.files')</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection
@push('head-script')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.13/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.1.1/css/responsive.bootstrap.min.css">
<link rel="stylesheet" href="//cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="//cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css">
<style>
    #expenses-recurring-table_wrapper .dt-buttons{
        display: none !important;
    }
</style>
@endpush

@section('content')

    <div class="row">
        <div class="col-md-12">

            <section>
                <div class="sttabs tabs-style-line">
                    <div class="white-box">
                        <div class="row">
                            <div class="col-md-11 p-r-0">
                                <nav>
                                    <ul >
                                        <li><a href="{{ route('admin.expenses-recurring.show', $expense->id) }}"><span>@lang('app.menu.expensesRecurring') @lang('app.info')</span></a>
                                        </li>
                                        <li class="tab-current"><a href="{{ route('admin.expenses-recurring.recurring-expenses', $expense->id) }}"><span>@lang('app.menu.expenses')</span></a></li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <div class="white-box">
                        <div class="row">
                            <div class="table-responsive">
                                {!! $dataTable->table(['class' => 'table table-bordered table-hover toggle-circle default footable-loaded footable']) !!}
                            </div>
                        </div>
                    </div>
                    <!-- /content -->
                </div>
                <!-- /tabs -->
            </section>
        </div>
    </div>
@endsection

@push('footer-script')
<script src="{{ asset('plugins/bower_components/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('js/datatables/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('js/datatables/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('js/datatables/responsive.bootstrap.min.js') }}"></script>
<script src="{{ asset('js/datatables/buttons.server-side.js') }}"></script>
<script src="https://cdn.datatables.net/buttons/1.0.3/js/dataTables.buttons.min.js"></script>
<script src="{{ asset('js/datatables/buttons.server-side.js') }}"></script>
{!! $dataTable->scripts() !!}
<script>

    $(function() {
        $('#recurring-expenses-table').on('preXhr.dt', function (e, settings, data) {
            data['recurringID'] = {{ $expense->id }};
        });


        $('body').on('click', '.delete-expense', function(){
            var id = $(this).data('expense-id');
            swal({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.confirmation.recoverExpense')",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "@lang('messages.deleteConfirmation')",
                cancelButtonText: "@lang('messages.confirmNoArchive')",
                closeOnConfirm: true,
                closeOnCancel: true
            }, function(isConfirm){
                if (isConfirm) {

                    var url = "{{ route('admin.expenses.destroy',':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {'_token': token, '_method': 'DELETE'},
                        success: function (response) {
                            if (response.status == "success") {
                                $.unblockUI();
                                window.LaravelDataTables["recurring-expenses-table"].draw();
                            }
                        }
                    });
                }
            });
        });
    });
    $(document).on('click', '.view-expense', function () {
        var id = $(this).data('expense-id');
        var url = "{{ route('admin.expenses.show',':id') }}";
        url = url.replace(':id', id);

        $('#modelHeading').html('...');
        $.ajaxModal('#leave-details', url);
    });
    function loadTable(){
        window.LaravelDataTables["recurring-expenses-table"].draw();
    }

    //    loadTable();

</script>
@endpush