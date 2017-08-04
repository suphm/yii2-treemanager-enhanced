<?php //ext/tree/TreeView.php
namespace app\ext\tree;

use yii\helpers\Html;
use Yii;
use yii\helpers\Url;

class TreeView extends \kartik\tree\TreeView
{
    public function init() {
        parent::init();

//        $this->view->registerAssetBundle(TreeViewAsset::className());
//        $this->view->assetBundles['kartik\tree\TreeViewAsset']->js = [];

        $moveAction = Url::toRoute('tree/move');
        $scrollTop = (int) Yii::$app->session['treeViewScrollTop'];

        $this->view->registerJs("!(function() {
            var jqTree = jQuery('#{$this->id}-tree'),
                jqDetail = jQuery('#{$this->id}-detail')

            jqTree.scrollTop({$scrollTop})

            // cut button
            var btnCut = jQuery('#{$this->id}-toolbar').find('.kv-cut')
            btnCut.on('click', function() {
                this.disabled = true

                // remove tooltip
                this.nextSibling.remove()
                this.dataset.clipnode = jQuery('#{$this->id}')[0].dataset.clipnode

//                BootstrapDialog.alert({
//                    message: 'Select folder to paste',
//                    size: 'size-small',
//                    closable: true
//                })
            })

            jQuery('#{$this->id}').on('treeview.beforeselect', function(event, key, jqXHR, settings) {
                this.dataset.clipnode = key

                if (btnCut[0].dataset.clipnode > 0) {
                    move(btnCut[0].dataset.clipnode, key)
                    btnCut[0].dataset.clipnode = null
                }
            })

            jQuery('#{$this->id}').on('treeview.selected', function(event, key, jqXHR, settings) {
                var jqForm = jQuery('#{$this->id}-nodeform')
                var scrollTop = jQuery('#{$this->id}-tree').scrollTop()
                jqForm.prepend('<input name=treeViewScrollTop value='+ scrollTop +' type=hidden>')
            })

            function move(from, to) {
                var jqNodeFrom = jqTree.find('li[data-key='+ from +']'),
                    jqNodeTo = jqTree.find('li[data-key='+ to +']'),
                    dir = 'any'
                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    data: {
                        'idFrom': jqNodeFrom.data('key'),
                        'idTo': jqNodeTo.data('key'),
                        'modelClass': 'app\\\\ext\\\\tree\\\\Node',
                        'dir': dir,
                        'allowNewRoots': 1,
                        'treeMoveHash': jQuery('input[name=treeMoveHash]').val(),
                        'treeViewScrollTop': jQuery('#{$this->id}-tree').scrollTop()
                    },
                    url: '{$moveAction}',
                    beforeSend: function (jqXHR, settings) {
                        jqTree.parent().addClass('kv-loading-search');
                    },
                    success: function (data, textStatus, jqXHR) {
                        if (data.status === 'success') {
                            if (jqNodeTo.find('li').length > 0) {
                                jqNodeTo.children('ul').append(jqNodeFrom);
                            } else {
                                jqNodeTo.addClass('kv-parent');
                                jQuery(document.createElement('ul')).appendTo(jqNodeTo).append(jqNodeFrom);
                            }

                            (jqDetail.length > 0) && showAlert(data.out, 'success');
                            jqTree.find('li.kv-collapsed').each(function() {
                                if ($(this).has(jqNodeFrom).length > 0) {
                                    $(this).removeClass('kv-collapsed');
                                }
                            })
                        }

                        (jqDetail.length > 0) && showAlert(data.out, 'danger');
                        jqTree.parent().removeClass('kv-loading-search');
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        (jqDetail.length > 0) && showAlert(errorThrown, 'danger');
                        jqTree.parent().removeClass('kv-loading-search');
                    },
                });
            }

            function showAlert(msg, type, cb) {
                var dur = jQuery.fn.treeview.defaults.alertFadeDuration,
                    alert = jqDetail.find('.alert-' + type);

                jqDetail.find('.alert').addClass('hide');
                jqDetail.find('.kv-select-node-msg').remove();
                alert.removeClass('hide').hide().find('div').remove();
                alert.append('<div>' + msg + '</div>').fadeIn(dur, function () {
                    cb && setTimeout(function() {alert.fadeOut(dur, cb())}, dur * 2)
                })
            }
        })()");
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
                (!empty($node->children()) ? $this->renderNodes($children) : ''),
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
