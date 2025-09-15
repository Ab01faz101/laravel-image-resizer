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
    public function resizeAndSave(
        UploadedFile $image,
        string $directory = 'images',
        string $disk = 'public',
        ?string $overrideEncoder = null
    ): array {
        $extension = strtolower($image->getClientOriginalExtension());
        $name = time() . '-' . pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);

// بدون تایم‌استمپ یا hash
        $filenameBase = Str::slug($name);

        $encoderStatus = config('laravel_image_resizer.config.encoder_status');
        $encoderObject = $encoderStatus ? $this->getEncoderType($extension, $overrideEncoder) : null;
        $extensionToSave = $this->getExtensionForSaving($encoderStatus, $overrideEncoder, $extension);

        $makeFilename = fn($size) => $filenameBase . "_{$size}." . $extensionToSave;

// Original (xl)
        $filenameXL = $makeFilename('xl');
        $imgXL = Image::read($image);
        $xlData = $this->encodeImage($imgXL, $encoderObject, $extension);
        Storage::disk($disk)->put("$directory/{$filenameXL}", $xlData);

// Medium (md)
        $filenameMd = $makeFilename('md');
        $imgMd = Image::read($image);
        $imgMd->resize((int)($imgMd->width() / 1.5), (int)($imgMd->height() / 1.5));
        $mdData = $this->encodeImage($imgMd, $encoderObject, $extension);
        Storage::disk($disk)->put("$directory/{$filenameMd}", $mdData);

// Small (sm)
        $filenameSm = $makeFilename('sm');
        $imgSm = Image::read($image);
        $imgSm->resize((int)($imgSm->width() / 2), (int)($imgSm->height() / 2));
        $smData = $this->encodeImage($imgSm, $encoderObject, $extension);
        Storage::disk($disk)->put("$directory/{$filenameSm}", $smData);

// Extra small (xs)
        $filenameXs = $makeFilename('xs');
        $imgXs = Image::read($image);
        $imgXs->resize((int)($imgXs->width() / 3), (int)($imgXs->height() / 3));
        $xsData = $this->encodeImage($imgXs, $encoderObject, $extension);
        Storage::disk($disk)->put("$directory/{$filenameXs}", $xsData);



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
        ?string $overrideEncoder = null
    ): array {
        $sizes = $sizes ?? config('laravel_image_resizer.sizes');

        $extension = strtolower($image->getClientOriginalExtension());
        $name = time() . '-' . pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $filenameBase = Str::slug($name);  // بدون timestamp

        $encoderStatus = config('laravel_image_resizer.config.encoder_status');
        $encoderObject = $encoderStatus ? $this->getEncoderType($extension, $overrideEncoder) : null;
        $extensionToSave = $this->getExtensionForSaving($encoderStatus, $overrideEncoder, $extension);

        $result = [];

        // Original
        $filenameOriginal = "{$filenameBase}_original.{$extensionToSave}";
        $imgOriginal = Image::read($image);
        $originalData = $this->encodeImage($imgOriginal, $encoderObject, $extension);
        Storage::disk($disk)->put("$directory/{$filenameOriginal}", $originalData);
        // $imgOriginal->destroy(); // حذف این خط (متد destroy در اینجا وجود ندارد)

        $result['original'] = "$directory/{$filenameOriginal}";

        foreach ($sizes as $sizeName => $dimensions) {
            [$width, $height] = $dimensions;

            if (!$width || !$height) continue;

            $filename = "{$filenameBase}_{$sizeName}.{$extensionToSave}";
            $img = Image::read($image);
            $img->resize($width, $height);
            $imageData = $this->encodeImage($img, $encoderObject, $extension);
            Storage::disk($disk)->put("$directory/{$filename}", $imageData);
            // $img->destroy(); // حذف این خط

            $result[$sizeName] = "$directory/{$filename}";
        }

        return $result;
    }


    protected function getEncoderType(string $extension, ?string $customEncoder = null): EncoderInterface
    {
        return match (strtolower($customEncoder ?? config('laravel_image_resizer.config.encoder'))) {
            'webp' => new WebpEncoder(),
            'png' => new PngEncoder(),
            'jpg', 'jpeg' => new JpegEncoder(),
            default => new JpegEncoder(),
        };
    }

    protected function getExtensionForSaving(bool $encoderStatus, ?string $customEncoder, string $defaultExtension): string
    {
        $ext = strtolower($customEncoder ?? config('laravel_image_resizer.config.encoder') ?? $defaultExtension);
        return $encoderStatus ? ($ext === 'jpeg' ? 'jpg' : $ext) : $defaultExtension;
    }

    protected function encodeImage($image, ?EncoderInterface $encoder = null, string $fallbackExtension = 'jpg'): string
    {
        return (string)($encoder ? $image->encode($encoder) : $image->encode($fallbackExtension));
    }
}
