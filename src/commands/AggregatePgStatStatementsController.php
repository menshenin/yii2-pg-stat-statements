<?php

namespace simaland\pgstatstatements\commands;

use simaland\pgstatstatements\models\AggregatePgStatStatements;
use simaland\pgstatstatements\models\PgStatStatements;
use yii\console\Controller;

/**
 * Controller for aggregate and work with aggregated pg_stat_statements view
 * @package simaland\pgstatstatements\console\controllers
 */
class AggregatePgStatStatementsController extends Controller
{
    /**
     * aggregate pg_stat_statements view to aggregate_pg_stat_statements table with info about host and operation start
     */
    public function actionAggregatePgStat()
    {
        $query = PgStatStatements::find();
        $date = date('c');

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            foreach ($query->batch(1000) as $pgStatStatements) {
                foreach ($pgStatStatements as $pgStatStatement) {
                    $aggPgStatStatement = new AggregatePgStatStatements();
                    $aggPgStatStatement->setAttributes($pgStatStatement->getAttributes());
                    $aggPgStatStatement->created_at = $date;
                    $aggPgStatStatement->server = gethostname();
                    $aggPgStatStatement->save();
                }
            }

            PgStatStatements::reset();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
