Easy uploader extension for yii2
================================
an easy way for make folders and upload images or file everywhere 

Installation
------------

The preferred way to install this extension is through [composer](../../../../web/index.phpdownload/).

Either run

```
php composer.phar require  gomonkey/yii2-easy-uploader "*"

```

or add

```
"gomonkey/yii2-easy-uploader": "*"
```

to the require section of your `composer.json` file.


Usage 
-----

Add in your config file 
( common/config/main.php )


```php
'components' => [
        'uploaders' => [
            'class' => 'common\components\Upload',
            'baseFrontendUrl' => '/add_your_path/frontend/web/images',
            'baseBackendUrl' => '/add_your_path/backend/web/images',
            'rename' => true,
            'remove' => true, // Remove the original file
            'folders' => [
                [
                    'name' => '1200',
                    'quality' => '70',
                    'width' => 1200
                ],
                [
                    'name' => '800',
                    'quality' => '70',
                    'width' => '800'
                ],
                [
                    'name' => '600',
                    'quality' => '70',
                    'width' => '600'
                ]
            ]

        ],
    ],
```
if you use basic template, you can still use the same code above, just put the code in you config file and change baseFrontendUrl.
You can remove or comment baseBackendUrl

In your controller action  :

```php
$upload = new Yii::$app->uploaders();


/**
If you want to use backend path:

$upload = new Yii::$app->uploaders("backend");
**/


$model->image =  $upload->upload( UploadedFile::getInstance($model, 'image'), "avatars" );
```

$model->image will have now the name of the uploaded image.




#Paremeters 


---

#### rename (Type: `boolean`, Default value: `true`)

Will rename your uploaded file, set to false if you don't want to change the file name
 
---

#### remove (Type: `boolean`, Default value: `true`)

Remove the original file

---

#### baseFrontendUrl ( Type: `string`)

Your frontend ( or web path for basic template ) path to image folder

---

#### baseBackendUrl ( Type: `string`)

Your backend path to image folder

---

#### folders ( Type: `array`)

The folders are not the primary.
name[] = is the name of the folder
quelity[] = is the quality of the uploaded image
with[] = is the with of the image, the height will be scaled




