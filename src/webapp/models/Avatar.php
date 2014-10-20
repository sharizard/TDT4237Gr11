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

        $imageTypes = array('jpg', 'png', 'gif');
        $fileName = $file['name'];
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        $newFileName = strtolower($user) . ".jpg";
        $filePath = "web" . self::AVATAR_PATH . $newFileName;

        // File is a valid image -> Upload it
        if (in_array($fileExtension, $imageTypes)) {
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
