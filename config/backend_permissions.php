<?php
/*
|--------------------------------------------------------------------------
| 權限設定檔
| 結構：大分類 (Group) -> 子功能 (Sub-module) -> 操作 (Actions)
|--------------------------------------------------------------------------
*/
return [

    'advert_management' => [ // 群組標籤
        'label' => '廣告管理',
        'subs' => [
            'advert' => [
                'label' => '廣告列表',
                'actions' => ['view' => '瀏覽', 'create' => '新增', 'edit' => '編輯', 'delete' => '刪除'],
                'dependencies' => ['create' => ['view'], 'edit' => ['view'], 'delete' => ['view']]
            ],
            'advert_category' => [
                'label' => '廣告分類',
                'actions' => ['view' => '瀏覽', 'create' => '新增', 'edit' => '編輯', 'delete' => '刪除'],
                'dependencies' => ['create' => ['view'], 'edit' => ['view'], 'delete' => ['view']]
            ],
        ]
    ],

    'news_management' => [ // 群組標籤
        'label' => '消息管理',
        'subs' => [
            'news' => [
                'label' => '最新消息',
                'actions' => ['view' => '瀏覽', 'create' => '新增', 'edit' => '編輯', 'delete' => '刪除'],
                'dependencies' => ['create' => ['view'], 'edit' => ['view'], 'delete' => ['view']]
            ],
            'news_category' => [
                'label' => '消息分類',
                'actions' => ['view' => '瀏覽', 'create' => '新增', 'edit' => '編輯', 'delete' => '刪除'],
                'dependencies' => ['create' => ['view'], 'edit' => ['view'], 'delete' => ['view']]
            ],
        ]
    ],
    'permission_setting' => [
        'label' => '權限設定',
        'subs' => [
            'logs' => [
                'label' => '操作紀錄',
                'actions' => ['view' => '瀏覽', 'delete' => '刪除'],
                'dependencies' => ['delete' => ['view']]
            ],
            'roles' => [
                'label' => '角色管理',
                'actions' => ['view' => '瀏覽', 'create' => '新增', 'edit' => '編輯', 'delete' => '刪除'],
                'dependencies' => ['create' => ['view'], 'edit' => ['view'], 'delete' => ['view']]
            ],
            'admins' => [
                'label' => '網站管理員',
                'actions' => ['view' => '瀏覽', 'create' => '新增', 'edit' => '編輯', 'delete' => '刪除'],
                'dependencies' => ['create' => ['view'], 'edit' => ['view'], 'delete' => ['view']]
            ],
        ]
    ],
    'contact_management' => [
        'label' => '聯絡管理',
        'subs' => [
            'contact' => [
                'label' => '聯絡單列表',
                'actions' => ['view' => '瀏覽', 'create' => '回覆', 'delete' => '刪除'],
            ],
        ]
    ],
];
