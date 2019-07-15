@extends('layouts.app')

@section('content')
    <h3 class="page-title">@lang('quickadmin.email.title')</h3>

    <div class="panel panel-default">
        <div class="panel-heading">
            @lang('quickadmin.qa_view')
        </div>

        <div class="panel-body table-responsive">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>@lang('quickadmin.email.fields.from')</th>
                            <td field-key='from'>{{ $email->from }}</td>
                        </tr>
                        <tr>
                            <th>@lang('quickadmin.email.fields.to')</th>
                            <td field-key='to'>{{ $email->to }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <p>&nbsp;</p>

            <a href="{{ route('admin.emails.index') }}" class="btn btn-default">@lang('quickadmin.qa_back_to_list')</a>
        </div>
    </div>
@stop


