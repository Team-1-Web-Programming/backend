<?php

namespace App\Http\Helpers;

trait BaseFunction
{
    public function getFileUploadedResize($path, $size)
    {
        $binary = file_get_contents($path);

        $imageSize = getimagesizefromstring($binary);

        $imageType = $imageSize['mime'];

        $imageWidth = $imageSize[0];
        $imageHeight = $imageSize[1];

        if ($imageType == IMAGETYPE_JPEG) {
            $imageOrientation = exif_read_data($path);
            $imageOrientation = isset($imageOrientation['Orientation']) ? $imageOrientation['Orientation'] : 1;
        } else {
            $imageOrientation = 0;
        }

        // get image resize
        $imageResize = imagecreatetruecolor($size, $size);
        imagecopyresampled($imageResize, $imageType == IMAGETYPE_JPEG ? $imageType : imagecreatefromstring($binary), $imageOrientation == 6 || $imageOrientation == 8 ? 0 : 1, 0, 0, 0, $size, $size, $imageWidth, $imageHeight);

        // get image resize binary
        ob_start();
        imagejpeg($imageResize);
        $imageResizeBinary = ob_get_contents();
        ob_end_clean();

        $base64 = base64_encode($imageResizeBinary);

        return "data:$imageType;base64,$base64";
    }
}