<?php

namespace App\Presenters;

use Nette;
use App\Model;
use App\Forms\PersonFormFactory;
use App\Forms\PersonMigrateFormFactory;
use Nette\Application\UI\Multiplier;
use Tracy\Debugger;
use Nette\Utils\DateTime;


class PersonPresenter extends ComplexPresenter {	
	/** @var PersonFormFactory @inject */
	public $factory;

    /** @var PersonMigrateFormFactory @inject */
    public $personMigratefactory;

	protected function startup() {
		parent::startup();
		$this->model = $this->person;
	}

    public function renderTest($record_id) {
        $first_week = $this->chartsData->getPersonFirstWeekDistribution($record_id);
        $last_week = $this->chartsData->getPersonLastWeekDistribution($record_id);

        $weeks_chart_data = null;

        if($first_week && $last_week) {
            $weeks_chart_data = $this->chartsData->generatePersonWeeksChartData($record_id, $first_week->year, $first_week->week, $last_week->year, $last_week->week);
        }

        $this->template->weeks_chart_data = $weeks_chart_data;

        $months_chart_data = null;

        $first_month = $this->chartsData->getPersonFirstMonthDistribution($record_id);
        $last_month = $this->chartsData->getPersonLastMonthDistribution($record_id);

        if($first_month && $last_month) {
            $months_chart_data = $this->chartsData->generatePersonMonthsChartData($record_id, $first_month['year'], $first_month['month'], $last_month['year'], $last_month['month']);
        }

        $this->template->months_chart_data = $months_chart_data;


        $years_chart_data = null;

        $first_year = $this->chartsData->getPersonFirstYearDistribution($record_id);
        $last_year = $this->chartsData->getPersonLastYearDistribution($record_id);

        if($first_year && $last_year) {
            $years_chart_data = $this->chartsData->generatePersonYearsChartData($record_id, $first_year->year, $last_year->year);
        }

        $this->template->years_chart_data = $years_chart_data;
        $this->template->person = $this->person->get($record_id);
    }

    public function renderList() {
        if($this->getUser()->isInRole('superadmin')) {
            $this->template->records = $this->model->findAll()
                                                   ->order('center.title');

            $this->template->centers = $this->center->findAll()
                                                    ->order('title')
                                                    ->fetchPairs('id', 'title');
        }
        else {
            $this->template->records = $this->model->findBy(['center_id' => $this->user->center_id]);
        }
    }

    public function handleSetPersonActivity($person_id, $active) {
        $this->person->findBy(['id' => $person_id])
                     ->update(['active' => $active == "true" ? 1 : 0]);

        $this->sendPayload();
    }    

    public function actionMigrate($person_id) {
        if(!$this->getUser()->isInRole('superadmin')) 
            throw new Nette\Application\ForbiddenRequestException("Nedostatečná práva.");
    }

    public function createComponentMigratePersonForm() {
        if(!$this->getUser()->isInRole('superadmin')) 
            throw new Nette\Application\ForbiddenRequestException("Nedostatečná práva.");

        $form = $this->personMigratefactory->create();
        
        $form->onSuccess[] = function ($form) {
            $this->flashMessage("Výsledky byly úpsěšně přesunuty", 'success');
            $form->getPresenter()->redirect('migrate');
        };

        return $form;
    }

    public function renderExpandRow($record_id) {
        $this->template->person = $this->person->get($record_id);
        $this->template->books_distribution = $this->distribution->getPersonBooksDistribution($record_id);
        
        $first_week = $this->chartsData->getPersonFirstWeekDistribution($record_id);
        $last_week = $this->chartsData->getPersonLastWeekDistribution($record_id);
        
        $weeks_chart_data = null;

        if($first_week && $last_week) {
            $weeks_chart_data = $this->chartsData->generatePersonWeeksChartData($record_id, $first_week->year, $first_week->week, $last_week->year, $last_week->week);
        }

        $this->template->weeks_chart_data = $weeks_chart_data;

        $first_month = $this->chartsData->getPersonFirstMonthDistribution($record_id);
        $last_month = $this->chartsData->getPersonLastMonthDistribution($record_id);
        
        $months_chart_data = null;

        if($first_month && $last_month) {
            $months_chart_data = $this->chartsData->generatePersonMonthsChartData($record_id, $first_month['year'], $first_month['month'], $last_month['year'], $last_month['month']);
        }

        $this->template->months_chart_data = $months_chart_data;
        
        $years_chart_data = null;

        $first_year = $this->chartsData->getPersonFirstYearDistribution($record_id);
        $last_year = $this->chartsData->getPersonLastYearDistribution($record_id);

        if($first_year && $last_year) {
            $years_chart_data = $this->chartsData->generatePersonYearsChartData($record_id, $first_year->year, $last_year->year);
        }

        $this->template->years_chart_data = $years_chart_data;

        parent::renderExpandRow($record_id);
    }

    public function actionDelete($id) {
        $person = $this->model->get($id);
        
        if(!$this->getUser()->isInRole('superadmin') && $person->center_id != $this->user->center_id) {
            throw new Nette\Application\ForbiddenRequestException("Nemůžete smazat osobu z jiného centra!");
        }

        $this->payload->success = $this->model->delete($id);
        $this->sendPayload();
    }
}
