<?php
namespace app\ext\tree;

class TreeViewAsset extends \yii\web\AssetBundle
{
    public $sourcePath = __DIR__.'/assets';
    public $js = ['tree.js'];
    public $depends = ['kartik\tree\TreeViewAsset'];
    public $publishOptions = ['forceCopy' => YII_ENV_DEV];
}
