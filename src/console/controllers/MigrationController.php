<?php

namespace unionco\ticketmaster\console\controllers;

use yii\console\Controller;
use unionco\ticketmaster\migrations\m190710_201141_create_event_field_table as EventTable;

class MigrationController extends Controller
{
    public function actionEventFieldInstall()
    {
        (new EventTable())->safeUp();
    }
}
