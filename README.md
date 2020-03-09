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
    public function actionCaptcha()
    {
        $captcha = new CaptchaHelper();
        $captcha->maxLength = 4;
        $captcha->minLength = 4;
        $captcha->generateImage();
    }
```

Use in HTML:

```html
<img src="<?= (new CaptchaHelper())->generateImage() ?>" />
<img src="http://baseUrl/site/captcha" />
```
Verify POST method captcha code:

```php
(new CaptchaHelper())->verify(\Yii::$app->request->post('code'));
```
