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
use Illuminate\Support\Str; // 引入 Str 處理字串

class ImageHelper
{
    /**
     * 萬用上傳入口
     */
    public static function handleUpload(UploadedFile $file, string $folder, ?string $oldPath = null, array $config = []): string
    {
        $disk = 'public';

        // 1. 清理舊檔
        if ($oldPath) {
            self::deleteImage($oldPath, $disk);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $useOriginalName = $config['useOriginalName'] ?? false;

        // 2. 決定檔名 (使用 Laravel 內建方法更安全)
        $filename = $useOriginalName
            ? self::getSmartFilename($file, $folder, $disk)
            : self::generateUniqueFilename($file);

        // 3. 判斷是否需要影像處理
        $imageExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $hasSizeConfig = isset($config['width']) || isset($config['height']);

        if (in_array($extension, $imageExtensions) && $hasSizeConfig) {
            try {
                $processed = self::processImage(
                    $file,
                    $config['width'] ?? 0,
                    $config['height'] ?? 0,
                    $config['mode'] ?? 'center_crop',
                    $config['bgColor'] ?? 'ffffff' // Intervention Image v3 推薦去井字號
                );

                return self::saveProcessedImage($processed, "{$folder}/{$filename}", $disk, 90, $extension);
            } catch (\Exception $e) {
                // 如果影像處理失敗（例如記憶體不足），記錄錯誤並退回為一般檔案上傳
                Log::error('影像處理失敗: ' . $e->getMessage());
            }
        }

        // 4. 一般檔案直接儲存 (使用 Storage::putFileAs 更穩定)
        return Storage::disk($disk)->putFileAs($folder, $file, $filename);
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
    public static function processImage(UploadedFile $file, int $targetWidth, int $targetHeight, string $mode = 'center_crop', string $bgColor = 'ffffff'): ImageInterface
    {
        $manager = new ImageManager(new Driver());
        $img = $manager->read($file->getRealPath()); // 使用 getRealPath() 確保路徑正確

        // 若無指定尺寸，使用原圖尺寸
        $finalWidth = $targetWidth > 0 ? $targetWidth : $img->width();
        $finalHeight = $targetHeight > 0 ? $targetHeight : $img->height();

        // 避免將小圖強制放大失真 (scale_fill 除外)
        if ($mode !== 'scale_fill') {
            $finalWidth = min($finalWidth, $img->width());
            $finalHeight = min($finalHeight, $img->height());
        }

        //
        return match ($mode) {

            /**
             * [中心裁切]
             * 行為：將圖片縮放填滿框架並裁掉多餘邊緣。
             * 適合：列表縮圖、Banner 等需要高度整齊排版的區域。
             */
            'center_crop' => $img->coverDown($finalWidth, $finalHeight, 'center'),

            /**
             * [等比例縮放]
             * 行為：依照原圖比例縮小到目標尺寸內，不進行裁切。
             * 適合：情境圖、作品照等不允許內容被截斷的情況。
             */
            'scale_fit'   => $img->scaleDown($finalWidth, $finalHeight),

            /**
             * [比例縮放 + 補背景色]
             * 行為：圖片縮小後置中，剩餘空白處用背景色填滿成固定尺寸。
             * 適合：型錄網站、LOGO 牆，確保圖片內容完整且整體視覺統一。
             */
            'scale_fill'  => self::applyScaleFill($manager, $img, $finalWidth, $finalHeight, $bgColor),
            default       => throw new \InvalidArgumentException("無效的圖片處理模式: {$mode}"),
        };
    }

    /**
     * 獨立出 scale_fill 邏輯，讓 processImage 更乾淨
     */
    private static function applyScaleFill(ImageManager $manager, ImageInterface $img, int $width, int $height, string $bgColor): ImageInterface
    {
        $resized = $img->scaleDown($width, $height);
        $canvas = $manager->create($width, $height)->fill($bgColor);
        return $canvas->place($resized, 'center');
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
        // 確保原檔名安全 (過濾掉特殊字元)
        $originalName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = $file->getClientOriginalExtension();
        $filename = "{$originalName}.{$extension}";
        $counter = 1;

        while (Storage::disk($disk)->exists("{$folder}/{$filename}")) {
            $filename = "{$originalName}-{$counter}.{$extension}"; // 改用 - 比較符合網址規範
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
    public static function saveProcessedImage(ImageInterface $image, string $path, string $disk = 'public', int $quality = 90, string $format = 'jpeg'): string
    {
        // 根據檔案類型自動切換對應的編碼器
        $encoder = match (strtolower($format)) {
            'png'  => new PngEncoder(),
            'webp' => new WebpEncoder(quality: $quality),
            default=> new JpegEncoder($quality),
        };

        Storage::disk($disk)->put($path, (string) $image->encode($encoder));
        return $path;
    }

    /**
     * 刪除檔案 (支援單一或批量路徑)
     *
     * @param string|array|null $path 檔案路徑
     * @param string $disk 磁碟名稱
     * @return bool 是否執行成功
     */
    public static function deleteImage($path, $disk = 'public'): bool
    {
        if (empty($path)) return false;

        // 若傳入的是陣列，則利用遞迴將所有路徑都跑一遍
        if (is_array($path)) {
            foreach ($path as $p) {
                self::deleteImage($p, $disk);
            }
            return true;
        }

        // 防止刪除目錄等危險操作
        if (Storage::disk($disk)->exists($path) && !is_dir(Storage::disk($disk)->path($path))) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }
}
