<?php

namespace App\Helpers;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Interfaces\ImageInterface; // 引入 ImageInterface
use Intervention\Image\Encoders\JpegEncoder; // 引入
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImageHelper
{
    /**
     * 處理圖片裁切 / 縮圖 / 補背景等功能
     *
     * @param UploadedFile $file 接收到的上傳檔案
     * @param int $targetWidth 目標寬度
     * @param int $targetHeight 目標高度
     * @param string $mode 處理模式：'center_crop' (中心裁切填滿), 'scale_fit' (等比例縮圖不補背景), 'scale_fill' (等比例縮圖補背景)
     * @param string $bgColor 補背景色 (僅 'scale_fill' 模式有效)
     * @return ImageInterface 返回處理後的 Intervention Image 物件
     * @throws \InvalidArgumentException 如果模式無效
     */
    public static function processImage(UploadedFile $file, int $targetWidth, int $targetHeight, string $mode = 'center_crop', string $bgColor = '#ffffff'): ImageInterface
    {
        $manager = new ImageManager(new Driver());

        try {
            $img = $manager->read($file);
        } catch (\Exception $e) {
            // 捕獲讀取檔案時可能發生的錯誤，例如檔案損壞
            throw new \RuntimeException("無法讀取圖片檔案: " . $e->getMessage(), 0, $e);
        }

        $originalWidth = $img->width();
        $originalHeight = $img->height();

        // 限制不超過原圖（避免放大模糊），這是非常好的實踐
        $targetWidth = min($targetWidth, $originalWidth);
        $targetHeight = min($targetHeight, $originalHeight);

        switch ($mode) {
            // 中心裁切填滿（coverDown）：圖片會被裁切以填滿目標尺寸，不留白
            case 'center_crop':
                $img = $img->coverDown($targetWidth, $targetHeight, 'center');
                break;

            // 等比例縮圖，不補背景：圖片會等比例縮小，可能比指定尺寸小，不填滿
            case 'scale_fit':
                $img = $img->scaleDown($targetWidth, $targetHeight);
                break;

            // 等比例縮圖，補背景色到指定大小：圖片等比例縮小後，不足部分用背景色填補
            case 'scale_fill':
                $resized = $img->scaleDown($targetWidth, $targetHeight);

                $canvas = $manager->create($targetWidth, $targetHeight)
                    ->fill($bgColor);

                $canvas->place($resized, 'center'); // 將縮圖後的圖片置中
                $img = $canvas;
                break;

            default:
                throw new \InvalidArgumentException('無效的圖片處理模式: ' . $mode);
        }

        return $img;
    }

    /**
     * 生成一個唯一的檔名
     *
     * @param UploadedFile $file 上傳檔案物件
     * @return string 唯一的檔名 (例如: 1678886400_65f3a7b0c9d4e.jpg)
     */
    public static function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $microTime = str_replace('.', '', microtime(true)); // 微秒時間
        return $microTime . '.' . $extension;
    }

    /**
     * 將處理後的圖片編碼並儲存到指定路徑
     *
     * @param ImageInterface $image 處理後的 Intervention Image 物件
     * @param string $path 儲存路徑 (例如: 'adv/filename.jpg')
     * @param string $disk 儲存磁碟 (例如: 'public')
     * @param int $quality 圖片品質 (0-100, 僅對 JPEG 有效)
     * @param string $format 輸出格式 (例如: 'jpeg', 'png', 'webp')
     * @return string 儲存後的完整路徑 (例如: 'adv/filename.jpg')
     * @throws \RuntimeException 如果儲存失敗
     */
    public static function saveProcessedImage($image, string $path, string $disk = 'public', int $quality = 90, string $format = 'jpeg'): string
    {
        $format = strtolower($format);
        // 根據格式建立對應 Encoder 物件
        $encoder = match ($format) {
            'png' => new \Intervention\Image\Encoders\PngEncoder(),
            'webp' => new \Intervention\Image\Encoders\WebpEncoder(quality: $quality),
            default => new JpegEncoder($quality),
        };
        // 使用 Encoder 物件編碼圖片
        $encodedImage = $image->encode($encoder);
        \Illuminate\Support\Facades\Storage::disk($disk)->put($path, $encodedImage);
        return $path;
    }

    /**
     * 刪除圖片檔案（支援單一或批量）
     *
     * @param string|array $path 檔案路徑（相對，如 uploads/xxx.jpg）或陣列
     * @param string $disk 磁碟名稱，預設 'public'
     * @return bool|array 刪除成功（單一）或陣列（批量）
     */
    public static function deleteImage($path, $disk = 'public')
    {
        if (is_array($path)) {
            // 批量刪除
            $results = [];
            foreach ($path as $p) {
                $results[$p] = self::deleteImage($p, $disk); // 遞迴呼叫單一
            }
            return $results;
        }

        // 單一刪除
        try {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
                Log::info("ImageHelper: 成功刪除圖片檔案：{$path} (磁碟: {$disk})");
                return true;
            } else {
                Log::warning("ImageHelper: 圖片檔案不存在，無法刪除：{$path} (磁碟: {$disk})");
                return false;
            }
        } catch (\Exception $e) {
            Log::error("ImageHelper: 刪除圖片檔案失敗：{$path}，錯誤：{$e->getMessage()} (磁碟: {$disk})");
            return false;
        }
    }
}
