<?php
namespace app\ext\tree;

use yii\helpers\Url;
use yii\helpers\Html;
use Yii;

class TreeView extends \kartik\tree\TreeView
{
    const BTN_CUT = 'cut';

    public function init() {
        parent::init();

        // controller/action
        $treeMoveRoute = Url::toRoute(Yii::$app->params['treeMoveRoute']);
        $this->view->registerAssetBundle(TreeViewAsset::className());

        $treeViewScrollTop = (int) Yii::$app->session['treeViewScrollTop']
            or ($treeViewScrollTop = (int) $_COOKIE['treeViewScrollTop']);

        $this->view->registerJs("
            jQuery('#{$this->id}-nodeform')
                .prepend('<input name=treeViewScrollTop value=$treeViewScrollTop type=hidden>')
            jQuery('#{$this->id}-tree').scrollTop($treeViewScrollTop)
            jQuery.fn.treeview.defaults.actions.move = '$treeMoveRoute'
        ");

        $defaultToolbar = $this->getDefaultToolbar();
        if (!$this->allowNewRoots) {
            unset($defaultToolbar[self::BTN_CREATE_ROOT]);
        }
        $this->toolbar = \yii\helpers\ArrayHelper::merge($defaultToolbar, $this->toolbar);
        $this->sortToolbar();
    }

    public function renderTree() {
        $roots = Tree::find()->roots()->all();

        return Html::tag('div',
                $this->renderRoot().
                $this->renderNodes($roots, ['class' => 'kv-tree']),
            $this->treeOptions);
    }

    public function renderNodes($nodes, $options = []) {
        $items = [];
        $structure = $this->_module->treeStructure + $this->_module->dataStructure;
        extract($structure);

        foreach ($nodes as $node) {
            if (!$this->isAdmin && !$node->isVisible() || !$this->showInactive && !$node->isActive()) {
                continue;
            }

            /** @noinspection PhpUndefinedVariableInspection */
            $nodeDepth = $node->$depthAttribute;
            /** @noinspection PhpUndefinedVariableInspection */
            $nodeLeft = $node->$leftAttribute;
            /** @noinspection PhpUndefinedVariableInspection */
            $nodeRight = $node->$rightAttribute;
            /** @noinspection PhpUndefinedVariableInspection */
            $nodeKey = $node->$keyAttribute;
            /** @noinspection PhpUndefinedVariableInspection */
            $nodeName = $node->$nameAttribute;
            /** @noinspection PhpUndefinedVariableInspection */
            $nodeIcon = $node->$iconAttribute;
            /** @noinspection PhpUndefinedVariableInspection */
            $nodeIconType = $node->$iconTypeAttribute;

            $indicators = '';

            if (isset($this->nodeLabel)) {
                $label = $this->nodeLabel;
                $nodeName = is_callable($label) ? $label($node) :
                    (is_array($label) ? ArrayHelper::getValue($label, $nodeKey, $nodeName)
                                      : $nodeName);
            }

            //$nodeName = $node->id . ') '.$nodeName;

            if (trim($indicators) == null) {
                $indicators = '&nbsp;';
            }
            $nodeOptions = [
                'data-key' => $nodeKey,
                'data-lft' => $nodeLeft,
                'data-rgt' => $nodeRight,
                'data-lvl' => $nodeDepth,
                'data-readonly' => static::parseBool($node->isReadonly()),
                'data-movable-u' => static::parseBool($node->isMovable('u')),
                'data-movable-d' => static::parseBool($node->isMovable('d')),
                'data-movable-l' => static::parseBool($node->isMovable('l')),
                'data-movable-r' => static::parseBool($node->isMovable('r')),
                'data-removable' => static::parseBool($node->isRemovable()),
                'data-removable-all' => static::parseBool($node->isRemovableAll()),
            ];

            $children = $node->children(1)->all();
            $css = [];

            if (!empty($children)) {
                $css[] = 'kv-parent';
            }
            if (!$node->isVisible() && $this->isAdmin) {
                $css[] = 'kv-invisible';
            }
            if ($this->showCheckbox && $node->isSelected()) {
                $css[] = 'kv-selected';
            }
            if ($node->isCollapsed()) {
                $css[] = 'kv-collapsed';
            }
            if ($node->isDisabled()) {
                $css[] = 'kv-disabled';
            }
            if (!$node->isActive()) {
                $css[] = 'kv-inactive';
            }
            $indicators .= $this->renderToggleIconContainer(false) . "\n";
            $indicators .= $this->showCheckbox ? $this->renderCheckboxIconContainer(false) . "\n" : '';

            if (!empty($css)) {
                Html::addCssClass($nodeOptions, $css);
            }

            $items[] = Html::tag('li',
                "<div tabindex='-1' class='kv-tree-list'>\n".
                "   <div class='kv-node-indicators'>\n{$indicators}\n</div>\n".
                "   <div tabindex=-1 class=kv-node-detail>\n".
                        $this->renderNodeIcon($nodeIcon, $nodeIconType, empty($children)) . "\n".
                "       <span class='kv-node-label'>". $nodeName ."</span>".
                "   </div>\n".
                "</div>\n".
                (!empty($children) ? $this->renderNodes($children) : ''),
            $nodeOptions);
        }

        return Html::tag('ul', implode('', $items), $options);
    }

    public function getDefaultToolbar() {
        return [
            self::BTN_CREATE => [
                'icon' => 'plus',
                'alwaysDisabled' => false, // set this property to `true` to force disable the button always
                'options' => ['title' => Yii::t('kvtree', 'Add new'), 'disabled' => true],
            ],
            self::BTN_CREATE_ROOT => [
                'icon' => $this->fontAwesome ? 'tree' : 'tree-conifer',
                'options' => ['title' => Yii::t('kvtree', 'Add new root')],
            ],
            self::BTN_REMOVE => [
                'icon' => 'trash',
                'options' => ['title' => Yii::t('kvtree', 'Delete'), 'disabled' => true],
            ],
            self::BTN_SEPARATOR,
            self::BTN_MOVE_UP => [
                'icon' => 'arrow-up',
                'options' => ['title' => Yii::t('kvtree', 'Move Up'), 'disabled' => true],
            ],
            self::BTN_MOVE_DOWN => [
                'icon' => 'arrow-down',
                'options' => ['title' => Yii::t('kvtree', 'Move Down'), 'disabled' => true],
            ],
            self::BTN_MOVE_LEFT => [
                'icon' => 'arrow-left',
                'options' => ['title' => Yii::t('kvtree', 'Move Left'), 'disabled' => true],
            ],
            self::BTN_MOVE_RIGHT => [
                'icon' => 'arrow-right',
                'options' => ['title' => Yii::t('kvtree', 'Move Right'), 'disabled' => true],
            ],
            self::BTN_CUT => [
                'icon' => 'move',
                'options' => ['title' => Yii::t('app', 'Cut'), 'disabled' => true]
            ],
            self::BTN_SEPARATOR,
            self::BTN_REFRESH => [
                'icon' => 'refresh',
                'options' => ['title' => Yii::t('kvtree', 'Refresh')],
                'url' => Yii::$app->request->url,
            ],
        ];
    }
}
