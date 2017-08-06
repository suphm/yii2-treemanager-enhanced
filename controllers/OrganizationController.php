<?php
namespace app\controllers;

class OrganizationController extends \yii\web\Controller
{
    public function actions() {
        return ['move-tree' => 'app\ext\tree\MoveAction'];
    }

    public function actionIndex() {
        return $this->render('@app/ext/tree/index_view.php');
    }
}
