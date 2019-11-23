[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)

Easy uploader extension for yii2
================================
an easy way for make folders and upload images with one simple code line.

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

### Parameters

Add in your config file ( common/config/main.php ) for using in frontend and backend


```php
'components' => [
        'uploaders' => [
            'class' => 'gomonkey\uploader\Uploader',
            'baseFrontendUrl' =>   dirname(dirname(__DIR__)) . '/frontend/web/images',
            'baseBackendUrl' => dirname(dirname(__DIR__)) . '/backend/web/images',
            'rename' => true, // Rename file 
            'random' => 12, // random alphanumeric name
            'remove' => true, // Remove original file after upload
            'folders' => [
                [
                    'name' => '1200',
                    'quality' => 70,
                    'width' => 1200
                ],
                [
                    'name' => '800',
                    'quality' => 70,
                    'width' => 800
                ],
                [
                    'name' => '600',
                    'quality' => 70,
                    'width' => 600
                ],
                [
                    'name' => 'avatars',
                    'quality' => 70,
                    'width' => 200
                ]
             
            ]

        ],
    ],
```
if you use basic template, you can still use the same code above, just put the code in you config file and change baseFrontendUrl.
You can remove or comment baseBackendUrl

### Controllers

add UploadFile in your controller
```php
use yii\web\UploadedFile;
```

#### single image upload

In your controller action  :

```php
$upload = new Yii::$app->uploaders();

/**
If you want to use backend path:
$upload = new Yii::$app->uploaders("backend");
**/

$model->image =  $upload->upload(UploadedFile::getInstance($model, 'image'), "avatars");
```

$model->image now have the name of the uploaded image, ready to save it in database.

#### multiple uploads

```php
foreach (UploadedFile::getInstances($model, 'image') as $file) {
    $model->image = (new Yii::$app->uploaders())->upload($file, "avatars");
    
}
```

#### upload from url
Now it is possible to upload images from other website, using dynamic or static urls.
 the same code can be used in a loop
```php
 $model->image = $upload->uploadFromUrl("https://www.website.it/url/to/image/image.jpg",  "myFolder");
```
N.B. no need upload instance

#### infinite folders generation

You can make infinite folders.
For example with user id:
images/user/3/1200/imagename.jpg

```php
$model->image =  $upload->upload(UploadedFile::getInstance($model, 'image'), "users/".Yii::$app->user->id);
```

#### in your view

do not forget to use multipart/form-data to your form

```php
<?php $form = ActiveForm::begin([
    'options' => ['enctype'=>'multipart/form-data']
]); ?>
```

#### Delete images from folder

If you need to delete one or more images from all folders:

( new Yii::$app->uploaders() )->delete( file, folder );
 
for example:
```php
 (new Yii::$app->uploaders())->delete($model->name, "/products/".Yii::$app->user->id);
```
It remove all images in your path/products/user id/others setted folder/file

### Paremeters 


---

#### rename (Type: `boolean`, Default value: `true`)

Will rename your uploaded file, set to false if you don't want to change the file name
 
---

#### remove (Type: `boolean`, Default value: `true`)

Remove the original file

---

#### random (Type: `integer`, Default value: 10)

Random is the length of the alphanumeric image name

---

#### baseFrontendUrl ( Type: `string`)

Your frontend ( or web path for basic template ) path to image folder

---

#### baseBackendUrl ( Type: `string`)

Your backend path to image folder

---

#### folders ( Type: `array`)

The folders are not the primary, these must be set in controller
- name[] = is the name of the folder
- quality[] = is the quality of the uploaded image
- width[] = is the width of the image, the height will be scaled

[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)





