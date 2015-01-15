<?php
/**
 * @link http://buildwithcraft.com/
 * @copyright Copyright (c) 2013 Pixel & Tonic, Inc.
 * @license http://buildwithcraft.com/license
 */

namespace craft\app\fieldtypes;

use Craft;
use craft\app\enums\AttributeType;
use craft\app\enums\ColumnType;
use craft\app\helpers\DbHelper;

/**
 * PlainText fieldtype
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.0
 */
class PlainText extends BaseFieldType
{
	// Public Methods
	// =========================================================================

	/**
	 * @inheritDoc ComponentTypeInterface::getName()
	 *
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Plain Text');
	}

	/**
	 * @inheritDoc SavableComponentTypeInterface::getSettingsHtml()
	 *
	 * @return string|null
	 */
	public function getSettingsHtml()
	{
		return Craft::$app->templates->render('_components/fieldtypes/PlainText/settings', [
			'settings' => $this->getSettings()
		]);
	}

	/**
	 * @inheritDoc FieldTypeInterface::defineContentAttribute()
	 *
	 * @return mixed
	 */
	public function defineContentAttribute()
	{
		$maxLength = $this->getSettings()->maxLength;

		if (!$maxLength)
		{
			$columnType = ColumnType::Text;
		}
		else
		{
			$columnType = DbHelper::getTextualColumnTypeByContentLength($maxLength);
		}

		return [AttributeType::String, 'column' => $columnType, 'maxLength' => $maxLength];
	}

	/**
	 * @inheritDoc FieldTypeInterface::getInputHtml()
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return string
	 */
	public function getInputHtml($name, $value)
	{
		return Craft::$app->templates->render('_components/fieldtypes/PlainText/input', [
			'name'     => $name,
			'value'    => $value,
			'settings' => $this->getSettings()
		]);
	}

	// Protected Methods
	// =========================================================================

	/**
	 * @inheritDoc BaseSavableComponentType::defineSettings()
	 *
	 * @return array
	 */
	protected function defineSettings()
	{
		return [
			'placeholder'   => [AttributeType::String],
			'multiline'     => [AttributeType::Bool],
			'initialRows'   => [AttributeType::Number, 'min' => 1, 'default' => 4],
			'maxLength'     => [AttributeType::Number, 'min' => 0],
		];
	}
}