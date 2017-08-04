<?php //controllers/TreeController.php
namespace app\controllers;

use Yii;

class TreeController extends \yii\web\Controller
{
    public function actionIndex() {
        return $this->render('index');
    }

    public function actionInput() {
        return $this->render('_index');
    }

    public function actionMove() {
        $dir = $idTo = $idFrom = $treeMoveHash = '';
        extract(Yii::$app->request->post());

        $nodeFrom = \app\ext\tree\Node::findOne($idFrom);
        $nodeTo = \app\ext\tree\Node::findOne($idTo);
        $nodeFrom->appendTo($nodeTo);

        \Yii::$app->response->format = 'json';
        return ['status' => 'success', 'out' => Yii::t('kvtree', 'The node was moved successfully.') ];
    }
}
