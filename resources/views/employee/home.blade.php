@extends('employee.layout.auth')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Входящие заявки</div>
                
                <div class="panel-body">
                    You are logged in as {{ Auth::user()->name}}!
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
