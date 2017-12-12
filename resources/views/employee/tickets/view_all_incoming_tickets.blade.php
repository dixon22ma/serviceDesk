@extends('employee.layout.auth')

@section('content')


    <div class="row">

        <div class="col-md-9 col-md-offset-2" >
            
            <div class="col-md-3" style="height: 300px; background-color: ;">
                <ul class="list-group">
                    <li class="list-group-item"><a href="{{ url('/employee/create_ticket') }}"> Создать заявку </a></li>
                    <li class="list-group-item"><a href="">Входящие заявки</a></li>
                    <li class="list-group-item"><a href="">Исходящие заявки</a></li>
                    <li class="list-group-item"><a href="">Архив заявок</a></li>
                </ul>
            </div>

        <!-- Вывод пользователей -->
        <div class="col-md-9 ">
            <b>На этой странице ({{   $tickets->count()}} заявок)</b>
            <div class="">
                <table class="table table-striped">
                    <thead>
                         <tr>
                             <th>#</th>
                             <th>Тема</th>
                             <th>От кого?</th>
                             <th>Дата создания</th>
                             <th>Исполнитель</th>
                             <th>Статус</th>
                             <th>Действия</th>
                         </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                         <tr>
                             <td>{{ $ticket->id }}</td>
                             <td>{{ $ticket->subject }}</td>
                             <td>{{ $ticket->employee_init_id }}</td>
                             <td>{{ $ticket->created_at }}</td>
                             <td>
                                @if (Auth::user()->head_unit_id != NULL)
                                    <div class="row">
                                        <div class="col-md-9">
                                            <div class="input-group">
                                                <form class="form-horizontal" method="POST" action="{{url('/employee/appoint_executor') }}">
                                                    <select class="form-control" name="unit_to_id">
                                                        <option value="{{ $ticket->id_executor }}" >
                                                             ----
                                                        </option>
                                                        @forelse($employees as $employee)
                                                            <option value="{{ $employee -> id }}">{{ $employee->name }}</option>
                                                        @empty
                                                            <p>Отделы не найдены.</p>
                                                        @endforelse
                                                    </select>
                                                    <span class="input-group-btn"><button class="btn btn-sm btn-success">OK</button></span>
                                                </form> 
                                            </div>
                                        
                                        </div>
                                    </div>
                                @else 
                                    {{ $ticket->id_executor }}
                                @endif
                            </td>
                             <td>{{ $ticket->id_status }}</td>
                         </tr>
                        @empty
                            <p>Нет входящих заявок.</p>
                        @endforelse
                    </tbody>
                </table>
            </div>            
    

                <!--  <ul class="list-group">
                @forelse($tickets as $ticket)
                    <li class="list-group-item" style="margin-top: 20px;">

                        <span> {{ $ticket->subject }} </span>

                        <span class="pull-right clearfix">


                    <button class="btn btn-xs btn-primary">Назначить исполнителя</button>
                    <button class="btn btn-xs btn-warning">Заблокировать</button>

                        </span>
                    </li>
                @empty
                    <p>Нет входящих заявок.</p>
                @endforelse

            </ul>
            -->
        </div>

        </div>

        

    </div>








@endsection