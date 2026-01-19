{{-- 這是遞迴用的 partial --}}
@php
    // 計算縮排層級，每一層多 30px
    $paddingLeft = $level * 30;
@endphp

<tr>
    <td>
        <div style="padding-left: {{ $paddingLeft }}px; display: flex; align-items: center;">
            @if($level > 0) <i class="fas fa-level-up-alt fa-rotate-90 mr-2 text-muted"></i> @endif

            @if($user->avatar)
                <img src="{{ asset('storage/'.$user->avatar) }}" class="img-circle mr-2" width="30">
            @else
                <i class="fas fa-user-circle fa-lg mr-2 text-secondary"></i>
            @endif

            <span class="{{ $user->role->is_system ? 'text-primary font-weight-bold' : '' }}">
                {{ $user->name }}
            </span>
        </div>
    </td>
    <td>{{ $user->email }}</td>
    <td>{{ $user->role->name ?? '--' }}</td>
    <td>
        @if($user->is_active)
            <span class="badge badge-success">啟用</span>
        @else
            <span class="badge badge-secondary">停用</span>
        @endif
    </td>
    <td>
        @can('admins.create')
            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-warning">編輯</a>
        @endcan
        {{-- 禁止刪除自己 --}}
        @if(auth()->id() !== $user->id && $user->children->isEmpty())
             {{-- 簡單防呆：有下屬也不能刪除 --}}
            @can('admins.delete')
                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display:inline;">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" onclick="return confirm('刪除?')">刪除</button>
                </form>
            @endcan
        @endif
    </td>
</tr>

{{-- 遞迴呼叫：如果有子層，且當前使用者可以看到 (已在 Controller 過濾過) --}}
@if($user->childrenRecursive && $user->childrenRecursive->isNotEmpty())
    @foreach($user->childrenRecursive as $child)
        @include('admin.users._user_row', ['user' => $child, 'level' => $level + 1])
    @endforeach
@endif
