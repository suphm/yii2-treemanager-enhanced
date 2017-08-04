<?php //ext/tree/Node.php
namespace app\ext\tree;

use Yii;

class Node extends \kartik\tree\models\Tree
{
    public static function tableName() {
        return 'tbl_tree';
    }

    public function afterSave($isInsert, $changedAttributes) {
        Yii::$app->session['tvScrollTop'] = Yii::$app->request->post('tvScrollTop');

        parent::afterSave($isInsert, $changedAttributes);
    }
}
