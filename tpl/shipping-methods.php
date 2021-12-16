<?php

use TrsVendors\Dgm\Arrays\Arrays;
use Trs\Woocommerce\Model\Shipping\ShippingMethod;
use Trs\Woocommerce\Model\Shipping\ShippingMethodFamily;
use Trs\Woocommerce\ShippingMethodsListing;

$select2Data = Arrays::map(array_values(ShippingMethodsListing::getListing()), function(ShippingMethodFamily $family) {
     return array(
         'text' => $family->title,
         'children' => Arrays::map(array_values($family->methods), function(ShippingMethod $method) {

             $family = $method->family->title;
             $zone = $method->zone->title;
             $iid = (string)$method->formatInstanceId();

             return array(
                 'valid' => true,
                 'id' => $method->id->serialize(),
                 'text' => join(' ', array($family, $zone, $method->title, $iid)),
                 'zone' => $zone,
                 'family' => $family,
                 'label' => $method->title,
                 'enabled' => $method->enabled,
                 'friendlyId' => $iid,
                 'settingsPageUrl' => $method->settingsPageUrl,
                 'apiUrl' => $method->apiUrl,
             );
         }),
         'addMethodUrl' => $family->addInstanceUrl,
     );
});

?>

<style>
    .wbs-smo {
        display: block;
        padding: 0;
        -webkit-transition: padding 300ms ease 700ms;
        -moz-transition: padding 300ms ease 700ms;
        -ms-transition: padding 300ms ease 700ms;
        -o-transition: padding 300ms ease 700ms;
        transition: padding 300ms ease 700ms;
    }

    .wbs-smo-header {
        display: block;
        white-space: nowrap;
    }

        .wbs-smo-title {
            display: inline;
            white-space: nowrap;
        }

            .wbs-smo-title-label {
                font-weight: 500;
            }

                .wbs-smo-title-label-spacer {
                    margin: 0 0.3em;
                    color: gray;
                }

            .wbs-smo-title-id {
                font-family: monospace;
                color: gray;
                margin-left: 0.3em;
            }

    .wbs-smo-actions {
        display: block;
        overflow: hidden;
        height: 0em; /* without 'em' transition doesn't work */
        -webkit-transition: height 300ms ease 700ms;
        -moz-transition: height 300ms ease 700ms;
        -ms-transition: height 300ms ease 700ms;
        -o-transition: height 300ms ease 700ms;
        transition: height 300ms ease 700ms;
    }

    .wbs-smo-action,
    .wbs-smo-action:hover,
    .wbs-smo-action:active,
    .wbs-smo-action:focus,
    .wbs-smo-action:visited,
    .wbs-smo-action:link {
        display: inline;
        margin: 0 0 0 1em;
        padding: 0;
        text-decoration: none;
        font-style: italic;
        font-size: small;
        color: #e0e0e0;
        border: none;
        background: none;
        cursor: pointer;
        -webkit-appearance: none;
        outline: none;
        -webkit-box-shadow: none;
        -moz-box-shadow: none;
        box-shadow: none;
    }

    .wbs-smo-action:first-child {
        margin-left: 0;
    }

    .wbs-smo-action:hover,
    .wbs-smo-action:active,
    .wbs-smo-action:focus {
        color: white;
    }

    .wbs-smo-action-text {
        border-bottom: 1px dashed;
    }

    .wbs-smo-action-text:hover,
    .wbs-smo-action-text:active,
    .wbs-smo-action-text:focus {
        border-bottom: none;
    }

    .select2-results__option--highlighted .wbs-smo {
        padding: 0.5em 0;
    }

        .select2-results__option--highlighted .wbs-smo-actions {
            height: 1.85em;
        }

        .select2-results__option--highlighted .wbs-smo-title-label-spacer,
        .select2-results__option--highlighted .wbs-smo-title-id {
            color: inherit;
        }


    .wbs-smo-group {
        display: block;
    }

    .wbs-smo-group-no-methods {
        display: block;
        font-weight: normal;
        font-style: italic;
        color: grey;
        padding-left: 1em;
    }

    .wbs-smo-action--group:first-child {
        margin-left: 1em;
    }

    .wbs-smo-action--group,
    .wbs-smo-action--group:focus,
    .wbs-smo-action--group:active,
    .wbs-smo-action--group:hover,
    .wbs-smo-action--group:visited,
    .wbs-smo-action--group:link {
        color: transparent;
        font-weight: normal;
        -webkit-transition: color 500ms;
        -moz-transition: color 500ms;
        -ms-transition: color 500ms;
        -o-transition: color 500ms;
        transition: color 500ms;
    }

    .wbs-smo-group:hover .wbs-smo-action--group {
        color: inherit;
    }

    .wbs-smo-tip {
        color: inherit;
        font-size: 120%;
    }

    .wbs-selection {
        vertical-align: -1px;
    }

    .wbs-selection-delimiter {
        font-size: 110%;
        color: grey;
        margin: 0 .15em;
    }

    #tiptip_holder {
        max-width: 500px !important;
    }

    #tiptip_content {
        font-size: .9em;
        max-width: none;
    }

    #tiptip_content br {
        line-height: 2.2;
    }
</style>

