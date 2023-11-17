<?php
/**
 * Author: Semen Dubina
 * Date: 19.01.16
 * Time: 15:34
 */

namespace khanifzyen\otp\behaviors;

use Yii;
use khanifzyen\otp\Otp;
use yii\base\Behavior;
use yii\db\BaseActiveRecord;


/**
 * Behavior for yii2-otp extension.
 *
 * For example:
 *
 * ```php
 * public function behaviors()
 * {
 *  return [
 *       'otp' => [
 *           'class' => OtpBehavior::className(),
 *           'component' => 'componentName',
 *           'window' => 0
 *       ],
 *  ];
 * }
 * ```
 *
 * @see https://en.wikipedia.org/wiki/Two-factor_authentication
 * @author khanifzyen
 * @package khanifzyen\otp
 */
class OtpBehavior extends Behavior
{
    /** @var string  */
    public $component = 'otp';

    /** @var string  */
    public $secretAttribute = 'secret';

    /** @var string  */
    public $codeAttribute = 'code';

    /** @var string  */
    public $countAttribute = 'count';

    /** @var int  */
    public $window = 0;

    /** @var Otp */
    private $otp = null;

    /** @var BaseActiveRecord */
    public $owner;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_INIT => 'initSecret',
            BaseActiveRecord::EVENT_BEFORE_VALIDATE => 'confirmSecret'
        ];
    }

    /**
     * Init secret attribute
     */
    public function initSecret()
    {
        $secret = $this->owner->{$this->secretAttribute};
        if (!empty($secret)) {
            $this->otp->setSecret($secret);
        }
    }

    /**
     * Confirm secret by code
     */
    public function confirmSecret()
    {
        $secret = $this->owner->{$this->secretAttribute};
        if (empty($secret)) {
            $this->owner->addError($this->codeAttribute, Yii::t('yii', 'The secret is empty.'));
        } else {
            $this->otp->setSecret($secret);
            if (!$this->secretConfirmed()) {
                $this->owner->addError($this->codeAttribute, Yii::t('yii', 'The code is incorrect.'));
            }
        }
    }

    public function init()
    {
        parent::init();
        $this->otp = Yii::$app->get($this->component);
    }

    private function secretConfirmed()
    {
        $code = $this->owner->{$this->codeAttribute};
        return $code !== null && $this->otp->valideteCode($code, $this->window);
    }
}
