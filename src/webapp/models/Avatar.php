<?php

namespace tdt4237\webapp\models;

class Avatar {

    const AVATAR_PATH = "/images/avatars/";
    const MAX_FILE_SIZE = 5000000; // 5 MB

    protected $avatar = 'aa.jpg';

    function __construct() {
        
    }

    function getAvatar() {
        return self::AVATAR_PATH . $this->avatar;
    }

    function setAvatar($avatar) {
        $this->avatar = $avatar;
    }

    function upload($user) {
        ini_set('post_max_size', '1M');
        ini_set('upload_max_filesize', '1M');
        ini_set('max_file_uploads', 1);

        $file = $_FILES['avatar'];

        if (!isset($file)) {
            echo "No files uploaded";
            return;
        }

        if (count($_FILES) > 1) {
            trigger_error('Too many files!', E_USER_WARNING);
            return;
        }

        if (count($_FILES) == 1) {
            if ($file['size'] > self::MAX_FILE_SIZE) {
                trigger_error('File size too big!', E_USER_WARNING);
                return;
            }

            $fileTemp = $file['tmp_name'];

            $imageExtensions = array('jpg', 'png', 'gif');

            $fileName = $file['name'];
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

            $newFileName = strtolower($user) . ".jpg";
            $filePath = "web" . self::AVATAR_PATH . $newFileName;

            $imageError = false;
            if ((list($width, $height, $imageType) = getimagesize($fileTemp)) === FALSE) {
                switch ($imageType) {
                    case IMAGETYPE_GIF :
                        if (!$img = @imagecreatefromgif($fileTemp)) {
                            trigger_error('Not a GIF image!', E_USER_WARNING);
                            $imageError = true;
                        }
                        break;
                    case IMAGETYPE_JPEG :
                        if (!$img = @imagecreatefromjpeg($fileTemp)) {
                            trigger_error('Not a JPEG image!', E_USER_WARNING);
                            $imageError = true;
                        }
                        break;
                    case IMAGETYPE_PNG :
                        if (!$img = @imagecreatefrompng($fileTemp)) {
                            trigger_error('Not a PNG image!', E_USER_WARNING);
                            $imageError = true;
                        }
                        break;
                    default :
                        $imageError = true;
                        trigger_error('The file is not an image!', E_USER_WARNING);
                }
            }

            if ($imageError) {
                trigger_error('The file is not an image!', E_USER_WARNING);
                return;
            }

            // File is a valid image -> Create new image and upload it.
            if (in_array($fileExtension, $imageExtensions)) {
                switch (exif_imagetype($fileTemp)) {
                    case IMAGETYPE_GIF:
                        $image = imagecreatefromgif($fileTemp);
                        break;
                    case IMAGETYPE_JPEG:
                        $image = imagecreatefromjpeg($fileTemp);
                        break;
                    case IMAGETYPE_PNG:
                        $image = imagecreatefrompng($fileTemp);
                        break;
                    default:
                        throw new Exception('Invalid file type');
                }

                $tempImage = imagecreatetruecolor($width, $height);
                imagecopy($tempImage, $image, 0, 0, 0, 0, $width, $height);

                // Convert image to jpg
                imagejpeg($tempImage, $filePath, 100);
                // Free up memory
                imagedestroy($tempImage);

                $noExecMode = 0644;
                chmod($filePath, $noExecMode);
                $this->setAvatar($newFileName);
            }
        }
    }

}

?>
