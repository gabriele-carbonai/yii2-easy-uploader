<?php
/**
 * Created by PhpStorm.
 * User: gabrielecarbonai
 * Date: 02/10/17
 * Time: 22:07
 */

namespace gomonkey\uploader;

use yii;
use yii\imagine\Image;

class Uploader
{
    /* integers */
    public $random = 10;

    /* Strings */
    public $baseFrontendUrl = "";
    public $baseBackendUrl = "";
    private $baseUrl;

    /* Booleans */
    public $rename = false;
    public $remove = false;

    /* arrays */
    public $folders = [];

    public function __construct($base = "frontend")
    {
        $this->baseUrl = $base;
    }

    /**
     * @param $image
     * @param $folder
     *
     * @return string
     * @throws
     */
    public function upload($image, $folder)
    {
        if (!$image) {
            return false;
        }

        $this->baseUrl = ($this->baseUrl == "frontend") ? Yii::$app->uploaders->baseFrontendUrl : Yii::$app->uploaders->baseBackendUrl;
        $this->folders($folder);

        if (Yii::$app->uploaders->rename) {
            $ext = substr($image->name, strrpos($image->name, '.') + 1);
            $image->name = Yii::$app->security->generateRandomString(Yii::$app->uploaders->random) . ".{$ext}";
        }

        $image->saveAs($imageLocation = $this->baseUrl . "/" . $folder . "/" . $image->name);

        foreach (Yii::$app->uploaders->folders as $f) {
            // Check if there are new folder in array
            $this->isFolderExist($this->baseUrl . "/" . $folder . "/" . $f['name'] . "/");

            $this->doResize($imageLocation, $this->baseUrl . "/" . $folder . "/" . $f['name'] . "/" . $image->name,
                [
                    'quality' => $f["quality"],
                    'width' => $f["width"],
                ]);
        }

        if (Yii::$app->uploaders->remove) {
            unlink($this->baseUrl . "/" . $folder . "/" . $image->name);
        }

        return $image->name;
    }

    /**
     * @param $urlImage
     * @param $folder
     *
     * @return string
     * @throws
     */
    public function uploadFromUrl($urlImage, $folder)
    {

        $this->baseUrl = ($this->baseUrl == "frontend") ? Yii::$app->uploaders->baseFrontendUrl : Yii::$app->uploaders->baseBackendUrl;
        $this->folders($folder);

        $imageLocation = $this->baseUrl . "/" . $folder . "/" . array_slice(explode('/', $urlImage), -1)[0];

        copy($urlImage, $imageLocation);

        $image = array_slice(explode('/', $urlImage), -1)[0];

        if (Yii::$app->uploaders->rename) {
            $ext = substr($image, strrpos($image, '.') + 1);
            $image = Yii::$app->security->generateRandomString(Yii::$app->uploaders->random) . ".{$ext}";
        }

        foreach (Yii::$app->uploaders->folders as $f) {
            // Check if there are new folder in array
            $this->isFolderExist($this->baseUrl . "/" . $folder . "/" . $f['name'] . "/");

            $this->doResize($imageLocation, $this->baseUrl . "/" . $folder . "/" . $f['name'] . "/" . $image,
                [
                    'quality' => $f["quality"],
                    'width' => $f["width"],
                ]);
        }

        if (file_exists($this->baseUrl . "/" . $folder . "/" . $image) && Yii::$app->uploaders->remove) {
            unlink($this->baseUrl . "/" . $folder . "/" . $image);
        }

        return $image;
    }

    /**
     * @param $image
     * @param $folder
     */
    public function delete($image, $folder)
    {
        $this->baseUrl = ($this->baseUrl == "frontend") ? Yii::$app->uploaders->baseFrontendUrl : Yii::$app->uploaders->baseBackendUrl;

        if (!empty(Yii::$app->uploaders)) {
            foreach (Yii::$app->uploaders->folders as $f) {
                unlink($this->baseUrl . "/" . $folder . "/" . $f["name"] . "/" . $image);
            }
        }
    }

    /**
     * @param $folder
     *
     * Create folders if not exists
     */
    private function folders($folder)
    {
        if (!file_exists($this->baseUrl . "/" . $folder)) {
            mkdir($this->baseUrl. "/" . $folder, 0775, true);
            foreach (Yii::$app->uploaders->folders as $f) {
                mkdir($this->baseUrl . "/" . $folder . "/" . $f['name'], 0775, true);
            }
        }
    }

    /**
     * @param $folder
     *
     * In that case array folders is changed
     */
    private function isFolderExist($folder)
    {
        if (!file_exists($folder)) {
            mkdir($this->baseUrl . "/" . $folder, 0775, true);
        }
    }

    public function doResize($imageLocation, $imageDestination, Array $options = null)
    {
        list($width, $height) = @getimagesize($imageLocation);

        if (!$width) {
            return false;
        }

        if (isset($options['width']) || isset($options['height'])) {
            if (isset($options['width']) && isset($options['height'])) {
                $newWidth = $options['width'];
                $newHeight = $options['width'];
            } else if (isset($options['width'])) {
                $deviationPercentage = (($width - $options['width']) / (0.01 * $width)) / 100;
                $newWidth = $options['width'];
                $newHeight = $height - ($height * $deviationPercentage);
            } else {
                $deviationPercentage = (($height - $options['height']) / (0.01 * $height)) / 100;
                $newWidth = $width - ($width * $deviationPercentage);
                $newHeight = $options['height'];
            }
        } else {
            // reduce image size up to 20% by default
            $reduceRatio = isset($options['reduceRatio']) ? $options['reduceRatio'] : 20;
            $newWidth = $width * ((100 - $reduceRatio) / 100);
            $newHeight = $height * ((100 - $reduceRatio) / 100);
        }

        return Image::thumbnail($imageLocation, (int)$newWidth, (int)$newHeight)->save($imageDestination,
            [
                'quality' => isset($options['quality']) ? $options['quality'] : 100,
            ]
        );
    }
}
