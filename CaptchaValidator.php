<?php


namespace haohetao\captcha;


class CaptchaValidator extends \yii\captcha\CaptchaValidator
{

    /**
     * {@inheritdoc}
     * @throws \yii\base\InvalidConfigException
     */
    protected function validateValue($value)
    {
        $captcha = $this->createCaptchaAction();
        $valid = !is_array($value) && $captcha->validate($value, false);

        return $valid ? null : [$this->message, []];
    }
}
