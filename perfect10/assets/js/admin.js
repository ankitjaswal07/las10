/**
 * Perfect 10 — admin behaviour.
 * Media-frame powered gallery manager used by the profile meta box.
 */
(function ($) {
  'use strict';

  var frame = null;

  function syncHidden($field) {
    var ids = $field.find('.p10-gallery-field__list li').map(function () {
      return $(this).data('id');
    }).get();
    $field.find('input[type="hidden"]').val(ids.join(','));
  }

  function openGallery($field) {
    var current = ($field.find('input[type="hidden"]').val() || '')
      .split(',')
      .filter(Boolean)
      .map(function (id) { return parseInt(id, 10); });

    if (frame) {
      frame.off('select');
    }

    frame = wp.media({
      title: 'Profile gallery',
      button: { text: 'Use these images' },
      multiple: true,
      library: { type: 'image' }
    });

    frame.on('open', function () {
      var selection = frame.state().get('selection');
      selection.reset();
      current.forEach(function (id) {
        var attachment = wp.media.attachment(id);
        attachment.fetch();
        selection.add(attachment);
      });
    });

    frame.on('select', function () {
      var selection = frame.state().get('selection');
      var $list = $field.find('.p10-gallery-field__list').empty();

      selection.each(function (attachment) {
        var data = attachment.toJSON();
        var url = (data.sizes && data.sizes.thumbnail && data.sizes.thumbnail.url) || data.url;
        var $li = $('<li>')
          .attr('data-id', data.id)
          .append($('<img>').attr({ src: url, alt: data.alt || data.title || '' }))
          .append($('<button type="button" class="button-link p10-gallery-remove" aria-label="Remove">&times;</button>'));
        $list.append($li);
      });

      syncHidden($field);
    });

    frame.open();
  }

  $(document).on('click', '.p10-gallery-add', function (e) {
    e.preventDefault();
    openGallery($(this).closest('.p10-gallery-field'));
  });

  $(document).on('click', '.p10-gallery-remove', function (e) {
    e.preventDefault();
    var $field = $(this).closest('.p10-gallery-field');
    $(this).closest('li').remove();
    syncHidden($field);
  });

  // Drag to reorder the gallery.
  $(function () {
    if (!window.jQuery.ui || !$.fn.sortable) {
      return;
    }
    $('.p10-gallery-field__list').sortable({
      items: 'li',
      placeholder: 'p10-gallery-placeholder',
      update: function () {
        syncHidden($(this).closest('.p10-gallery-field'));
      }
    });
  });
})(jQuery);
