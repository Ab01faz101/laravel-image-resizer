<?php

namespace Ab01faz101\LaravelImageResizer\Traits;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

trait LaravelImageResizer
{
    public function resizeAndSave(
        UploadedFile $image,
        string $directory = 'images',
        string $disk = 'public',
        ?string $overrideEncoder = null // مقدار دلخواه کاربر
    ): array {
        // پسوند و نام فایل یکتا
        $extension = strtolower($image->getClientOriginalExtension());
        $name = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $filenameBase = Str::slug($name) . '_' . now()->timestamp;

        // وضعیت انکودر از کانفیگ
        $encoderStatus = config('laravel_image_resizer.config.encoder_status');

        $encoderType = null;
        if ($encoderStatus) {
            $encoderType = $this->getEncoderType($extension, $overrideEncoder);
        }

        // تابع کمکی برای ساخت نام فایل با پسوند مناسب
        $makeFilename = fn($size) => $filenameBase . "_{$size}." . ($encoderType === 'jpeg' ? 'jpg' : ($encoderType ?? $extension));

        // ذخیره نسخه‌های مختلف با اندازه دلخواه
        $imgOriginal = Image::read($image);
        $width = $imgOriginal->width();
        $height = $imgOriginal->height();

        // نسخه XL (اصلی) را اگر انکودر فعال است، encode و ذخیره کن، در غیر اینصورت مستقیم آپلود کن
        $filenameXL = $makeFilename('xl');
        if ($encoderStatus && $encoderType) {
            $imgXL = Image::read($image);
            $imgXLData = (string)$imgXL->encode($encoderType);
            Storage::disk($disk)->put("$directory/{$filenameXL}", $imgXLData);
            $imgXL->destroy();
        } else {
            $filenameXL = $image->storeAs($directory, $filenameXL, $disk);
        }

        // نسخه متوسط (md)
        $filenameMd = $makeFilename('md');
        $imgMd = Image::read($image);
        $imgMd->resize((int)($width / 2), (int)($height / 2));
        $mdData = (string)$imgMd->encode($encoderType ?? $extension);
        Storage::disk($disk)->put("$directory/{$filenameMd}", $mdData);
        $imgMd->destroy();

        // نسخه کوچک (sm)
        $filenameSm = $makeFilename('sm');
        $imgSm = Image::read($image);
        $imgSm->resize((int)($width / 3), (int)($height / 3));
        $smData = (string)$imgSm->encode($encoderType ?? $extension);
        Storage::disk($disk)->put("$directory/{$filenameSm}", $smData);
        $imgSm->destroy();

        // نسخه بسیار کوچک (xs)
        $filenameXs = $makeFilename('xs');
        $imgXs = Image::read($image);
        $imgXs->resize((int)($width / 4), (int)($height / 4));
        $xsData = (string)$imgXs->encode($encoderType ?? $extension);
        Storage::disk($disk)->put("$directory/{$filenameXs}", $xsData);
        $imgXs->destroy();

        return [
            'xl' => "$directory/{$filenameXL}",
            'md' => "$directory/{$filenameMd}",
            'sm' => "$directory/{$filenameSm}",
            'xs' => "$directory/{$filenameXs}",
        ];
    }

    public function resizeWithCustomSizes(
        UploadedFile $image,
        array $sizes = null,
        string $directory = 'images',
        string $disk = 'public',
        ?string $overrideEncoder = null // مقدار دلخواه کاربر
    ): array {
        if ($sizes === null) {
            $sizes = config('laravel_image_resizer.sizes');
        }

        $extension = strtolower($image->getClientOriginalExtension());
        $name = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $filenameBase = Str::slug($name) . '_' . now()->timestamp;

        $encoderStatus = config('laravel_image_resizer.config.encoder_status');
        $encoderType = null;
        if ($encoderStatus) {
            $encoderType = $this->getEncoderType($extension, $overrideEncoder);
        }

        // ذخیره نسخه اصلی (original)
        $extensionToSave = $encoderType === 'jpeg' ? 'jpg' : ($encoderType ?? $extension);
        $filenameOriginal = "{$filenameBase}_original.{$extensionToSave}";
        if ($encoderStatus && $encoderType) {
            $imgOriginal = Image::read($image);
            $originalData = (string)$imgOriginal->encode($encoderType);
            Storage::disk($disk)->put("$directory/{$filenameOriginal}", $originalData);
            $imgOriginal->destroy();
        } else {
            $filenameOriginal = $image->storeAs($directory, $filenameOriginal, $disk);
        }

        $result = ['original' => "$directory/{$filenameOriginal}"];

        foreach ($sizes as $sizeName => $dimensions) {
            $width = $dimensions[0] ?? null;
            $height = $dimensions[1] ?? null;

            if (!$width || !$height) {
                continue;
            }

            $filename = "{$filenameBase}_{$sizeName}." . ($encoderType === 'jpeg' ? 'jpg' : ($encoderType ?? $extension));

            $img = Image::read($image);
            $img->resize($width, $height);
            $imageData = (string)$img->encode($encoderType ?? $extension);
            Storage::disk($disk)->put("$directory/{$filename}", $imageData);
            $img->destroy();

            $result[$sizeName] = "$directory/{$filename}";
        }

        return $result;
    }

    protected function getEncoderType(string $extension, ?string $customEncoder = null): string
    {
        $encoderType = strtolower($customEncoder ?? config('laravel_image_resizer.config.encoder'));

        return match ($encoderType) {
            'webp', 'png', 'jpg', 'jpeg' => $encoderType,
            default => 'jpeg',
        };
    }
}
