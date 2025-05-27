📦 Laravel Image Resizer
========================

A simple and flexible Laravel package for resizing and encoding images into multiple sizes and formats, powered by [Intervention Image](http://image.intervention.io/).

* * *

✨ Features
----------

*   Resize images into multiple predefined sizes (XL, MD, SM, XS) or custom sizes.
*   Supports image encoding formats: `jpeg`, `jpg`, `png`, and `webp`.
*   Saves resized images to specified storage disk and directory.
*   Option to override encoder format dynamically.
*   Uses Laravel's filesystem and Intervention Image package under the hood.

* * *

🚀 Installation
---------------

1.  Require the package via Composer (assuming it’s published on Packagist):

    composer require ab01faz101/laravel-image-resizer

2.  (Optional) Publish the config file:

    php artisan vendor:publish --tag=laravel_image_resizer_config

3.  Configure `config/laravel_image_resizer.php` as needed (see Configuration section).

* * *

⚙️ Configuration
----------------

The package configuration file contains:

    return [
        'sizes' => [
            'lg' => [1200, 800],
            'md' => [600, 400],
            'sm' => [150, 150],
        ],
        'config' => [
            'encoder_status' => true,
            'encoder' => 'webp', // supported values: 'webp', 'png', 'jpeg', 'jpg'
        ],
    ];

*   `sizes`: Default sizes for resizing.
*   `encoder_status`: Enable or disable image encoding.
*   `encoder`: Default encoder format for output images.

* * *

📚 Usage
--------

Include the `LaravelImageResizer` trait in your Laravel class:

    use Ab01faz101\LaravelImageResizer\Traits\LaravelImageResizer;
    
    class YourClass
    {
        use LaravelImageResizer;
    
        // Your methods
    }


### 🖼️ Basic resize and save

    use Illuminate\Http\Request;
    
    public function uploadImage(Request $request)
    {
        $image = $request->file('image');
    
        $paths = $this->resizeAndSave(
            $image,
            'uploads/images', // target directory
            'public',         // storage disk
            'webp'            // optional encoder override
        );
    
        // $paths will be an array with keys: xl, md, sm, xs containing saved paths.
    }


### 🔧 Resize with custom sizes

    $sizes = [
        'large' => [1200, 800],
        'medium' => [600, 400],
        'small' => [300, 200],
    ];
    
    $paths = $this->resizeWithCustomSizes(
        $image,
        $sizes,
        'uploads/custom',
        'public',
        'png'
    );


* * *

📋 Methods
----------

Method

Description

Parameters

Returns

`resizeAndSave`

Resize the uploaded image into default sizes and save

`UploadedFile $image`, `string $directory`, `string $disk`, `?string $overrideEncoder`

Array of saved file paths

`resizeWithCustomSizes`

Resize the image to custom sizes and save

`UploadedFile $image`, `array $sizes`, `string $directory`, `string $disk`, `?string $overrideEncoder`

Array of saved file paths

* * *

🛠️ Requirements
----------------

*   PHP 7.4 or higher
*   Laravel 8 or higher
*   Intervention Image package (`intervention/image`)

* * *

📄 License
----------

MIT License

* * *

👨‍💻 Author
------------

Abolfazl Qaederahmat

* * *

⭐ If you find this package useful, please star the repository on [GitHub](https://github.com/abolfazlqaederahmat/laravel-image-resizer) ⭐

© 2025 Abolfazl Qaederahmat
