<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI\Form;
use Tracy\Debugger;
use Nette\Forms\Controls;
use App\Forms\ChooseCentersFormFactory;


class ResultSettingsPresenter extends BasePresenter {
    /** @var ChooseCentersFormFactory @inject */
    public $chooseCentersFormFactory;

    public function renderShowCenters() {
        $centers_group = $this->center->findAll()->group('country_id');
        $this->template->centers_group = $centers_group;
        $selected_centers = [];

        $selected_centers_db = $this->show_center->findBy(['user_id' => $this->user->getId()]);
        foreach ($selected_centers_db as $selected_center) {
            $selected_centers[] = $selected_center->center_id;
        }

        $centers = [];

        foreach ($centers_group as $center_group) {
            $centers_country = $this->center->findBy(['country_id' => $center_group->country_id]);
            foreach ($centers_country as $center_country) {
                $centers[$center_group->country_id][] = ['center' => $center_country,
                                                         'checked' => in_array($center_country->id, $selected_centers) ? true : false];
            }
        }

        $this->template->centers = $centers;
    }

    protected function createComponentChooseCentersForm() {
        $form = $this->chooseCentersFormFactory->create();

        $form->onSuccess[] = function ($form) {
            $this->flashMessage("Centra byla nastavena", 'success');
            $this->redirect("showCenters");
        };

        return $form;
    }
}
