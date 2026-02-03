{{-- 遞迴顯示用 --}}
@php
    $paddingLeft = $level * 30;
    // 判斷是否有自訂權限 (用於顯示小標記)
    $hasCustomPerms = !empty($user->permissions);
@endphp

<tr>
    <td>
        <div style="padding-left: {{ $paddingLeft }}px; display:flex; align-items:center;">
            @if($level > 0)
                <i class="fas fa-level-up-alt fa-rotate-90 mr-2 text-muted"></i>
            @endif

            {{-- Avatar --}}
            @if($user->avatar_url)
                <img src="{{ asset('storage/'.$user->avatar_url) }}"
                     class="img-circle mr-2"
                     style="object-fit:cover; border:1px solid #ddd;"
                     width="30" height="30">
            @else
                <i class="fas fa-user-circle fa-lg mr-2 text-secondary"></i>
            @endif

            {{-- 姓名 --}}
            <span class="{{ $user->isDeveloper() ? 'text-danger font-weight-bold' : '' }}">
                {{ $user->name }}
            </span>
        </div>
    </td>

    <td>{{ $user->email }}</td>

    <td class="hidden-md">
        <span class="badge badge-{{ $user->isDeveloper() ? 'danger' : 'info' }}">
            {{ $user->role->name ?? '無角色' }}
        </span>
        {{-- 若有自訂權限，顯示額外標籤 --}}
        @if($hasCustomPerms && !$user->isDeveloper())
            <span class="badge badge-warning" title="此人擁有額外自訂權限">+自訂</span>
        @endif
    </td>

    <td class="hidden-sm">
        @if($user->is_active)
            <span class="badge badge-success">啟用</span>
        @else
            <span class="badge badge-secondary">停用</span>
        @endif
    </td>

    {{-- 操作欄位 --}}
    <td class="text-center">
        <div class="table-actions-container">
            {{-- 編輯按鈕 --}}
            @can('users.create')
                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-warning" title="編輯">
                    <i class="fas fa-edit"></i>
                </a>
            @endcan

            {{-- 刪除按鈕 --}}
            {{-- 防呆：不能刪除自己，不能刪除有下屬的人 --}}
            @if (auth()->id() !== $user->id)
                @can('users.delete')
                    @if ($user->children->isEmpty())
                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                              style="display:inline-block;" id="deleteForm{{ $user->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-danger js-delete-btn"
                                    data-id="{{ $user->id }}"
                                    title="刪除">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    @else
                        <button class="btn btn-sm btn-secondary" disabled title="此管理者尚有下屬，不可刪除">
                            <i class="fas fa-trash"></i>
                        </button>
                    @endif
                @endcan
            @endif
        </div>
    </td>
</tr>

{{-- 遞迴子層 --}}
@if($user->childrenRecursive && $user->childrenRecursive->isNotEmpty())
    @foreach($user->childrenRecursive as $child)
        @include('admin.users._user_row', [
            'user'  => $child,
            'level' => $level + 1
        ])
    @endforeach
@endif
