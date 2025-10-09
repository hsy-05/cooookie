# Cooookie Laravel 專案展示

這是示範用 Laravel 12.21.0 + PHP 8.2 建立的專案，結合 AdminLTE及Summernote打造的後台管理系統。

## 功能

- 使用 Laravel Eloquent ORM 設計關聯資料表
- 後台管理使用 AdminLTE 模板
- 文章內容編輯器整合 Summernote
- RESTful API 與基礎認證用 Laravel Sanctum
- 使用 Laravel Task Scheduler 及 Queue 執行背景任務
- 單元測試覆蓋主要功能

## 安裝

1. `composer install`
2. 複製 `.env.example` 為 `.env`，設定資料庫等設定
3. `php artisan migrate --seed`
4. `npm install && npm run dev`
5. `php artisan serve`

## 測試

執行 `php artisan test`

