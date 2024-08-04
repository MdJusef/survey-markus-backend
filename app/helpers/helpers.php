<?php
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Illuminate\Http\UploadedFile;

function saveImage($request, $type)
{
    $directoryMap = [
        'image' => 'adminAsset/image/',
        'id_card' => 'adminAsset/id_card/',
        'cover-image' => 'adminAsset/cover-image/',
        'category_image' => 'adminAsset/category_image/',
        'service_image' => 'adminAsset/service_image/',
        'product_image' => 'adminAsset/product_image/',
        'slider_image' => 'adminAsset/slider_image/',
    ];

    if ($request->file($type)) {
        $image = $request->file($type);
        $imageName = rand() . '.' . $image->getClientOriginalExtension();
        $directory = $directoryMap[$type];

        // Ensure the directory exists
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $imgUrl = $directory . $imageName;

        try {
            $image->move($directory, $imageName);
            return $imgUrl;
        } catch (FileException $e) {
            Log::error("File upload error: " . $e->getMessage());
            throw new \Exception("Error uploading file: " . $e->getMessage());
        }
    }

    return null;
}


function removeImage($imagePath)
{
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
}
