<?php
/**
 * @link      https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license   https://craftcms.com/license
 */

namespace craft\models;

use Craft;
use craft\base\Model;

/**
 * MailSettings Model class.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since  3.0
 */
class MailSettings extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var string The default email address that emails should be sent from
     */
    public $fromEmail;

    /**
     * @var string The default name that emails should be sent from
     */
    public $fromName;

    /**
     * @var string The template that emails should be sent with
     */
    public $template;

    /**
     * @var string The transport type that should be used
     */
    public $transportType;

    /**
     * @var array The transport type’s settings
     */
    public $transportSettings;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'fromEmail' => Craft::t('app', 'System Email Address'),
            'fromName' => Craft::t('app', 'Sender Name'),
            'template' => Craft::t('app', 'HTML Email Template'),
            'transportType' => Craft::t('app', 'Transport Type'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fromEmail', 'fromName', 'transportType'], 'required'],
            [['fromEmail'], 'email'],
        ];
    }
}
