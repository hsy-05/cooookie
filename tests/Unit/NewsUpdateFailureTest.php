<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NewsUpdateFailureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function update_fails_with_invalid_category_id()
    {
        // 建立一筆新聞資料
        $news = News::factory()->create();

        // 傳入不存在的 cat_id
        $response = $this->post(route('admin.news.update', $news->news_id), [
            'cat_id' => 999999, // 不存在的分類ID
            'desc' => [
                1 => ['title' => '測試標題', 'content' => '測試內容'],
            ],
        ]);

        // 檢查 redirect 回上一頁
        $response->assertRedirect();

        // 檢查 session flash 是否有 form_success
        $this->assertTrue(session()->has('form_success'));

        $flash = session('form_success');
        $this->assertEquals(1, $flash['msg_type']); // 錯誤訊息
        $this->assertStringContainsString('更新失敗', $flash['title']);
    }

    /** @test */
    public function update_fails_when_desc_title_is_empty()
    {
        $news = News::factory()->create();

        // 模擬 desc title 為空，應該觸發 delete 或失敗邏輯
        $response = $this->post(route('admin.news.update', $news->news_id), [
            'cat_id' => null,
            'desc' => [
                1 => ['title' => '', 'content' => '測試內容']
            ]
        ]);

        $response->assertRedirect();
        $this->assertTrue(session()->has('form_success'));

        $flash = session('form_success');
        $this->assertEquals(1, $flash['msg_type']); // 錯誤訊息
        $this->assertStringContainsString('更新失敗', $flash['title']);
    }

    /** @test */
    public function update_fails_with_invalid_image_upload()
    {
        $news = News::factory()->create();

        // 模擬上傳非圖片檔案
        $response = $this->post(route('admin.news.update', $news->news_id), [
            'cat_id' => null,
            'desc' => [
                1 => ['title' => '測試標題', 'content' => '測試內容']
            ],
            'image' => 'not-a-file', // 非法檔案
        ]);

        $response->assertRedirect();
        $this->assertTrue(session()->has('form_success'));

        $flash = session('form_success');
        $this->assertEquals(1, $flash['msg_type']);
        $this->assertStringContainsString('更新失敗', $flash['title']);
    }
}
