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

        

        $first_month = $this->chartsData->getFirstMonthPersonDistribution($record_id);
        $last_monnth = $this->chartsData->getLastMonthPersonDistribution($record_id);

        $months_set = $this->chartsData->generateMonthsAxis($first_month['year'], $first_month['month'], $last_monnth['year'], $last_monnth['month']);
        $months_data = $this->chartsData->getMonthsPersonSumDistribution($record_id);

        $x_axis = [];
        foreach($months_set as $year => $months) {
            foreach ($months as $month => $value) {
                $x_axis[] = $year."/".$month;
                $x_data[] = isset($months_data[$year][$month]) ? $months_data[$year][$month] : 0;
            }
        }


        /*
        $first_week = $this->chartsData->getPersonFirstWeekDistribution($record_id);
        $last_week = $this->chartsData->getPersonLastWeekDistribution($record_id);
        
        $weeks_set = $this->chartsData->generateWeeksAxis($first_week->year, $first_week->week, $last_week->year, $last_week->week);
        $weeks_data = $this->chartsData->getWeeksPersonSumDistribution($record_id);

        $x_axis = [];
        foreach($weeks_set as $year => $weeks) {
            foreach ($weeks as $week => $value) {
                $x_axis[] = $year."/".$week;
                $x_data[] = isset($weeks_data[$year][$week]) ? $weeks_data[$year][$week] : 0;
            }
        }
    */
        $this->template->person = $this->person->get($record_id);
        $this->template->x_axis = $x_axis;
        $this->template->x_data = $x_data;
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
        
        $x_axis_weeks = [];
        $x_data_weeks = [];

        $first_week = $this->chartsData->getPersonFirstWeekDistribution($record_id);
        $last_week = $this->chartsData->getPersonLastWeekDistribution($record_id);
        
        if($first_week && $last_week) {
            $weeks_set = $this->chartsData->generateWeeksAxis($first_week->year, $first_week->week, $last_week->year, $last_week->week);
            $chart_data = $this->chartsData->getWeeksPersonSumDistribution($record_id);
        
            foreach($weeks_set as $year => $weeks) {
                foreach ($weeks as $week => $value) {
                    $x_axis_weeks[] = $year."/".$week;
                    $x_data_weeks[] = isset($chart_data[$year][$week]) ? $chart_data[$year][$week] : 0;
                }
            }
        }

        $this->template->x_axis_weeks = $x_axis_weeks;
        $this->template->x_data_weeks = $x_data_weeks;

        $x_axis_months = [];
        $x_data_months = [];

        $first_month = $this->chartsData->getFirstMonthPersonDistribution($record_id);
        $last_month = $this->chartsData->getLastMonthPersonDistribution($record_id);

        if($first_month && $last_month) {
            $months_set = $this->chartsData->generateMonthsAxis($first_month['year'], $first_month['month'], $last_month['year'], $last_month['month']);
            $months_data = $this->chartsData->getMonthsPersonSumDistribution($record_id);

            foreach($months_set as $year => $months) {
                foreach ($months as $month => $value) {
                    $x_axis_months[] = $year."/".$month;
                    $x_data_months[] = isset($months_data[$year][$month]) ? $months_data[$year][$month] : 0;
                }
            }
        }
        
        $this->template->x_axis_months = $x_axis_months;
        $this->template->x_data_months = $x_data_months;
        
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
