# yii2-rest-api-captcha
Simple captcha image generator for restful api 22

## Installation

Recommended installation via [composer](http://getcomposer.org/download/):

```
composer require haohetao/yii2-rest-api-captcha
```

## Usage

Generate captcha code (image/png;base64):

```php
    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'haohetao\captcha\CaptchaAction',
                'maxLength' => 4,
                'minLength' => 4
            ]
        ];
    }
```
use in curl(for test):
```shell
curl http://localhost/site/captcha
```
Verify captcha code:

```php
    public function rules()
    {
        return [
            [
                'code', \haohetao\captcha\CaptchaValidator::class
            ]
        ];
    }
```
