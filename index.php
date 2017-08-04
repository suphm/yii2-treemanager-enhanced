<?php //dist/views/tree/index.php
use kartik\tree\TreeView;

?>
<br>
<br>
<br>

<?=\app\ext\tree\TreeView::widget([
    'query' => \app\ext\tree\Node::find()->addOrderBy('root, lft'),
    'headingOptions' => ['label' => 'Categories'],
    'rootOptions' => ['label'=>'<span class="text-primary">Root</span>'],
    'fontAwesome' => false
,    'isAdmin' => true,
    'displayValue' => 1,
//    'iconEditSettings'=> [
//        'show' => 'list',
//        'listData' => [
//            'folder' => 'Folder',
//            'file' => 'File',
//            'mobile' => 'Phone',
//            'bell' => 'Bell',
//        ]
//    ],
    'softDelete' => true,
    'cacheSettings' => ['enableCache' => FALSE]
]);
