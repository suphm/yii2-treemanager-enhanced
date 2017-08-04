<?php
namespace app\controllers;

class TreeController extends \yii\web\Controller
{
    public function actions() {
        return [
            // route: tree/move
            'move' => \app\ext\tree\MoveAction::className()
        ];
    }

    public function actionIndex() {
        return $this->render('index');
    }
}
