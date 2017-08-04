!(function(__tvId) {
   var __tv = window[$('#' + __tvId).data('krajee-treeview')],
    $tree = $('#' + __tv.treeId),
    $detail = $('#' + __tv.detailId),
    $btnCut = $('#' + __tv.toolbarId).find('button.kv-cut');

  __tv.alertFadeDuration = 2000

  // cut button
  $btnCut.on('click', function() {
      this.disabled = true
      $(this).data('clipNode', $('#' + __tvId).data('clipNode'))
      $('#'+ __tvId).data('clipNode', 0)

      // remove tooltip
      this.nextSibling && this.nextSibling.remove()
      showAlert('Select folder to paste')
  })

  $('#' + __tvId).on('treeview.beforeselect',
    function(event, key, jqXhr, xhrSettings) {
      console.log(arguments)
      $(this).data('clipNode', key)
      if ($btnCut.data('clipNode') > 0) {
              move($btnCut.data('clipNode'), key)
              $btnCut.data('clipNode', 0)
      }
  })

  $('#'+ __tvId).on('treeview.selected', function(event, key, data, success, jqXhr) {
      var $form = $('#' + __tvId + '-nodeform')
      var scrollTop = $tree.scrollTop()
      $form.prepend('<input name=tvScrollTop value='+ scrollTop +' type=hidden>')
      $('#'+ __tv.detailId +' .alert').css('display', 'none').removeClass('hide')
  })

  function move(from, to) {
    var $nodeFrom = $tree.find('li[data-key=' + from + ']'),
      $nodeTo = $tree.find('li[data-key=' + to + ']'),
      dir = 'any'
    $.ajax({
      type: 'post',
      dataType: 'json',
      data: {
        'idFrom': $nodeFrom.data('key'),
        'idTo': $nodeTo.data('key'),
        'modelClass': 'app\\\\ext\\\\tree\\\\Node',
        'dir': dir,
        'allowNewRoots': 1,
        'treeMoveHash': $('input[name=treeMoveHash]').val(),
        'tvScrollTop': $tree.scrollTop()
      },
      url: $.fn.treeview.defaults.actions.move,
      beforeSend: function(jqXHR, settings) {
        $tree.parent().addClass('kv-loading-search');
      },
      success: function(data, textStatus, jqXHR) {
        if (data.status === 'success') {
          if ($nodeTo.find('li').length > 0) {
            $nodeTo.children('ul').append($nodeFrom);
          } else {
            $nodeTo.addClass('kv-parent');
            $(document.createElement('ul')).appendTo($nodeTo).append($nodeFrom);
          }

          showAlert(data.out, 'success');
          $tree.find('li.kv-collapsed').each(function() {
            if ($(this).has($nodeFrom).length > 0) {
              $(this).removeClass('kv-collapsed');
            }
          })
        }

        $tree.parent().removeClass('kv-loading-search');
      },
      error: function(jqXHR, textStatus, errorThrown) {
        showAlert(errorThrown, 'danger');
        $tree.parent().removeClass('kv-loading-search');
      },
    });
  }

  function showAlert(msg, type) {
    ('danger|warning|success'.indexOf(type) == -1) && (type = 'info')
    var $alert = $detail.find('.alert-' + type)

    $alert.append(''+msg).fadeIn('slow', function() {
      setTimeout(function() {
                $alert.fadeOut('slow')
      }, __tv.alertFadeDuration)
    })
  }
})(document.querySelector('[data-krajee-treeview]').id)
