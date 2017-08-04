<?php
namespace app\ext\tree;

class MoveAction extends \yii\base\Action
{
    public function run() {
        $dir = $idTo = $idFrom = $treeMoveHash = '';
        extract(\Yii::$app->request->post());

        $nodeFrom = Node::findOne($idFrom);
        $nodeTo = Node::findOne($idTo);
        $nodeFrom->appendTo($nodeTo);

        \Yii::$app->response->format = 'json';
        return ['status' => 'success', 'out' => \Yii::t('kvtree', 'The node was moved successfully.') ];
    }
}
