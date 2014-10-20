<?php

namespace tdt4237\webapp\models;

class Avatar {

    const AVATAR_PATH = "/images/avatars/";

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
        ini_set('post_max_size', '40M');
        ini_set('upload_max_filesize', '40M');
        ini_set('max_file_uploads', 1);

        $file = $_FILES['avatar'];

        if (!isset($file)) {
            echo "No files uploaded";
            return;
        }

        if (count($_FILES) > 1) {
            echo "Error in files length";
            return;
        }

        $fileTemp = $file['tmp_name'];

        $imageExtensions = array('jpg', 'png', 'gif');

        $fileName = $file['name'];
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        $newFileName = strtolower($user) . ".jpg";
        $filePath = "web" . self::AVATAR_PATH . $newFileName;

        $imageError = false;
        if (($fileImageInfo = getimagesize($fileTemp)) === FALSE)
            switch ($fileImageInfo[2]) {
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
                    die("The file is not an image!");
            }

        if ($imageError) {
            echo "The file is not a valid image!";
            return;
        }


        // File is a valid image -> Upload it
        if (in_array($fileExtension, $imageExtensions)) {
            if (move_uploaded_file($fileTemp, $filePath)) {

                $noExecMode = 0644;
                chmod($filePath, $noExecMode);
                $this->setAvatar($newFileName);
//                try {
//                    $img = imagecreatefromjpeg($filePath);
//                    imagejpeg($img, $filePath, 100);
//                    
//                } catch (Exception $exception) {
//                    echo "Image file not a valid image";
//                    return;
//                }
            }
        }
    }

}

?>
