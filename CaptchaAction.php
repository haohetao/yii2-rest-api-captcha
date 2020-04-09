<?php

namespace haohetao\captcha;

use yii\base\Exception;
use Yii;
use yii\web\Response;

class CaptchaAction extends \yii\captcha\CaptchaAction
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
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function run()
    {
        $response = Yii::$app->getResponse();
        $imageData = $this->renderImage($this->getVerifyCode());
        if (CaptchaAction::isBase64()) {
            $response->format = Response::FORMAT_JSON;
            $content = "data:image/png;base64," . base64_encode($imageData);
        } else {
            $response->format = Response::FORMAT_RAW;
            $response->getHeaders()
                ->set('Pragma', 'public')
                ->set('Expires', '0')
                ->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                ->set('Content-Transfer-Encoding', 'binary')
                ->set('Content-type', 'image/png');
            $content = $imageData;
        }
        Yii::$app->cache->set($this->generateSessionKey($this->getVerifyCode()), $this->getVerifyCode(), 600);
        return $content;
    }

    /**
     * 是否base64上传
     * @return bool
     */
    public static function isBase64()
    {
        $mime = Yii::$app->request->getAcceptableContentTypes();
        if (isset($mime['application/json'])) {
            return true;
        }
        if (isset($mime['text/json'])) {
            return true;
        }
        if (isset($mime['application/javascript'])) {
            return true;
        }
        if (isset($mime['text/javascript'])) {
            return true;
        }
        return false;
    }

    /**
     * Gets the verification code.
     * @param bool $regenerate 在Rest中这一项不生效
     * @return string the verification code.
     */
    public function getVerifyCode($regenerate = true): string
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
     * Validates the input to see if it matches the generated code.
     * @param string $input user input
     * @param bool $caseSensitive 在Rest中这一项没有意义，验证码只有小写
     * @return bool whether the input is valid
     */
    public function validate($input, $caseSensitive = false): bool
    {
        if ($caseSensitive) {
            $code = strtolower ($input);
        } else {
            $code = $input;
        }
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
