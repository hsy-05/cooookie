@extends('adminlte::page')

@section('title', '操作紀錄')

@section('content_header')
    <h1>操作紀錄</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.logs.index') }}" method="GET" class="mb-4">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label>關鍵字 (內容/IP/操作者)</label>
                        <input type="text" name="search" class="form-control"
                               value="{{ request('search') }}" placeholder="輸入關鍵字...">
                    </div>

                    <div class="col-md-4">
                        <label>日期區間</label>
                        <div class="input-group">
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                            <div class="input-group-append"><span class="input-group-text">~</span></div>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                    </div>

                    <div class="col-md-5">
                        <button class="btn btn-primary" type="submit">查詢</button>
                        <a href="{{ route('admin.logs.index') }}" class="btn btn-secondary">重置</a>

                        <div class="btn-group ml-2">
                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                                快速篩選
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('admin.logs.index', ['quick_date' => 'week']) }}">一周內</a>
                                <a class="dropdown-item" href="{{ route('admin.logs.index', ['quick_date' => 'month']) }}">一個月內</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>操作者</th>
                        <th>內容</th>
                        <th>IP</th>
                        <th>時間</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->user->name }}</td>
                            <td>
                                <span class="badge badge-{{ $log->action == '刪除' ? 'danger' : ($log->action == '新增' ? 'success' : 'info') }}">
                                    {{ $log->action }}
                                </span>
                                {{ str_replace($log->action, '', $log->log_info) }}
                            </td>
                            <td>{{ $log->ip_address }}</td>
                            <td>{{ $log->created_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">查無紀錄</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $logs->appends(request()->all())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@stop
