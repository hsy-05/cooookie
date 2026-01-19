@extends('adminlte::page')

@section('title', '管理中心')

@section('content_header')
    <h1>管理中心</h1>
@stop

@section('content')
    <div class="row">
        @foreach ($systemInfoChunks as $chunk)
            <div class="col-12 col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-body p-0">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 35%">項目</th>
                                    <th>資訊內容</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($chunk as $item)
                                    <tr>
                                        <td class="font-weight-bold text-muted">
                                            {{ $item['label'] }}
                                        </td>
                                        <td>
                                            {{-- 依 type 決定顯示方式 --}}
                                            @if ($item['type'] === 'badge')
                                                @php
                                                    /**
                                                     * badge 顏色集中管理
                                                     * 之後要改顏色，只改這裡
                                                     */
                                                    $badgeClass = 'secondary';

                                                    if ($item['label'] === '執行環境 (Env)') {
                                                        $badgeClass =
                                                            $item['status'] === 'production' ? 'danger' : 'success';
                                                    }

                                                    if ($item['label'] === '除錯模式 (Debug)') {
                                                        $badgeClass = $item['status'] ? 'warning' : 'success';
                                                    }
                                                @endphp

                                                <span class="badge badge-{{ $badgeClass }}">
                                                    {{ $item['value'] }}
                                                </span>
                                            @else
                                                {{ $item['value'] }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="text-muted text-sm mt-2">
        <i class="fas fa-info-circle"></i>
        伺服器時間：{{ now()->format('Y-m-d H:i:s') }}
    </div>
@stop

@section('css')
@stop

@section('js')
    <script>
        console.log('Dashboard loaded!');
    </script>
@stop
