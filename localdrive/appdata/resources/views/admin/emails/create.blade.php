@extends('layouts.app')

@section('content')
    <h3 class="page-title">@lang('quickadmin.email.title')</h3>
    {!! Form::open(['method' => 'POST', 'route' => ['admin.emails.store']]) !!}

    <div class="panel panel-default">
        <div class="panel-heading">
            @lang('quickadmin.qa_create')
        </div>
        
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-12 form-group">
                    {!! Form::label('from', trans('quickadmin.email.fields.from').'*', ['class' => 'control-label']) !!}
                    {!! Form::email('from', old('from'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                    <p class="help-block"></p>
                    @if($errors->has('from'))
                        <p class="help-block">
                            {{ $errors->first('from') }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 form-group">
                    {!! Form::label('to', trans('quickadmin.email.fields.to').'*', ['class' => 'control-label']) !!}
                    {!! Form::email('to', old('to'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                    <p class="help-block"></p>
                    @if($errors->has('to'))
                        <p class="help-block">
                            {{ $errors->first('to') }}
                        </p>
                    @endif
                </div>
            </div>
            
        </div>
    </div>

    {!! Form::submit(trans('quickadmin.qa_save'), ['class' => 'btn btn-danger']) !!}
    {!! Form::close() !!}
@stop

