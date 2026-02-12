{{--
    組件說明：管理員列表的單一列 (支援遞迴)
    傳入參數：
    - $admin: 管理員資料模型
    - $level: 目前的層級深度 (0, 1, 2...)
--}}

<tr class="admin-row">
    <td>
        {{-- 使用 CSS Class 控制縮排，不再用內嵌 style --}}
        <div class="admin-name-wrapper level-indent-{{ $level }}">

            {{-- 若為子層，顯示層級圖示 --}}
            @if($level > 0)
                <i class="fas fa-level-up-alt fa-rotate-90 mr-2 text-muted"></i>
            @endif

            {{-- 管理員大頭照：若無則顯示預設圖示 --}}
            <div class="avatar-container mr-2">
                <img src="{{ $admin->avatar_url ? asset('storage/'.$admin->avatar_url) : asset('images/admin/default-avatar.png') }}" alt="{{ $admin->name }}">
            </div>

            {{-- 姓名顯示：如果是開發者身分則加粗強顯 (Class 由 Model 判斷較佳) --}}
            <span class="admin-name {{ $admin->isDeveloper() ? 'is-developer' : '' }}">
                {{ $admin->name }}
            </span>
        </div>
    </td>

    {{-- 電子郵件 --}}
    <td>{{ $admin->email }}</td>

    {{-- 角色與權限 --}}
    <td class="hidden-md">
        <span class="badge {{ $admin->isDeveloper() ? 'badge-danger' : 'info' }}">
            {{ $admin->role->name ?? '無角色' }}
        </span>

        {{-- 防呆判斷：只有非開發者且有自訂權限才顯示標記 (邏輯清晰化) --}}
        @if(!empty($admin->permissions) && !$admin->isDeveloper())
            <span class="badge badge-warning" title="此人擁有額外自訂權限">+自訂</span>
        @endif
    </td>

    {{-- 狀態 --}}
    <td class="hidden-sm">
        @if($admin->is_active)
            <span class="badge badge-success">啟用</span>
        @else
            <span class="badge badge-secondary">停用</span>
        @endif
    </td>

    {{-- 操作按鈕區 --}}
    <td class="text-center">
        <div class="table-actions-container">

            {{-- 編輯按鈕：由權限控制顯示 --}}
            @can('admins.create')
                <a href="{{ route('admin.admins.edit', $admin->id) }}" class="btn btn-sm btn-warning" title="編輯">
                    <i class="fas fa-edit"></i>
                </a>
            @endcan

            {{-- 刪除按鈕 (防呆：不能刪除自己 & 不能刪除有下屬的人) --}}
            @if (auth()->id() !== $admin->id)
                @can('admins.delete')
                    @if ($admin->children->isEmpty())
                        <form action="{{ route('admin.admins.destroy', $admin->id) }}"
                              method="POST"
                              id="deleteForm{{ $admin->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-danger js-delete-btn"
                                 data-id="{{ $admin->id }}" title="刪除">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>

                    @else
                        {{-- 防呆：有下屬時按鈕失效並顯示提示 --}}
                        <button class="btn btn-sm btn-secondary" disabled title="此管理者尚有下屬，不可刪除">
                            <i class="fas fa-trash"></i>
                        </button>
                    @endif
                @endcan
            @endif
        </div>
    </td>
</tr>

{{-- 遞迴子層：如果還有下屬管理員，繼續載入本檔案 --}}
@if($admin->childrenRecursive && $admin->childrenRecursive->isNotEmpty())
    @foreach($admin->childrenRecursive as $child)
        @include('admin.admins.item_row', [
            'admin' => $child,
            'level' => $level + 1
        ])
    @endforeach
@endif
