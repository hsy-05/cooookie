<?php

return [
    // 模組定義
    'news' => [
        'label' => '消息管理',
        'actions' => [
            // key => label
            'view'   => '瀏覽列表',
            'create' => '新增/編輯', // 包含 update
            'delete' => '刪除',
        ],
        // 定義依賴：勾選 key，必須自動勾選 value
        'dependencies' => [
            'create' => ['view'],
            'delete' => ['view'],
        ],
    ],
    'roles' => [
        'label' => '角色管理',
        'actions' => [
            'view'   => '瀏覽列表',
            'create' => '新增/編輯',
            'delete' => '刪除',
        ],
        'dependencies' => [
            'create' => ['view'],
            'delete' => ['view'],
        ],
    ],
    'admins' => [
        'label' => '網站管理員',
        'actions' => [
            'view'   => '瀏覽列表',
            'create' => '新增/編輯',
            'delete' => '刪除',
        ],
        'dependencies' => [
            'create' => ['view'],
            'delete' => ['view'],
        ],
    ],
];
