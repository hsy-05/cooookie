{{-- 繼承 AdminLTE 的母版 --}}
@extends('adminlte::page')

{{-- 網頁標題 --}}
@section('title', $page_title)

{{-- 頁面內容標題 --}}
@section('content_header')
    <h1>{{ $page_title }}</h1>
@stop

{{-- 主內容區 --}}
@section('content')
    <div class="row">
        <div class="col-12">
            {{-- AdminLTE Card 元件：防呆、結構清楚 --}}
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        即時系統錯誤日誌 (Laravel Logs)
                    </h3>
                    <div class="card-tools">
                        {{-- 參數化按鈕：方便另開視窗查看全螢幕 --}}
                        <a href="{{ $log_url }}" target="_blank" class="btn btn-tool">
                            <i class="fas fa-expand"></i> 全螢幕檢視
                        </a>
                    </div>
                </div>

                {{-- Card Body: 移除 padding 讓 iframe 滿版 --}}
                <div class="card-body p-0">
                    {{--
                        使用 Iframe 嵌入套件介面
                        style="height: 70vh;" 這裡的內聯 style 是為了控制高度，
                        在專業開發中，高度通常由 JS 動態計算或固定參數設定。
                    --}}
                    <iframe
                        src="{{ $log_url }}"
                        style="width: 100%; height: 75vh; border: none;"
                        title="System Logs">
                    </iframe>
                </div>

                <div class="card-footer text-muted font-italic">
                    <small>* 此頁面僅限系統管理員 (Developer) 存取。紀錄包含敏感路徑資訊，請謹慎操作。</small>
                </div>
            </div>
        </div>
    </div>
@stop

{{-- JS 腳本區：目前不需要，留空備用 --}}
@section('js')
@stop
