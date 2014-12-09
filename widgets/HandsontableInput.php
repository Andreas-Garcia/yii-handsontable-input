<?php

namespace neam\yii_handsontable_input\widgets;

use Yii;
use CHtml;
use CInputWidget;
use CClientScript;
use yii\base\Widget;
use yii\helpers\Json;
use yii\web\JsExpression;
use himiklab\handsontable\HandsontableWidget;

/**
 * Handsontable grid input widget for Yii1.
 * The input is a hidden input that stores the JSON-encoded string representation of the grid's data.
 *
 * Use in a Yii 1 app as follows:
 *
 * ```php
 * $this->widget('\neam\yii_handsontable_input\widgets\HandsontableInput',[
 *  'model' => $model,
 *  'attribute' => 'foo',
 *  'settings' => [
 *    'colHeaders' => true,
 *    'rowHeaders' => true,
 *  ]
 * ]);
 * ```
 *
 * Or:
 * ```php
 * $this->widget('\neam\yii_handsontable_input\widgets\HandsontableInput',[
 *  'id' => 'foo',
 *  'value' => [
 *          ['A1', 'B1', 'C1'],
 *          ['A2', 'B2', 'C2'],
 *  ],
 *  'settings' => [
 *    'colHeaders' => true,
 *    'rowHeaders' => true,
 *  ]
 * ]);
 * ```
 */
class HandsontableInput extends CInputWidget
{

    /**
     * Required by CInputWidget
     * @var
     */
    public $options;

    protected $jsWidget;

    /**
     * @var string $settings
     * @see https://github.com/handsontable/jquery-handsontable/wiki
     */
    public $settings = [];

    public function init()
    {
        parent::init();
        list($name, $id) = $this->resolveNameID();
        $this->settings = array_merge($this->settings, [
            "updateParentHandsontableInput" => $id,
        ]);

        if ($this->hasModel()) {
            $value = $this->model->{$this->attribute};
        } else {
            $value = $this->value;
        }
        if (empty($value)) {
            $value = json_encode([]);
        }

        $this->settings["data"] = json_decode($value);

        $script = "Handsontable.PluginHooks.add('afterChange', function() {
          if(this.getSettings().updateParentHandsontableInput) {
            var id = this.getSettings().updateParentHandsontableInput;
            var dataObj = {
                dataSchema: this.getSettings().dataSchema,
                data: this.getData()
            };
            document.getElementById(id).value = JSON.stringify(dataObj);
          }
        });";
        $clientScript = Yii::app()->getClientScript();
        $clientScript->registerScript(uniqid(), $script, CClientScript::POS_READY);

    }

    public function run()
    {
        $this->jsWidget = HandsontableWidget::begin(['settings' => $this->settings]);
        $this->jsWidget->end();
        echo $this->renderInput();
    }

    /**
     * Render the text area input
     */
    protected function renderInput()
    {
        if ($this->hasModel()) {
            $input = CHtml::activeHiddenField($this->model, $this->attribute, $this->options);
        } else {
            $input = CHtml::hiddenField($this->name, $this->value, $this->options);
        }

        return $input;
    }

}
