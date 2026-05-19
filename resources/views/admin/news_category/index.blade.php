@extends('adminlte::page')
@section('title', $pageTitle)

{{-- 麵包屑與標題組件 --}}
@include('components.admin.page_content_header')

@section('content')
    <x-admin.page-message>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="{{ route('admin.news_category.create') }}" class="btn btn-primary ml-auto">
                        <i class="fas fa-plus-square"></i> 新增分類
                    </a>
                </div>

                <table class="table table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-left">名稱</th> {{-- 樹狀結構名稱必須靠左 --}}
                            <th class="text-center px-width-150">是否顯示</th>
                            <th class="text-center px-width-100">排序</th>
                            <th class="text-center px-width-150">更新時間</th>
                            <th class="text-center px-width-200">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- 開始遞迴渲染，傳入初始層級 level = 0 --}}
                        @forelse ($catItems as $cat)
                            @include('admin.news_category.item_row', ['cat' => $cat, 'level' => 1])
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">目前沒有任何記錄。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-admin.page-message>
@stop
