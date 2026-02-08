<?php

namespace App\Helpers;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImageHelper
{
    /**
     * 萬用上傳入口 (自動判斷檔案類型並處理)
     *
     * @param UploadedFile $file 上傳的檔案物件
     * @param string $folder 儲存路徑 (如 'news', 'products')
     * @param string|null $oldPath 舊檔案路徑 (傳入則自動刪除舊檔)
     * @param array $config 處理設定 (width, height, mode, useOriginalName 等)
     * @return string 儲存後的相對路徑
     */
    public static function handleUpload(UploadedFile $file, string $folder, ?string $oldPath = null, array $config = []): string
    {
        $disk = 'public';

        // 刪除舊檔案 (防呆：確保路徑存在才刪除)
        if ($oldPath) {
            self::deleteImage($oldPath, $disk);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $useOriginalName = $config['useOriginalName'] ?? false;

        // 決定檔名：保留原檔名（自動避開重複）或是生成隨機唯一碼
        if ($useOriginalName) {
            $filename = self::getSmartFilename($file, $folder, $disk);
        } else {
            $filename = self::generateUniqueFilename($file);
        }

        $fullPath = "{$folder}/{$filename}";

        // 判斷是否為圖片，且是否有給予縮圖尺寸的需求
        $imageExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $hasSizeConfig = isset($config['width']) || isset($config['height']);

        if (in_array($extension, $imageExtensions) && $hasSizeConfig) {
            // 進入影像處理引擎 (裁切、縮放或補背景)
            $processed = self::processImage(
                $file,
                $config['width'] ?? 0,
                $config['height'] ?? 0,
                $config['mode'] ?? 'center_crop',
                $config['bgColor'] ?? '#ffffff'
            );

            // 儲存處理後的圖檔並回傳路徑
            return self::saveProcessedImage($processed, $fullPath, $disk, 90, $extension);
        }

        // 一般檔案 (如 PDF) 或不需處理的圖，直接存入指定的磁碟位置
        Storage::disk($disk)->putFileAs($folder, $file, $filename);
        return $fullPath;
    }

    /**
     * 影像處理核心 (裁切 / 縮圖 / 補畫布背景)
     *
     * @param UploadedFile $file 原始上傳檔案
     * @param int $targetWidth 目標寬度
     * @param int $targetHeight 目標高度
     * @param string $mode 處理模式 (center_crop, scale_fit, scale_fill)
     * @param string $bgColor 畫布補位背景色 (預設白色)
     * @return ImageInterface 處理後的圖片物件
     */
    public static function processImage(UploadedFile $file, int $targetWidth, int $targetHeight, string $mode = 'center_crop', string $bgColor = '#ffffff'): ImageInterface
    {
        $manager = new ImageManager(new Driver());
        $img = $manager->read($file);

        // 初始化尺寸設定，若沒傳寬高則預設使用原圖大小
        $finalWidth = $targetWidth > 0 ? $targetWidth : $img->width();
        $finalHeight = $targetHeight > 0 ? $targetHeight : $img->height();

        // scale_fill 模式需強制畫布尺寸；其餘模式則檢查原圖尺寸，若原圖較小則不強行放大以維護品質
        if ($mode !== 'scale_fill') {
            $targetWidth = min($finalWidth, $img->width());
            $targetHeight = min($finalHeight, $img->height());
        } else {
            $targetWidth = $finalWidth;
            $targetHeight = $finalHeight;
        }

        switch ($mode) {
            /**
             * [中心裁切]
             * 行為：將圖片縮放填滿框架並裁掉多餘邊緣。
             * 適合：列表縮圖、Banner 等需要高度整齊排版的區域。
             */
            case 'center_crop':
                $img = $img->coverDown($targetWidth, $targetHeight, 'center');
                break;

            /**
             * [等比例縮放]
             * 行為：依照原圖比例縮小到目標尺寸內，不進行裁切。
             * 適合：情境圖、作品照等不允許內容被截斷的情況。
             */
            case 'scale_fit':
                $img = $img->scaleDown($targetWidth, $targetHeight);
                break;

            /**
             * [比例縮放 + 補背景色]
             * 行為：圖片縮小後置中，剩餘空白處用背景色填滿成固定尺寸。
             * 適合：型錄網站、LOGO 牆，確保圖片內容完整且整體視覺統一。
             */
            case 'scale_fill':
                $resized = $img->scaleDown($targetWidth, $targetHeight);
                $canvas = $manager->create($targetWidth, $targetHeight)->fill($bgColor);
                $canvas->place($resized, 'center');
                $img = $canvas;
                break;

            default:
                throw new \InvalidArgumentException('無效的圖片處理模式: ' . $mode);
        }

        return $img;
    }

    /**
     * 檔名重複處理機制 (自動附加序號)
     *
     * @param UploadedFile $file 檔案物件
     * @param string $folder 資料夾路徑
     * @param string $disk 磁碟名稱
     * @return string 最終可用檔名
     */
    private static function getSmartFilename(UploadedFile $file, string $folder, string $disk): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $filename = "{$originalName}.{$extension}";
        $counter = 1;

        // 若資料夾內已存在同名檔案，則自動命名為 (1), (2)... 避免覆蓋
        while (Storage::disk($disk)->exists("{$folder}/{$filename}")) {
            $filename = "{$originalName}({$counter}).{$extension}";
            $counter++;
        }

        return $filename;
    }

    /**
     * 生成唯一隨機檔名 (高精度時間戳)
     *
     * @param UploadedFile $file 檔案物件
     * @return string 隨機檔名
     */
    public static function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $microTime = str_replace('.', '', microtime(true)); // 微秒時間
        return $microTime . '.' . $extension; // 也可以加隨機碼   . '_' . uniqid()
    }

    /**
     * 編碼並儲存影像檔案
     *
     * @param ImageInterface $image 圖片物件
     * @param string $path 儲存路徑
     * @param string $disk 磁碟名稱
     * @param int $quality 壓縮品質 (0-100)
     * @param string $format 存檔格式 (jpg, png, webp)
     * @return string 存檔成功後的路徑
     */
    public static function saveProcessedImage($image, string $path, string $disk = 'public', int $quality = 90, string $format = 'jpeg'): string
    {
        // 根據檔案類型自動切換對應的編碼器
        $encoder = match (strtolower($format)) {
            'png' => new PngEncoder(),
            'webp' => new WebpEncoder(quality: $quality),
            default => new JpegEncoder($quality),
        };

        Storage::disk($disk)->put($path, $image->encode($encoder));
        return $path;
    }

    /**
     * 刪除檔案 (支援單一或批量路徑)
     *
     * @param string|array|null $path 檔案路徑
     * @param string $disk 磁碟名稱
     * @return bool 是否執行成功
     */
    public static function deleteImage($path, $disk = 'public')
    {
        if (!$path) return false;

        // 若傳入的是陣列，則利用遞迴將所有路徑都跑一遍
        if (is_array($path)) {
            foreach ($path as $p) { self::deleteImage($p, $disk); }
            return true;
        }

        // 確認檔案確實存在才執行實體刪除，避免系統報錯
        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }
        return false;
    }
}
