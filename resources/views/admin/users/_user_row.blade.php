{{-- 這是遞迴用的 partial --}}
@php
    // 計算縮排層級，每一層多 30px
    $paddingLeft = $level * 30;
@endphp

<tr>
    <td>
        <div style="padding-left: {{ $paddingLeft }}px; display: flex; align-items: center;">
            @if ($level > 0)
                <i class="fas fa-level-up-alt fa-rotate-90 mr-2 text-muted"></i>
            @endif

            @if ($user->avatar_url)
                <img src="{{ asset('storage/' . $user->avatar_url) }}" class="img-circle mr-2" width="30" height="30"
                    alt="Avatar of {{ $user->name }}">
            @else
                <i class="fas fa-user-circle fa-lg mr-2 text-secondary"></i>
            @endif

            <span class="{{ $user->role->is_system ? 'text-primary font-weight-bold' : '' }}">
                {{ $user->name }}
            </span>
        </div>
    </td>
    <td>{{ $user->email }}</td>
    <td>
        <span class="badge badge-{{ $user->role->is_system ? 'danger' : 'info' }}">
            {{ $user->role->name ?? '無角色' }}
        </span>
    </td>
    <td>
        @if ($user->is_active)
            <span class="badge badge-success">啟用</span>
        @else
            <span class="badge badge-secondary">停用</span>
        @endif
    </td>
    <td class="text-left">
        <div class="table-actions-container">
            @can('users.create')
                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-warning" title="編輯">
                    <i class="fas fa-edit"></i>
                </a>
            @endcan

            {{-- 刪除按鈕邏輯 --}}
            @if (auth()->id() !== $user->id)
                @can('users.delete')
                    @if ($user->children->isEmpty())
                        <button type="button" class="btn btn-sm btn-danger"
                            onclick="confirmDelete('{{ route('admin.users.destroy', $user->id) }}')" title="刪除">
                            <i class="fas fa-trash"></i>
                        </button>
                    @else
                        <button class="btn btn-sm btn-secondary" disabled title="尚有下屬，不可刪除">
                            <i class="fas fa-trash"></i>
                        </button>
                    @endif
                @endcan
            @endif
        </div>
    </td>
</tr>

{{-- 遞迴呼叫：如果有子層，且當前使用者可以看到 (已在 Controller 過濾過) --}}
@if ($user->childrenRecursive && $user->childrenRecursive->isNotEmpty())
    @foreach ($user->childrenRecursive as $child)
        @include('admin.users._user_row', ['user' => $child, 'level' => $level + 1])
    @endforeach
@endif
