<?php

namespace Ab01faz101\LaravelImageResizer\Traits;


use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Interfaces\EncoderInterface;


trait LaravelImageResizer
{

    public function resizeAndSave(UploadedFile $image, string $directory = 'images', string $disk = 'public'): array
    {
        // تعیین پسوند و ایجاد نام یکتا برای فایل‌ها
        $extension = strtolower($image->getClientOriginalExtension());
        $filenameBase = time().$image->getClientOriginalName();
        $filenameXL = "{$filenameBase}_xl.{$extension}";
        $filenameMd = "{$filenameBase}_md.{$extension}";
        $filenameSm = "{$filenameBase}_sm.{$extension}";
        $filenameXs = "{$filenameBase}_xs.{$extension}";

        // بارگذاری تصویر اصلی برای استخراج ابعاد
        $imgOriginal = Image::read($image);
        $width = $imgOriginal->width();
        $height = $imgOriginal->height();

        // تعیین encoder مناسب بر اساس پسوند
        $encoder = $this->getEncoder($extension);

        // ایجاد و ذخیره نسخه متوسط (md)
        $imgMd = Image::read($image);
        $imgMd->resize((int)($width / 2), (int)($height / 2));
        $mdData = (string) $imgMd->encode($encoder);
        Storage::disk($disk)->put("$directory/{$filenameMd}", $mdData);

        // ایجاد و ذخیره نسخه کوچک (sm) - یک سوم اندازه اصلی
        $imgSm = Image::read($image);
        $imgSm->resize((int)($width / 3), (int)($height / 3));
        $smData = (string) $imgSm->encode($encoder);
        Storage::disk($disk)->put("$directory/{$filenameSm}", $smData);

        // ایجاد و ذخیره نسخه بسیار کوچک (xs) - یک چهارم اندازه اصلی
        $imgXs = Image::read($image);
        $imgXs->resize((int)($width / 4), (int)($height / 4));
        $xsData = (string) $imgXs->encode($encoder);
        Storage::disk($disk)->put("$directory/{$filenameXs}", $xsData);

        // ذخیره تصویر اصلی (xl)
        $pathXL = $image->storeAs($directory, $filenameXL, $disk);

        return [
            'xl' => $pathXL,
            'md' => "$directory/{$filenameMd}",
            'sm' => "$directory/{$filenameSm}",
            'xs' => "$directory/{$filenameXs}",
        ];
    }
    /**
     * Resize and save image with custom sizes
     *
     * @param UploadedFile $image The uploaded image file
     * @param array $sizes Array of sizes (e.g., ['sm' => [200, 150], 'lg' => [300, 200]])
     * @param string $directory Storage directory
     * @param string $disk Storage disk
     * @return array Paths of resized images
     */
    /**
     * Resize and save image with custom sizes
     *
     * @param UploadedFile $image The uploaded image file
     * @param array $sizes Array of sizes (e.g., ['sm' => [200, 150], 'lg' => [300, 200]])
     * @param string $directory Storage directory
     * @param string $disk Storage disk
     * @return array Paths of resized images
     */
    public function resizeWithCustomSizes(
        UploadedFile $image,
        array $sizes = null,
        string $directory = 'images',
        string $disk = 'public'
    ): array {
        // Generate unique filename
        $extension = strtolower($image->getClientOriginalExtension());
        $filenameBase = time() . '_' . pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);

        // Store original image
        $filenameOriginal = "{$filenameBase}_original.{$extension}";
        $pathOriginal = $image->storeAs($directory, $filenameOriginal, $disk);

        $result = ['original' => $pathOriginal];

        // Get appropriate encoder
        $encoder = $this->getEncoder($extension);

        // Process each size
        foreach ($sizes as $sizeName => $dimensions) {
            $width = $dimensions[0] ?? null;
            $height = $dimensions[1] ?? null;

            if (!$width || !$height) {
                continue;
            }

            $filename = "{$filenameBase}_{$sizeName}.{$extension}";

            $img = Image::read($image);
            $img->resize($width, $height);

            $imageData = (string) $img->encode($encoder);
            Storage::disk($disk)->put("$directory/{$filename}", $imageData);

            $result[$sizeName] = "$directory/{$filename}";
        }

        return $result;
    }

    protected function getEncoder(string $extension): EncoderInterface
    {
        return match ($extension) {
            'jpg', 'jpeg' => new JpegEncoder(),
            'png' => new PngEncoder(),
            'webp' => new WebpEncoder(),
            default => new JpegEncoder(), // پیش‌فرض
        };
    }

}
