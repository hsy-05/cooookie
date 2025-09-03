@extends('adminlte::page')

@section('title', '廣告管理')

@section('content_header')
    <h1>廣告列表</h1>
@stop

@section('content')
    <a href="{{ route('admin.advert.create') }}" class="btn btn-success mb-3">新增廣告</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>分類</th>
                <th>電腦圖</th>
                {{-- <th>手機圖</th> --}}
                <th>連結</th>
                <th>排序</th>
                <th>狀態</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach($adverts as $advert)
                <tr>
                    <td>{{ $advert->adv_id }}</td>
                    <td>{{ $advert->category->cat_code }}</td>
                    <td><img src="{{  $UPLOAD_PATH . '/' . $advert->adv_img_url }}" width="120"></td>
                    {{-- <td><img src="{{ $advert->adv_img_m_url }}" width="80"></td> --}}
                    <td>{{ $advert->adv_link_url }}</td>
                    <td>{{ $advert->display_order }}</td>
                    <td>{{ $advert->is_visible ? '顯示' : '隱藏' }}</td>
                    <td>
                        <a href="{{ route('admin.advert.edit', $advert->adv_id) }}" class="btn btn-primary btn-sm">編輯</a>
                        <form action="{{ route('admin.advert.destroy', $advert->adv_id) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">刪除</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $adverts->links() }}
@stop
