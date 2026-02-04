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
     * 萬用上傳入口 (支援圖片處理與一般檔案)
     * * @param UploadedFile $file 上傳的檔案物件
     * @param string $folder 儲存目錄 (例如 'news')
     * @param string|null $oldPath 舊圖路徑 (用於刪除)
     * @param array $config 配置 (包含 width, height, mode, useOriginalName 等)
     * @return string 儲存後的完整路徑
     */
    public static function handleUpload(UploadedFile $file, string $folder, ?string $oldPath = null, array $config = []): string
    {
        $disk = 'public';

        // 1. 刪除舊檔案 (防呆：確保路徑存在才刪除)
        if ($oldPath) {
            self::deleteImage($oldPath, $disk);
        }

        // 2. 處理檔名
        $extension = strtolower($file->getClientOriginalExtension());
        $useOriginalName = $config['useOriginalName'] ?? false;

        if ($useOriginalName) {
            $filename = self::getSmartFilename($file, $folder, $disk);
        } else {
            $filename = self::generateUniqueFilename($file);
        }

        $fullPath = "{$folder}/{$filename}";

        // 3. 判斷是否為圖片且需要處理
        $imageExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $hasSizeConfig = isset($config['width']) || isset($config['height']);

        if (in_array($extension, $imageExtensions) && $hasSizeConfig) {
            // 處理圖片 (裁切/縮圖)
            $processed = self::processImage(
                $file,
                $config['width'] ?? 0,
                $config['height'] ?? 0,
                $config['mode'] ?? 'center_crop'
            );

            // 儲存處理後的圖片
            return self::saveProcessedImage($processed, $fullPath, $disk, 90, $extension);
        }

        // 4. 一般檔案 (PDF/Word/SVG/不縮圖的圖片) 直接儲存
        Storage::disk($disk)->putFileAs($folder, $file, $filename);
        return $fullPath;
    }

    /**
     * 處理圖片裁切 / 縮圖 / 補背景
     */
    public static function processImage(UploadedFile $file, int $targetWidth, int $targetHeight, string $mode = 'center_crop', string $bgColor = '#ffffff'): ImageInterface
    {
        $manager = new ImageManager(new Driver());
        $img = $manager->read($file);

        // 防呆：若沒設定尺寸則以原圖尺寸為準
        $targetWidth = $targetWidth > 0 ? $targetWidth : $img->width();
        $targetHeight = $targetHeight > 0 ? $targetHeight : $img->height();

        // 限制不超過原圖避免放大模糊
        $targetWidth = min($targetWidth, $img->width());
        $targetHeight = min($targetHeight, $img->height());

        switch ($mode) {
            case 'center_crop':
                $img = $img->coverDown($targetWidth, $targetHeight, 'center');
                break;
            case 'scale_fit':
                $img = $img->scaleDown($targetWidth, $targetHeight);
                break;
            case 'scale_fill':
                $resized = $img->scaleDown($targetWidth, $targetHeight);
                $canvas = $manager->create($targetWidth, $targetHeight)->fill($bgColor);
                $canvas->place($resized, 'center');
                $img = $canvas;
                break;
        }

        return $img;
    }

    /**
     * 智慧檔名生成：若檔名重複，自動產生 (1), (2) 避免覆蓋
     */
    private static function getSmartFilename(UploadedFile $file, string $folder, string $disk): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $filename = "{$originalName}.{$extension}";
        $counter = 1;

        // 檢查檔案是否存在，若存在則循環增加序號
        while (Storage::disk($disk)->exists("{$folder}/{$filename}")) {
            $filename = "{$originalName}({$counter}).{$extension}";
            $counter++;
        }

        return $filename;
    }

    /**
     * 生成唯一檔名 (時間戳記+隨機碼)
     */
    public static function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $microTime = str_replace('.', '', microtime(true)); // 微秒時間
        return $microTime . '.' . $extension; // 也可以加隨機碼   . '_' . uniqid()
    }

    /**
     * 儲存處理後的圖片 (支援多格式)
     */
    public static function saveProcessedImage($image, string $path, string $disk = 'public', int $quality = 90, string $format = 'jpeg'): string
    {
        $encoder = match (strtolower($format)) {
            'png' => new PngEncoder(),
            'webp' => new WebpEncoder(quality: $quality),
            default => new JpegEncoder($quality),
        };

        Storage::disk($disk)->put($path, $image->encode($encoder));
        return $path;
    }

    /**
     * 刪除檔案 (單一或批量)
     */
    public static function deleteImage($path, $disk = 'public')
    {
        if (!$path) return false;

        if (is_array($path)) {
            foreach ($path as $p) { self::deleteImage($p, $disk); }
            return true;
        }

        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }
        return false;
    }
}
