/**
 * Perfect 10 — Customizer live preview bindings.
 */
(function ($) {
  'use strict';

  function bindColor(settingId, cssVar) {
    wp.customize(settingId, function (value) {
      value.bind(function (to) {
        if (to) {
          document.documentElement.style.setProperty(cssVar, to);
        }
      });
    });
  }

  function bindText(settingId, selector, attr) {
    wp.customize(settingId, function (value) {
      value.bind(function (to) {
        var $el = $(selector);
        if (!$el.length) {
          return;
        }
        if (attr) {
          $el.attr(attr, to);
        } else {
          $el.text(to);
        }
      });
    });
  }

  $(function () {
    bindColor('p10_color_gold', '--p10-gold');
    bindColor('p10_color_ink', '--p10-ink');
    bindColor('p10_color_cream', '--p10-cream');
    bindColor('p10_color_panel', '--p10-panel');

    bindText('p10_brand_name', '.p10-brand__name');
    bindText('p10_brand_suffix', '.p10-brand__sub');
    bindText('p10_hero_title_before', '[data-p10-hero-before]');
    bindText('p10_hero_title_accent', '[data-p10-hero-accent]');
    bindText('p10_hero_tagline', '[data-p10-hero-tagline]');
    bindText('p10_hero_eyebrow', '[data-p10-hero-eyebrow]');

    wp.customize('p10_hero_overlay', function (value) {
      value.bind(function (to) {
        document.documentElement.style.setProperty('--p10-hero-overlay', (parseInt(to, 10) || 0) / 100);
      });
    });

    wp.customize('p10_marquee_speed', function (value) {
      value.bind(function (to) {
        document.documentElement.style.setProperty('--p10-marquee-duration', (parseInt(to, 10) || 38) + 's');
      });
    });

    wp.customize('p10_directory_columns', function (value) {
      value.bind(function (to) {
        document.documentElement.style.setProperty('--p10-grid-columns', parseInt(to, 10) || 3);
      });
    });

    // Section visibility toggles need a full refresh.
    ['p10_marquee_enabled', 'p10_search_enabled', 'p10_muses_enabled', 'p10_premium_enabled',
     'p10_types_enabled', 'p10_cities_enabled', 'p10_call_enabled', 'p10_about_enabled',
     'p10_reviews_enabled', 'p10_ads_enabled', 'p10_age_gate_enabled', 'p10_concierge_enabled',
     'p10_hero_image', 'p10_ads_image', 'p10_muses_count', 'p10_premium_count',
     'p10_types_count', 'p10_cities_count', 'p10_reviews_count'].forEach(function (id) {
      wp.customize(id, function (value) {
        value.bind(function () {
          wp.customize.preview.send('refresh');
        });
      });
    });
  });
})(jQuery);
