<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\Admin\NewsRequest;
use App\Models\{News, NewsDesc, NewsCategory};
use Illuminate\Support\Facades\{DB, Log};
use App\Helpers\{ContentHelper, ImageHelper, SummernoteImageHelper, TagHelper};

/**
 * 管理控制器
 * 採用參數化配置，方便未來快速複製為其他文章模組（如：案例實績、活動花絮）
 */
class NewsController extends BaseAdminController
{
    /**
     * 核心配置
     */
    protected $permissionName = 'news';             // 權限代碼
    protected $modelClass     = News::class;         // 主模型類別
    protected $descClass      = NewsDesc::class;     // 語系模型類別
    protected $catClass       = NewsCategory::class; // 分類模型類別
    protected $routePrefix    = 'admin.news';       // 路由前綴
    protected $primaryKey     = 'news_id';           // 資料表主鍵名稱
    protected $logModuleName  = '消息管理';           // 日誌顯示名稱

    /**
     * 頁面相關配置
     * 集中管理圖片路徑與規格，調整時只需修改此處
     */
    protected $pageCfg = [
        'files' => [
            'image_url' => [
                'path'   => 'news',            // 圖片存放的資料夾名稱
                'width'  => 500,               // 縮圖後的寬度
                'height' => 360,               // 縮圖後的高度
                'mode'   => 'center_crop',     // 裁切模式：從中心裁切
                'useOriginalName' => false,    // 檔案名稱：自動生成隨機字串，避免中文檔名亂碼
            ],
        ],
    ];

    /**
     * 顯示管理列表
     *
     * @param Request $request 包含搜尋關鍵字 search
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 抓取搜尋關鍵字
        $search = $request->input('search');

        // 取得統一的分頁數量（由父類別 BaseAdminController 定義）
        $perPage = $this->getPerPage($request);

        /**
         * 【效能優化重點：解決 N+1 問題】
         * 使用 with() 預先載入關聯資料。
         * 1. 'descs'：載入消息的各語系標題。
         * 2. 'category.descs'：這是最關鍵的調整！
         *    不只載入分類(category)，連分類裡面的語系描述(descs)也一併載入。
         *    這樣在列表顯示「分類：春季餅乾」時，就不會重複觸發資料庫查詢。
         */
        $list = $this->modelClass::with(['descs', 'category.descs'])
            ->when($search, function ($query) use ($search) {
                // 搜尋各語系標題中包含關鍵字的資料
                $query->whereHas('descs', fn($q) => $q->where('title', 'like', "%{$search}%"));
            })
            ->orderByDesc('display_order') // 優先依照手動排序值
            ->orderByDesc($this->primaryKey) // 排序相同時，以編號由大到小排序
            ->paginate($perPage);

        // 取得類別名稱（不含命名空間），例如：News
        // class_basename 是 Laravel 內建函式，可以把 "App\Models\News" 轉成 "News"
        $modelName = class_basename($this->modelClass);

