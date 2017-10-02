<?php

namespace gomonkey\uploader;

/**
 * This is just an example.
 */
class Uploader extends \yii\base\Widget
{
    /* Strings */
    public $baseFrontendUrl = "";
    public $baseBackenddUrl = "";

    /* Booleans */
    public $rename = false;
    public $remove = false;

    /* arrays */
    public $folder = [];

    public function upload($image, $folder){

        if( $image ) {

            $this->folders($folder);

            if(Yii::$app->uploaders->rename) {
                $ext = explode( ".", $image->name );
                $image->name = Yii::$app->security->generateRandomString( 12 ) . ".{$ext[1]}";
            }

            $image->saveAs($imageLocation = Yii::$app->uploaders->baseFrontendUrl."/".$folder  ."/" . $image->name);

            foreach(Yii::$app->uploaders->folder as $f){

                $this->doResize($imageLocation, $f["name"], [
                    'quality' => $f["quality"],
                    'width' => $f["width"],
                ]);

                $imageLocation = Yii::$app->uploaders->baseFrontendUrl."/".$folder  ."/".$f['name']."/" . $image->name;

            }

        }

    }

    private function folders( $folder ){


        if( !file_exists( Yii::$app->uploaders->baseUrl."/".$folder ) ){
            mkdir(Yii::$app->uploaders->baseUrl."/".$folder, 0777, true);

            foreach(Yii::$app->uploaders->folder as $f)
                mkdir(Yii::$app->uploaders->baseUrl."/".$folder ."/".$f['name'] , 0777, true);
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

        //$color = (new RGB())->color('#FFF', 100);

        return Image::thumbnail(
            $imageLocation,
            (int) $newWidth,
            (int) $newHeight,
            $color
        )->save(
            $imageDestination,
            [
                'quality' => isset($options['quality']) ? $options['quality'] : 100,

            ]
        );
    }

}
