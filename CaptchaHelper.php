<?php

namespace haohetao\captcha;

use yii\captcha\CaptchaAction;
use yii\base\Exception;
use Yii;
use yii\web\Response;

class CaptchaHelper extends CaptchaAction
{
    /**
     * 显示数字验证码
     * @var bool
     */
    public $digit = true;

    /**
     * 可用数字集合
     * @var string
     */
    public $digits = "0123456789";

    /**
     * 可用字母集合
     * @var string
     */
    public $letters = "bcdfghjklmnpqrstvwxyz";

    /**
     * 可用元音字母
     * @var string
     */
    public $vowels = 'aeiou';
    private $code;

    /**
     * CaptchaHelper constructor.
     * @throws \yii\base\InvalidConfigException
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function generateImage(): string
    {
        $response = Yii::$app->getResponse();
        $imageData = $this->renderImage($this->generateCode());
        if (CaptchaHelper::isBase64()) {
            $response->format = Response::FORMAT_JSON;
            $response->content = "data:image/png;base64," . base64_encode($imageData);
        } else {
            $response->format = Response::FORMAT_RAW;
            $response->content = $imageData;
        }
        Yii::$app->cache->set($this->generateSessionKey($this->generateCode()), $this->generateCode(), 60);
        Yii::$app->end();
    }

    /**
     * 是否base64上传
     * @return bool
     */
    public static function isBase64()
    {
        $mime = Yii::$app->request->acceptableContentTypes;
        if (isset($mime['application/json']) ) {
            return true;
        }
        if (isset($mime['text/json']) ) {
            return true;
        }
        if (isset($mime['application/javascript']) ) {
            return true;
        }
        if (isset($mime['text/javascript']) ) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function generateCode(): string
    {
        if ($this->code) {
            return $this->code;
        }

        return $this->code = $this->generateVerifyCode();
    }

    /**
     * Generates a new verification code.
     * @return string the generated verification code
     */
    protected function generateVerifyCode()
    {
        if ($this->minLength > $this->maxLength) {
            $this->maxLength = $this->minLength;
        }
        if ($this->minLength < 3) {
            $this->minLength = 3;
        }
        if ($this->maxLength > 20) {
            $this->maxLength = 20;
        }
        $length = mt_rand($this->minLength, $this->maxLength);
        if ($this->digit) {
            $code = '';
            $digitsCount = strlen($this->digits) - 1;
            for ($i = 0; $i < $length; ++$i) {
                $code .= $this->digits[mt_rand(0, $digitsCount)];
            }
        } else {
            $this->letters = 'bcdfghjklmnpqrstvwxyz';
            $this->vowels = 'aeiou';
            $code = '';
            for ($i = 0; $i < $length; ++$i) {
                if ($i % 2 && mt_rand(0, 10) > 2 || !($i % 2) && mt_rand(0, 10) > 9) {
                    $code .= $this->vowels[mt_rand(0, 4)];
                } else {
                    $code .= $this->letters[mt_rand(0, 20)];
                }
            }
        }

        return $code;
    }

    /**
     * @param string $code
     * @return bool
     * @throws Exception
     */
    public function verify(string $code): bool
    {
        $verify = Yii::$app->cache->get($this->generateSessionKey($code));

        // 删除cache
        Yii::$app->cache->delete($this->generateSessionKey($code));

        if ($verify === $code) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    private function generateSessionKey(string $code): string
    {
        return base64_encode(Yii::$app->request->getRemoteIP() . Yii::$app->request->getUserAgent() . $code);
    }
}
