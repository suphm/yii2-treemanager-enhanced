<div class="site-index">
    <?=
    \app\ext\tree\TreeView::widget([
        'query' => \app\ext\tree\Tree::find()->addOrderBy('root, lft'),
        'headingOptions' => ['label' => 'Categories'],
        'rootOptions' => ['label' => '<span class="text-primary">President</span>'],
        'fontAwesome' => false,
        'isAdmin' => true,
        'displayValue' => 1,
        'iconEditSettings'=> [
            'show' => 'list',
            'listData' => [
                'folder' => 'Folder',
                'file' => 'File',
                'mobile' => 'Phone',
                'bell' => 'Bell',
            ]
        ],
        'softDelete' => true,
        'cacheSettings' => ['enableCache' => !YII_ENV_DEV]
    ]);
    ?>
</div>
