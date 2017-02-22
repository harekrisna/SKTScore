<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Tracy\Debugger;
use Nette\Application\UI\Form;

class BackupPresenter extends Nette\Application\UI\Presenter {
	/** @var BackupManager */
	protected $backup_manager;

	public function __construct(\App\Model\BackupManager $backup_manager) {
 		parent::__construct();
 		$this->backup_manager = $backup_manager;
 	}

	public function actionBackupDb() {
		$backup_file = DB_BACKUP_FOLDER."/".date('Y-m-d--h-i-s').".sql";

		$this->writeTableToBackupFile("country", $backup_file);
		$this->writeTableToBackupFile("center", $backup_file);
		$this->writeTableToBackupFile("category", $backup_file);
		$this->writeTableToBackupFile("person", $backup_file);
		$this->writeTableToBackupFile("book", $backup_file);
		$this->writeTableToBackupFile("distribution", $backup_file);
		$this->writeTableToBackupFile("book_priority", $backup_file);
		$this->writeTableToBackupFile("show_center", $backup_file);

		$this->payload->success = 1;
		$this->sendPayload();
	}

	private function writeTableToBackupFile($table, $backup_file) {
		$persons_head = "";

		if(file_exists($backup_file)) {
			$persons_head .= "\r\n\r\n";
		}

		$persons_head .= "-- data pro tabulku `".$table."` \r\n\r\n";
		$persons_backup = $this->backup_manager->backupAll($table);
		file_put_contents($backup_file, $persons_head.$persons_backup, FILE_APPEND | LOCK_EX);		
	}
}
