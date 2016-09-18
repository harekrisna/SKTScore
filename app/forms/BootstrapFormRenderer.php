<?php
namespace App\Forms;
use Nette\Forms\Rendering\DefaultFormRenderer;
use Nette\Forms\Controls;
use Nette\Forms\Form;
use Nette;

class BootstrapFormRenderer extends DefaultFormRenderer   {
    /** @var Controls\Button */
    public $primaryButton = NULL;
    /** @var bool */
    private $controlsInit = FALSE;

    public function __construct() {
        $this->wrappers['controls']['container'] = NULL;
        $this->wrappers['pair']['container'] = 'div class=form-group';
        $this->wrappers['pair']['.error'] = 'has-error';
        $this->wrappers['control']['container'] = 'div class=col-sm-10';
        $this->wrappers['label']['container'] = 'div class="col-sm-2 control-label"';
        $this->wrappers['control']['description'] = 'span class=help-block';
        $this->wrappers['control']['errorcontainer'] = 'span class=help-block';
    }

    public function renderBegin() {
        $this->controlsInit();
        return parent::renderBegin();
    }

    public function renderEnd() {
        $this->controlsInit();
        return parent::renderEnd();
    }

    public function renderBody() {
        $this->controlsInit();
        return parent::renderBody();
    }

    public function renderControls($parent) {
        $this->controlsInit();
        return parent::renderControls($parent);
    }

    public function renderPair(Nette\Forms\IControl $control) {
        $this->controlsInit();
        return parent::renderPair($control);
    }

    public function renderPairMulti(array $controls) {
        $this->controlsInit();
        return parent::renderPairMulti($controls);
    }

    public function renderLabel(Nette\Forms\IControl $control) {
        $this->controlsInit();
        return parent::renderLabel($control);
    }

    public function renderControl(Nette\Forms\IControl $control) {
        $this->controlsInit();
        return parent::renderControl($control);
    }

    private function controlsInit() {
        if ($this->controlsInit) {
            return;
        }

        $this->controlsInit = TRUE;
        $this->form->getElementPrototype()->addClass('form-horizontal');

        foreach ($this->form->getControls() as $control) {
            if ($control instanceof Controls\Button || $control instanceof Controls\SubmitButton) {
                $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
                $usedPrimary = TRUE;
            } elseif ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox || $control instanceof Controls\UploadControl) {
                $control->getControlPrototype()->addClass('form-control upload-control');
            } elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
                $control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
            }
        }

    }
}