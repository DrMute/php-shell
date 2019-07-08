<?php
function imagecropper($source_path,$target_height)
{
$source_info = getimagesize($source_path);
$source_width = $source_info[0];
$source_height = $source_info[1];
$source_mime = $source_info['mime'];



    $cropped_width = $source_width;
    $cropped_height = $source_height-$target_height;
    $source_x = 0;
    $source_y = 0;



switch ($source_mime)
{
case 'image/gif':
$source_image = imagecreatefromgif($source_path);
break;

case 'image/jpeg':
$source_image = imagecreatefromjpeg($source_path);
break;

case 'image/png':
$source_image = imagecreatefrompng($source_path);
break;

default:
return false;
break;
}


$cropped_image = imagecreatetruecolor($cropped_width, $cropped_height);

// 裁剪
imagecopy($cropped_image, $source_image, 0, 0, $source_x, $source_y, $cropped_width, $cropped_height);



imagepng($cropped_image,'./'.$source_path);
imagedestroy($cropped_image);


}


imagecropper('./5bb1a53d96bf9.jpg',38);


