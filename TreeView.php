<?php //ext/tree/TreeView.php
namespace app\ext\tree;

use yii\helpers\Url;
use yii\helpers\Html;
use Yii;

class TreeView extends \kartik\tree\TreeView
{
    public function getActions() {
        return [
            'move' => Url::toRoute('tree/move'),
        ];
    }

    public function init() {
        parent::init();

        $this->view->registerAssetBundle(TreeViewAsset::className());
        $tvScrollTop = (int) Yii::$app->session['tvScrollTop'];

        $this->view->registerJs("
            jQuery('#{$this->id}-nodeform')
                .prepend('<input name=tvScrollTop value=$tvScrollTop type=hidden>')
            jQuery('#{$this->id}-tree').scrollTop($tvScrollTop)
            jQuery('#{$this->id}').data('moveAction', '{$this->actions['move']}')
        ");
    }

    public function renderTree() {
        $roots = Node::find()->roots()->all();

        return Html::tag('div',
                $this->renderRoot().
                $this->renderNodes($roots, ['class' => 'kv-tree']),
            $this->treeOptions);
    }

    public function renderNodes($nodes, $options = []) {
        $items = [];
        $structure = $this->_module->treeStructure + $this->_module->dataStructure;
        extract($structure);
        $nodeDepth = $currDepth = $counter = 0;

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

            $nodeName = $node->id . ') '.$nodeName;

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

    public function renderToolbar() {
        $_toolbar = $this->toolbar;
        $this->toolbar = array_slice($_toolbar, 0, 8, true);
        $this->toolbar += [
            'cut' => [
                'icon' => 'move',
                'options' => ['title' => Yii::t('app', 'Cut'), 'disabled' => true]
            ],
//            'paste' => [
//                'icon' => 'paste',
//                'options' => ['title' => Yii::t('app', 'Paste'), 'disabled' => true]
//            ]
        ];
        $this->toolbar += array_slice($_toolbar, -2, 3, true);

        return parent::renderToolbar();
    }
}
