<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdvertCategory;
use App\Models\AdvertCategoryDesc;
use App\Models\Language;
use Illuminate\Http\Request;

class AdvertCategoryController extends Controller
{
    /** 列表 */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);

        $list = AdvertCategory::with('descs')
            ->orderBy('display_order', 'desc')
            ->paginate($perPage);

        return view('admin.advert_category.index', compact('list'));
    }

    /** 建立表單 */
    public function create()
    {
        $langs = Language::where('enabled', 1)->orderBy('display_order', 'desc')->get();
        $categoryList = AdvertCategory::with('descs')->get(); // 加這行載入所有選項

        // $defaultParams = [
        //     'item_limit_num' => -1,
        //     'fields' => [
        //         'adv_img_url' => ['width' => 1920, 'height' => 960],
        //         'adv_img_m_url' => ['width' => 800, 'height' => 960],
        //         'adv_link_url' => new \stdClass(),
        //     ],
        // ];

        return view('admin.advert_category.form', [
            'langs'           => $langs,
            'advert_category' => new AdvertCategory([
                'cat_func_scope' => ['adv_img_url', 'adv_img_m_url', 'adv_link_url'],
                // 'cat_params'     => $defaultParams,
                'is_visible'     => true,
                'display_order'     => 0,
            ]),
            'descMap'         => [],
            'categoryList'    => $categoryList, // 傳進 blade 用來產生 select options
        ]);
    }


    /** 儲存 */
    public function store(Request $request)
    {
        $data = $request->validate([
            'cat_code'        => 'required|string|max:50|unique:advert_category,cat_code',
            'cat_func_scope'  => 'nullable|array', // 例如 ["adv_img_url","adv_img_m_url","adv_link_url"]
            'cat_params'      => 'nullable|json',  // 也可讓前端傳字串 JSON
            'display_order'      => 'nullable|integer',
            'is_visible'      => 'nullable|boolean',
            'desc'            => 'nullable|array', // desc[lang_id][cat_name]
        ]);

        $category = AdvertCategory::create([
            'cat_code'       => $data['cat_code'],
            'cat_func_scope' => $data['cat_func_scope'] ?? [],
            'cat_params'     => isset($data['cat_params']) ? json_decode($data['cat_params'], true) : null,
            'display_order'     => $data['display_order'] ?? 0,
            'is_visible'     => $data['is_visible'] ?? true,
        ]);

        if (!empty($data['desc'])) {
            foreach ($data['desc'] as $langId => $descData) {
                if (!empty($descData['cat_name'])) {
                    AdvertCategoryDesc::create([
                        'cat_id'   => $category->cat_id,
                        'lang_id'  => $langId,
                        'cat_name' => $descData['cat_name'],
                    ]);
                }
            }
        }

        return redirect()
            ->route('admin.advert_category.index')
            ->with('form_success', '分類新增成功');
    }

    /** 編輯表單 */
    public function edit(AdvertCategory $advert_category)
    {
        $langs = Language::where('enabled', 1)->orderBy('display_order', 'desc')->get();
        $categoryList = AdvertCategory::with('descs')->get(); // 新增
        $advert_category->load('descs');
        $descMap = collect($advert_category->descs)->keyBy('lang_id')->all();

        return view('admin.advert_category.form', [
            'advert_category' => $advert_category,
            'langs'           => $langs,
            'isEdit'          => true,
            'descMap'         => $descMap,
            'categoryList'    => $categoryList,
        ]);
    }

    /** 更新 */
    public function update(Request $request, AdvertCategory $advert_category)
    {
        $data = $request->validate([
            'cat_code'        => 'required|string|max:50|unique:advert_category,cat_code,' . $advert_category->cat_id . ',cat_id',
            'cat_func_scope'  => 'nullable|array',
            'cat_params'      => 'nullable|json',
            'display_order'      => 'nullable|integer',
            'is_visible'      => 'nullable|boolean',
            'desc'            => 'nullable|array',
        ]);

        $advert_category->update([
            'cat_code'       => $data['cat_code'],
            'cat_func_scope' => $data['cat_func_scope'] ?? [],
            'cat_params'     => isset($data['cat_params']) ? json_decode($data['cat_params'], true) : null,
            'display_order'     => $data['display_order'] ?? 0,
            'is_visible'     => $data['is_visible'] ?? true,
        ]);

        if (!empty($data['desc'])) {
            foreach ($data['desc'] as $langId => $descData) {
                $exists = AdvertCategoryDesc::where('cat_id', $advert_category->cat_id)
                    ->where('lang_id', $langId)
                    ->first();

                $payload = ['cat_name' => $descData['cat_name'] ?? ''];
                if ($exists) {
                    $exists->update($payload);
                } else {
                    if (!empty($descData['cat_name'])) {
                        AdvertCategoryDesc::create([
                            'cat_id'   => $advert_category->cat_id,
                            'lang_id'  => $langId,
                            'cat_name' => $descData['cat_name'],
                        ]);
                    }
                }
            }
        }

        return redirect()
            ->route('admin.advert_category.index')
            ->with('form_success', '分類更新成功');
    }

    /** 刪除 */
    public function destroy(AdvertCategory $advert_category)
    {
        $advert_category->delete();
        return redirect()->route('admin.advert_category.index')->with('form_success', '分類已刪除');
    }
}
