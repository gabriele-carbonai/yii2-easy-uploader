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
    /* Strings */
    public $baseFrontendUrl = "";
    public $baseBackendUrl = "";

    /* Booleans */
    public $rename = false;
    public $remove = false;

    private $baseUrl;
    /* arrays */
    public $folders = [];

    public function __construct($base = "frontend")
    {
        $this->baseUrl = $base;

    }

    public function upload($image, $folder){

        if( $image ) {

            if( $this->baseUrl == "frontend" )
                $this->baseUrl = Yii::$app->uploaders->baseFrontendUrl;
            else
                $this->baseUrl = Yii::$app->uploaders->baseBackendUrl;


            $this->folders($folder);

            if(Yii::$app->uploaders->rename) {
                $ext = explode( ".", $image->name );
                $image->name = Yii::$app->security->generateRandomString( 12 ) . ".{$ext[1]}";
            }

            $image->saveAs($imageLocation = $this->baseUrl."/".$folder  ."/" . $image->name);


            foreach(Yii::$app->uploaders->folders as $f){

                // Check if there are new folder in array
                $this->isFolderExist($this->baseUrl."/".$folder  ."/".$f['name']."/"  );
                
                $this->doResize($imageLocation,  $imageLocation = $this->baseUrl."/".$folder  ."/".$f['name']."/" . $image->name, [
                    'quality' => $f["quality"],
                    'width' => $f["width"],
                ]);


            }

            if( Yii::$app->uploaders->remove ){
                unlink($this->baseUrl."/".$folder  ."/" . $image->name);
            }

            return $image->name;

        }

    }

    /**
     * @param $folder
     *
     * Create folders if not exists
     */
    private function folders( $folder ){


        if( !file_exists( $this->baseUrl."/".$folder ) ){
            mkdir($this->baseUrl."/".$folder, 0777, true);

            foreach(Yii::$app->uploaders->folders as $f)
                mkdir($this->baseUrl."/".$folder ."/".$f['name'] , 0777, true);
        }

    }

    /**
     * @param $folder
     *
     * In that case array folders is changed
     */
    private function isFolderExist( $folder ){

        if( !file_exists( $folder ) ){
            mkdir($folder, 0777, true);
        }

    }

    public function doResize($imageLocation, $imageDestination, Array $options = null)
    {

        $newWidth = $newHeight = 0;
        list($width, $height) = getimagesize($imageLocation);

        if(isset($options['width']) || isset($options['height']))
        {
            if(isset($options['width']) && isset($options['height']))
            {
                $newWidth = $options['width'];
                $newHeight = $options['width'];
            }

            else if(isset($options['width']))
            {
                $deviationPercentage = (($width - $options['width']) / (0.01 * $width)) / 100;

                $newWidth = $options['width'];
                $newHeight = $height - ($height * $deviationPercentage);
            }

            else
            {
                $deviationPercentage = (($height - $options['height']) / (0.01 * $height)) / 100;

                $newWidth = $width - ($width * $deviationPercentage);
                $newHeight = $options['height'];
            }
        }

        else
        {
            // reduce image size up to 20% by default
            $reduceRatio = isset($options['reduceRatio']) ? $options['reduceRatio'] : 20;

            $newWidth = $width * ((100 - $reduceRatio) / 100);
            $newHeight = $height * ((100 - $reduceRatio) / 100);
        }


        return Image::thumbnail(
            $imageLocation,
            (int) $newWidth,
            (int) $newHeight
        )->save(
            $imageDestination,
            [
                'quality' => isset($options['quality']) ? $options['quality'] : 100,

            ]
        );
    }

}