<script>
    (function($) {

        function html(text) {
            return html.helper.text(''+text).html();
        }
        html.helper = $('<span/>');

        Ractive.decorators.select2.type['shipping-methods'] = {

            placeholder: 'Select shipping method(s)',

            dropdownAutoWidth: true,

            data: <?php echo json_encode($select2Data) ?>,

            templateResult: function(data) {

                if (data.children) {
                    return $(
                        '<span class="wbs-smo-group">' +
                            html(data.text) +
                            (data.addMethodUrl ?
                                '<a href="'+ html(data.addMethodUrl) +'" class="wbs-smo-action wbs-smo-action--group" target="_blank">' +
                                    '<i class="fa fa-plus"></i> ' +
                                    '<span class="wbs-smo-action-text">add method</span>' +
                                '</a>' : ''
                            ) +
                            (!data.children.length ?
                                '<div class="wbs-smo-group-no-methods">no shipping methods</div>' : '') +
                        '</span>'
                    );
                }

                if (!data.valid) {
                    return data.text;
                }

                var title = [
                    'Disable a shipping method if you don\'t want it to show shipping options to the customer on its own when invoked by Woocommerce.',
                    'That might be useful if you want it to be invoked by Tree Table Rate Shipping only.',
                    'Tree Table Rate Shipping will invoke all specified shipping methods regardless of their activity state and shipping zone.'
                ].join("\n");

                var $element =  jQuery(
                    // 'smo' is for 'shipping method option'
                    '<span class="wbs-smo">' +
                        '<span class="wbs-smo-header">' +
                            '<span class="wbs-smo-title">' +
                                '<span class="wbs-smo-title-zone">'+ html(data.zone) +'</span> ' +
                                '<span class="wbs-smo-title-label"><i class="wbs-smo-title-label-spacer fa fa-caret-right"></i> '+ html(data.label) +'</span> ' +
                                '<span class="wbs-smo-title-id">'+ html(data.friendlyId) +'</span> ' +
                            '</span>' +
                        '</span>' +
                        '<span class="wbs-smo-actions">' +
                            '<button type="button" class="wbs-smo-action js-wbs-smo-action-toggle"><i class="fa fa-times-circle"></i> <span class="wbs-smo-action-text">disable</span> <i title="'+ html(title) +'" class="woocommerce-help-tip wbs-smo-tip"></i></button> ' +
                            (data.settingsPageUrl ?
                                '<a class="wbs-smo-action" href="'+ html(data.settingsPageUrl) +'" target="_blank"><i class="fa fa-cog"></i> <span class="wbs-smo-action-text">configure</span></a> ' : '') +
                        '</span>' +
                    '</span>'
                );

                function api(options) {

                    options = $.extend({

                        method: 'POST',
                        url: data.apiUrl,
                        data: null,

                        before: null,
                        after: null,

                        success: null,
                        error: null,
                        complete: null

                    }, options);

                    options.type = options.method || options.type;
                    delete options.method;

                    options.data = $.extend({
                        _wpnonce: $('#_wpnonce').val()
                    }, options.data || {});

                    if (!options.error) {
                        options.error = function(xhr, status, error) {

                            var msg = 'An error occurred while performing the action';
                            if (xhr.responseText) {
                                msg += ". \n\n" + xhr.responseText;
                            } else if (error || status) {
                                msg += ': ' + (error || status) + '.';
                            } else {
                                msg += '.';
                            }

                            alert(msg);
                        }
                    }

                    options.before = options.before || function() {};
                    options.after = options.after || function() {};

                    var _complete = options.complete;
                    options.complete = function() {
                        options.after();
                        _complete && _complete.apply(this, arguments);
                    };

                    options.before();
                    try {
                        $.ajax(options);
                    } catch (e) {
                        options.after();
                        throw e;
                    }
                }

                var updateToogleCaption = function() {
                    $element.find('.js-wbs-smo-action-toggle .wbs-smo-action-text').text(data.enabled ? 'disable' : 'enable');
                };

                updateToogleCaption();

                $element.on('click', '.js-wbs-smo-action-toggle', function() {

                    var $button = $(this);

                    api({
                        method: 'POST',
                        data: { enable: Number(!data.enabled) },
                        success: function() {
                            data.enabled = !data.enabled;
                            updateToogleCaption();
                        },
                        before: function() {
                            $button.attr('disabled', true);
                        },
                        after: function() {
                            $button.attr('disabled', false);
                        }
                    });
                });

                $element.on('mousedown mouseup click', '.wbs-smo-action', function(e) {
                    e.stopPropagation();
                });

                if ($.fn.tipTip) {
                    $element.find('[title]')
                        .each(function() { $(this).attr('title', this.title.replace(/\n/g, '<br>')); })
                        .tipTip();
                }

                return $element;
            },

            templateSelection: function(data) {

                if (!data.valid) {
                    return data.text;
                }

                return $(
                    '<span class="wbs-selection">' +
                        $.map([data.family, data.zone, data.label], html).join(' <i class="fa fa-caret-right wbs-selection-delimiter"></i> ') +
                        ' ' + data.friendlyId +
                    '</span>'
                );
            }
        };
    })(jQuery);
</script>