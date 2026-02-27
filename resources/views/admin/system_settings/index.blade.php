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
                                <a class="nav-link {{ $loop->first ? 'active' : '' }}" data-toggle="pill"
                                    href="#tab-{{ $tab->id }}">
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
                                        <div class="form-group border-bottom pb-3">
                                            <label>{{ $item->title }} <small
                                                    class="text-secondary">({{ $item->setting_key }})</small></label>

                                            {{-- 邏輯分離：根據 type 決定顯示方式 --}}
                                            @switch($item->type)
                                                @case('number')
                                                @case('text')
                                                    <input type="{{ $item->type }}" name="settings[{{ $item->setting_key }}]"
                                                        value="{{ $item->setting_value }}" class="form-control">
                                                @break

                                                @case('radio')
                                                    <div class="mt-2">
                                                        {{-- 解析 range 欄位: "front:前台顯示,bg:背景執行" --}}
                                                        @foreach (explode(',', $item->range) as $option)
                                                            @php
                                                                [$val, $label] = explode(':', $option);
                                                            @endphp
                                                            <div class="custom-control custom-radio custom-control-inline">
                                                                <input type="radio" id="{{ $item->setting_key . $val }}"
                                                                    name="settings[{{ $item->setting_key }}]"
                                                                    value="{{ $val }}" class="custom-control-input"
                                                                    {{ $item->setting_value == $val ? 'checked' : '' }}>
                                                                <label class="custom-control-label"
                                                                    for="{{ $item->setting_key . $val }}">{{ $label }}</label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @break

                                                @case('image')
                                                    <input type="file" name="settings[{{ $item->setting_key }}]"
                                                        class="form-control-file">
                                                    @if ($item->setting_value)
                                                        <div class="mt-2">
                                                            <img src="{{ asset('storage/' . $item->setting_value) }}"
                                                                width="100" class="img-thumbnail">
                                                        </div>
                                                    @endif
                                                @break

                                                @case('textarea')
                                                    <textarea name="settings[{{ $item->setting_key }}]" class="form-control" rows="3">{{ $item->setting_value }}</textarea>
                                                @break
                                            @endswitch

                                            @if (!$item->is_visible)
                                                <small class="text-danger mt-1 d-block"><i class="fas fa-tools"></i>
                                                    工程師專用參數</small>
                                            @endif
                                        </div>
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
