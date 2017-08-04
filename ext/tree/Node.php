<?php
namespace app\ext\tree;

class Node extends \kartik\tree\models\Tree
{
    public static function tableName() {
        return \Yii::$app->params['treeTable'];
    }

    public function afterSave($isInsert, $changedAttributes) {
        \Yii::$app->session['tvScrollTop'] = \Yii::$app->request->post('tvScrollTop');

        parent::afterSave($isInsert, $changedAttributes);
    }
}
