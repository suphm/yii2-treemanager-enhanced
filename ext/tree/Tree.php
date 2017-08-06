<?php
namespace app\ext\tree;

class Tree extends \kartik\tree\models\Tree
{
    public static function tableName() {
        return \Yii::$app->params['treeTable'];
    }

    public function afterSave($isInsert, $changedAttributes) {
        \Yii::$app->session['treeViewScrollTop'] = \Yii::$app->request->post('treeViewScrollTop');

        parent::afterSave($isInsert, $changedAttributes);
    }
}