        // 回傳視圖
        return $this->view("{$this->routePrefix}.index", [
            'items'     => $list,
            'search'    => $search,
            'modelName' => $modelName // 將 Model 名稱傳給 Blade
        ]);
    }

    /**
     * 進入「新增」頁面
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // 傳入空模型，維持 View 變數一致性
        return $this->renderForm(new $this->modelClass);
    }

    /**
     * 儲存新增資料
     *
     * @param NewsRequest $request 驗證請求物件
     */
    public function store(NewsRequest $request)
    {
        // 使用資料庫交易機制，確保主表、圖片、語系三個步驟要麼全過，要麼全失敗回滾
        return DB::transaction(function () use ($request) {
            try {
                $item = new $this->modelClass;

                // 填充基本欄位，自動排除 image_url 檔案欄位以便手動處理
                $item->fill($request->safe()->except(['image_url']));

                // 處理主圖上傳，並根據 $pageCfg 的設定自動裁切與縮圖
                $this->handleFileUploads($request, $item);

                // 處理開關欄位：如果有勾選才存為 true
                $item->is_visible = $request->has('is_visible');
                $item->is_visible_home = $request->has('is_visible_home');
                $item->save();

                // 呼叫編輯器助手，將上傳到暫存區的內文圖片移動到正式目錄
                $editorId = $request->input('editor_id', 'default');
                SummernoteImageHelper::commitTempImages($editorId);

                // 儲存各語系的標題、SEO與內容
                $this->saveTranslations($item, $request->desc);

                // 紀錄操作紀錄
                $item->writeLog('新增', $item->currentDesc->title ?? "未知名{$this->logModuleName}");

                // 計算跳轉網址，優先回到列表頁
                $backUrl = $request->input('back_url', route("{$this->routePrefix}.index"));

                // 顯示成功提示視窗
                $this->showMsg(0, '新增完成', [
                    ['text' => '繼續新增', 'href' => route("{$this->routePrefix}.create")],
                    ['text' => '繼續編輯', 'href' => route("{$this->routePrefix}.edit", $item->{$this->primaryKey})],
                    ['text' => '返回列表', 'href' => $backUrl],
                ]);

                return redirect()->back();
            } catch (\Exception $e) {
                // 若失敗則紀錄錯誤日誌，並把輸入資料塞回 Session
                Log::error("{$this->logModuleName} Store Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', '新增失敗：' . $e->getMessage());
            }
        });
    }

    /**
     * 進入「編輯」頁面
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $item = $this->modelClass::findOrFail($id);
        return $this->renderForm($item);
    }

    /**
     * 儲存修改後的資料
     *
     * @param NewsRequest $request
     * @param mixed $id 路由傳入的主鍵 ID
     */
    public function update(NewsRequest $request, $id)
    {
        $item = $this->modelClass::findOrFail($id);

        return DB::transaction(function () use ($request, $item) {
            try {
                // 更新主表，排除檔案欄位
                $item->fill($request->safe()->except(['image_url']));

                // 更新圖片，若有新圖會自動覆蓋並刪除舊圖
                $this->handleFileUploads($request, $item);

                $item->is_visible = $request->has('is_visible');
                $item->is_visible_home = $request->has('is_visible_home');
                $item->save();

                // 更新語系資料，並自動清理內容中被刪除的圖片檔案
                $this->saveTranslations($item, $request->desc);

                // 紀錄操作日誌
                $item->writeLog('編輯', $item->currentDesc->title ?? "未知名{$this->logModuleName}");

                $backUrl = $request->input('back_url', route("{$this->routePrefix}.index"));

                $this->showMsg(0, '編輯操作完成', [
                    ['text' => '繼續編輯', 'href' => route("{$this->routePrefix}.edit", $item->{$this->primaryKey})],
                    ['text' => '返回列表', 'href' => $backUrl],
                ]);

                return redirect()->back();
            } catch (\Exception $e) {
                Log::error("{$this->logModuleName} Update Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', '更新失敗');
            }
        });
    }

    /**
     * 刪除單筆資料
     *
     * @param mixed $id 路由傳入的主鍵 ID
     */
    public function destroy($id)
    {
        $item = $this->modelClass::findOrFail($id);
        $item->load('descs');
        $title = $item->currentDesc->title ?? "未知名{$this->logModuleName}";

        // 刪除前清理編輯器中的實體圖片檔案
        foreach ($item->descs as $desc) {
            SummernoteImageHelper::syncEditorImages(ContentHelper::decodeSiteUrl($desc->content), null);
        }

        // 執行刪除，並觸發 Model 的刪除事件處理封面圖
        $item->delete();

        $item->writeLog('刪除', $title);

        return redirect()->route("{$this->routePrefix}.index")->with('form_success_swal', '資料已刪除');
    }

    /**
     * AJAX 刪除指定圖片欄位檔案
     *
     * @param Request $request
     * @param mixed $id 路由傳入的主鍵 ID
     */
    public function deleteImageField(Request $request, $id)
    {
        $item = $this->modelClass::findOrFail($id);
        return $item->deleteImageFieldGeneric($request);
    }

    /**
     * 內部共用：準備表單所需的資料並渲染 View
     *
     * @param mixed $item 模型物件
     */
    private function renderForm($item)
    {
        $isEdit = (bool)$item->exists;

        // 取得分類下拉選單需要的資料
        $categories = $this->catClass::with('descs')->orderByDesc('display_order')->get();

        // 防呆：進入表單時清理超過 24 小時的孤立暫存圖
        SummernoteImageHelper::cleanAbandonedImages();

        $langs = $this->getActiveLanguages();
        $fileConfigs = $this->pageCfg['files'];

        $descMap = [];
        if ($isEdit) {
            // 編輯時載入語系，將存於資料庫的縮減路徑還原成編輯器可顯示的完整路徑
            $item->load('descs');
            foreach ($item->descs as $desc) {
                // 將內容中的縮減網址路徑還原成完整網址，方便編輯器顯示
                $desc->content = ContentHelper::decodeSiteUrl($desc->content);
                $descMap[$desc->lang_id] = $desc;
            }
        }

        $backUrl = $this->getBackUrl("{$this->routePrefix}.index");

        return $this->view("{$this->routePrefix}.form", [
            'item'        => $item,
            'isEdit'      => $isEdit,
            'categories'  => $categories,
            'langs'       => $langs,
            'descMap'     => $descMap,
            'fileConfigs' => $fileConfigs,
            'backUrl'     => $backUrl
        ]);
    }

    /**
     * 萬用檔案上傳處理：遍歷設定檔自動存檔
     *
     * @param Request $request
     * @param mixed $item
     */
    private function handleFileUploads(Request $request, $item)
    {
        foreach ($this->pageCfg['files'] as $field => $config) {
            if ($request->hasFile($field)) {
                $item->$field = ImageHelper::handleUpload(
                    $request->file($field),
                    $config['path'],
                    $item->$field,
                    $config
                );
            }
        }
    }
/**
 * 儲存多語系描述資料
 *
 * @param mixed $item 主表模型
 * @param array|null $descData 語系資料陣列
 */
private function saveTranslations($item, ?array $descData)
{
    if (!$descData) return;

    foreach ($descData as $langId => $data) {
        // 修正：確保變數名稱為 $langId
        $oldDesc = $this->descClass::where($this->primaryKey, $item->{$this->primaryKey})
            ->where('lang_id', $langId)
            ->first();

        // 若標題空白，執行刪除
        if (empty($data['title'])) {
            if ($oldDesc) {
                SummernoteImageHelper::syncEditorImages(ContentHelper::decodeSiteUrl($oldDesc->content), null);
                $oldDesc->delete();
            }
            continue;
        }

        $newContent = $data['content'] ?? '';

        // 比對圖片變化
        SummernoteImageHelper::syncEditorImages(
            $oldDesc ? ContentHelper::decodeSiteUrl($oldDesc->content) : null,
            $newContent
        );

        // 處理 SEO
        // 修正：原程式碼此處有定義 $metaKeyword 但下面沒用到
        $metaKeyword = TagHelper::toString($data['meta_keyword'] ?? null);

        // 執行資料更新或建立
        $this->descClass::updateOrCreate(
            [
                $this->primaryKey => $item->{$this->primaryKey},
                'lang_id' => $langId
            ],
            [
                'title'            => $data['title'],
                'description'      => $data['description'] ?? null,
                'content'          => ContentHelper::encodeSiteUrl($newContent),
                'meta_title'       => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'meta_keyword'     => $metaKeyword, // 修正：應使用處理過的字串
                'seo_h1'           => $data['seo_h1'] ?? null,
            ]
        );
    }
}

    /**
     * 列表批次刪除功能
     *
     * @param Request $request
     */
    public function batchDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return back()->with('error', "請選擇要刪除的{$this->logModuleName}");

        $itemList = $this->modelClass::whereIn($this->primaryKey, $ids)->get();

        foreach ($itemList as $item) {
            $this->destroy($item->{$this->primaryKey});
        }

        $this->writeBatchDeleteLog($this->logModuleName, $itemList->count(), $ids);

        return back()->with('form_success_swal', "已刪除 {$itemList->count()} 筆資料");
    }
}
