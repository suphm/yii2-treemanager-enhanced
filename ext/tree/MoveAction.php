<?php
namespace app\ext\tree;

class MoveAction extends \yii\base\Action
{
    public function run() {
        $idTo = $idFrom = '';
        extract(\Yii::$app->request->post());

        $nodeFrom = Tree::findOne($idFrom);
        $nodeTo = Tree::findOne($idTo);
        $nodeFrom->appendTo($nodeTo);

        \Yii::$app->response->format = 'json';
        return ['status' => 'success', 'out' => \Yii::t('kvtree', 'The node was moved successfully.') ];
    }
}
