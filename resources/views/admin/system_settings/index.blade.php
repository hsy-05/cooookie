@extends('adminlte::page')
@section('title', $pageTitle)

@include('components.admin.page_content_header')

@section('content')
    <x-admin.page-message>
        <form name="the-form" action="{{ route('admin.system_settings.update_all') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="col-md-12">
                <x-admin.card-tabs>
                    <x-slot:tabs>
                        @foreach ($tabs as $tab)
                            <li class="nav-item">
                                <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                                   data-toggle="pill" href="#tab-{{ $tab->id }}">
                                   {{ $tab->title }}
                                </a>
                            </li>
                        @endforeach
                    </x-slot:tabs>

                    <x-slot:content>
                        @foreach ($tabs as $tab)
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ $tab->id }}">
                                <div class="p-3">
                                    @foreach ($tab->children as $item)
                                        @if (auth()->user()->isDeveloper())
                                            <div class="form-group border-bottom pb-3">
                                                <label>{{ $item->title }} <small>({{ $item->setting_key }})</small></label>

                                                {{-- 根據類型渲染 input --}}
                                                <input type="{{ $item->type == 'number' ? 'number' : 'text' }}"
                                                       name="settings[{{ $item->setting_key }}]"
                                                       value="{{ $item->setting_value }}"
                                                       class="form-control">

                                                @if(!$item->is_visible)
                                                    <small class="text-danger">工程師專用</small>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </x-slot:content>

                    <x-slot:footer>
                        <button type="submit" class="btn btn-success">儲存所有設定</button>
                    </x-slot:footer>
                </x-admin.card-tabs>
            </div>
        </form>
    </x-admin.page-message>
@stop
