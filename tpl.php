<?php

use TrsVendors\Dgm\Shengine\Conditions\DestinationCondition;
use TrsVendors\Dgm\Shengine\Interfaces\IItemAggregatables;
use TrsVendors\Dgm\Shengine\Interfaces\IPackage;
use TrsVendors\Dgm\Shengine\Model\Price;


function trsGetTerms($taxonomy, $nonTermLabel = null)
{
	$terms = get_terms($taxonomy, array('hide_empty' => false, 'fields' => 'id=>name'));
	if (is_wp_error($terms)) {
	    return null;
    }

	if (isset($nonTermLabel)) {
	    $terms = [IPackage::NONE_VIRTUAL_TERM_ID => $nonTermLabel] + $terms;
    }

	return $terms;
}


$terms = array(
    IItemAggregatables::TAXONOMY_SHIPPING_CLASS .':Shipping Classes' => trsGetTerms('product_shipping_class', '[unclassed]'),
    IItemAggregatables::TAXONOMY_TAG .':Tags' => trsGetTerms('product_tag', '[untagged]'),
    IItemAggregatables::TAXONOMY_CATEGORY .':Categories' => trsGetTerms('product_cat')
);

foreach (wc_get_attribute_taxonomies() as $attr) {

    $taxonomy = wc_attribute_taxonomy_name($attr->attribute_name);
    $label = $attr->attribute_label ?: $attr->attribute_name;
    $taxterms = trsGetTerms($taxonomy, '[unassigned]');

    if (isset($taxterms)) {
        $terms["{$taxonomy}:{$label}"] = $taxterms;
    }
}

function trsTermsOptions($terms)
{
    $result = array();

    foreach ($terms as $group => $items) {

        list($idprefix, $label) = explode(':', $group);

        $children = array();
        foreach ($items as $id => $caption) {
           $children[] = array(
               'id' => "{$idprefix}:{$id}",
               'text' => $caption,
           );
        }

        $result[] = array(
            'text' => $label,
            'children' => $children,
        );
    }

    return json_encode($result);
}

$regions = array();
foreach (WC()->countries->get_shipping_countries() as $cc => $country) {

    $regions[$cc] = $country;

    if ($states = WC()->countries->get_states($cc)) {
        foreach ($states as $sc => $state) {
            $regions["{$cc}:{$sc}"] = "{$country} — {$state}";
        }
    }
}

$coupons = call_user_func(static function() {

    $query = new WP_Query(array(
        'post_type'   => 'shop_coupon',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    ));

    $coupons = array();
    foreach ($query->posts as $post) {
        if ($post->post_title) {
            $coupons[$post->post_title] = $post->post_title;
        }
    }

    return $coupons;
});


$roles = function_exists('wp_roles') ? wp_roles() : $GLOBALS['wp_roles'];
$userRoles = $roles->role_names;

$currencyPosParts = explode('_', get_option('woocommerce_currency_pos'), 2);
$currencyDecoratorPosition = reset($currencyPosParts);

require(__DIR__.'/tpl/shipping-methods.php');
require(__DIR__.'/tpl/admin.php');
?>

    <script>
        window.trsTerms = <?php echo trsTermsOptions($terms); ?>;
    </script>

    <div class="trs" id="trs">
    </div>

    <script id="trs_template" type="text/html">
        <div class="rules">
            <ul>
                <li class="root">
                    {{#with rule}}
                        {{#if .children.length}}

                            {{#with .operations.list.0.add.calculator.children}}
                                <div class="root-options">

                                    <span class="helper prefix">Show</span>

                                    <select value="{{.aggregator}}">
                                        <option value="all">all</option>
                                        <option value="min">lowest</option>
                                        <option value="max">highest</option>
                                        <option value="first">first</option>\
                                        <option value="last">last</option>\
                                        <option value="sum">sum</option>
                                    </select>

                                    <span class="helper">

                                        {{#if .aggregator == 'all'}}
                                            applicable shipping options
                                        {{elseif .aggregator == 'sum'}}
                                            of applicable charges as a single shipping option
                                        {{else}}
                                            applicable shipping option
                                        {{/if}}

                                        {{#if .aggregator != 'sum'}}
                                            to the customer
                                        {{/if}}
                                    </span>
                                </div>
                            {{/with}}

                            {{#with .children}}
                                {{>children}}
                            {{/with .children}}


                            <br><br>
                            <div class="dropdown input-group">
                                <button class="button bigger-button main-input right" type="button" on-click="add">
                                    <i class="fa fa-plus"></i>
                                    Add rule
                                </button>

                                <button class="button bigger-button input-decorator right dropdown-handle" type="button">
                                    <i class="fa fa-caret-down"></i>
                                </button>

                                <ul class="dropdown-menu">
                                    {{#each snippets:snippet}}
                                        <li>
                                            <a on-click="add:'append','snippet','{{snippet}}'">
                                                {{.title}}
                                            </a>
                                        </li>
                                    {{/each}}
                                </ul>
                            </div>

                        {{else}}

                            <div class="greeting">
                                <p>Start with an example:</p>

                                {{#each snippets:snippet}}
                                    <button class="button bigger-button snippet-button" type="button" on-click="add:'append','snippet','{{snippet}}'">
                                        {{.title}}
                                        <span class="snippet-button-subline">{{.subtitle}}</span>
                                    </button>
                                {{/each}}
                                
                                <button class="button bigger-button snippet-button empty-snippet-button" type="button" on-click="add">
                                    Start from scratch
                                </button>
                            </div>

                        {{/if .children.length}}

                    {{/with rule}}
                </li>
            </ul>
        </div>

        {{#partial children}}
            {{#if .length}}
                <ul class="rule-list">
                    {{#each .}}
                        {{>rule}}
                    {{/each}}
                </ul>
            {{/if .length}}
        {{/partial}}

        {{#partial rule}}
            <li class="rule-item
                {{this._view.showExpanded ? 'expanded' : 'collapsed'}}
                {{this._view.showSettings ? 'settings-open' : null}}
                {{this.children.length ? 'has-children' : 'no-children'}}"
                intro-outro='slide:{"duration": 200}'
            >
                <div class="content">

                    <div class="header hoverable" on-click="settings" title="click to toggle settings, drag to move in hierarchy">
                        <span>
                            <i class="toggle expand fa fa-plus-square-o" on-click="toggle"></i>
                            <i class="toggle collapse fa fa-minus-square-o" on-click="toggle"></i>
                        </span>

                        <input class="rule-enable" type="checkbox" checked="{{.meta.enable}}" title="Enable or disable this rule">

                        <input class="rule-label" type="text" value="{{.meta.label}}" decorator="autosize"
                               title="Rule label (not shown to the customer)"
                               placeholder="Rule label (not shown to the customer)">
                        
                        <div class="rule-hint hoverable-target">
                            <a class="rule-toggle">
                                {{#._view.showSettings}}hide{{else}}show{{/}} settings
                            </a>
                            <span class="rule-drag">
                                <i class="fa fa-arrows"></i> move
                            </span>
                        </div>


                        <div class="actions">
                            <div class="dropdown input-group">
                                <button class="add button main-input right" type="button" on-click="add:append">
                                    <i class="fa fa-plus"></i>
                                    Add child rule
                                </button>

                                <button class="button input-decorator right dropdown-handle" type="button">
                                    <i class="fa fa-caret-down"></i>
                                </button>

                                <ul class="dropdown-menu">
                                    <li>
                                        <a on-click="add:after">
                                            Add sibling rule
                                        </a>
                                    </li>
                                    <li>
                                        <a on-click="add:'after','clone'">
                                            Duplicate
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <div class="dropdown input-group">
                                <button class="remove button {{#.children.length}}main-input right{{/}}"
                                        type="button" on-click="remove:keepchildren">
                                    <i class="fa fa-remove"></i>
                                    Remove
                                </button>

                                {{#if this.children.length}}
                                    <button class="button input-decorator right dropdown-handle" type="button">
                                        <i class="fa fa-caret-down"></i>
                                    </button>

                                    <ul class="dropdown-menu">
                                        <li>
                                            <a on-click="remove">
                                                With child rules
                                            </a>
                                        </li>
                                    </ul>
                                {{/if this.children.length}}
                            </div>

                        </div>
                    </div>

                    {{#if ._view.showSettings}}
                        <div class="settings form panel" intro-outro="slide">
                            
                            <div class="rate-title">
                                <input type="text" placeholder="Shipping option title" value="{{.meta.title}}" size="30">
                                
                                <Hint>
                                    This controls the title which the customer sees during checkout.<br>
                                    If there are multiple rules applied to the order the last non-empty title
                                    is shown to the customer.
                                </Hint>
                                
                                <button
                                    class="button advanced-settings-button 
                                    {{#showExtended(.)}}active{{/}} 
                                    {{#!isUnextendable(.)}}disabled{{/}}"
                                    type="button" on-click="extend"
                                >
                                    Advanced settings
                                </button>
                            </div>

                            <!--suppress CheckEmptyScriptTag -->
                            <Conditions meta="{{.conditions.meta}}" list="{{.conditions.list}}" rule="{{.}}" />

                            <!--suppress CheckEmptyScriptTag -->
                            <Operations meta="{{.operations.meta}}" list="{{.operations.list}}" rule="{{.}}" />
                        </div>
                    {{/if this._view.showSettings}}
                </div>

                {{#if ._view.showExpanded}}
                    {{#with .children}}
                        {{>children}}
                    {{/with .children}}
                {{/if ._view.showExpanded}}
            </li>
        {{/partial rule}}
    </script>

    <script id="trs_conditions" type="text/html">
        <fieldset class="conditions">
            <?php $sectionHint = "
                Specify what {{#showExtended(.rule)}}packages{{else}}orders{{/}} you want this rule to process.<br>
                Leaving the conditions list empty will make this rule {{#showExtended(.rule)}}matching items from all packages{{else}}handling all orders{{/}}.
            "; ?>

            <header class="section-header">
                <legend>
                    <span class="strong">When</span>

                    {{#if showExtended(.rule)}}
                        {{#if .meta.grouping}}
                            <select value="{{.meta.require_all_packages}}">
                                <option value="0">any</option>
                                <option value="1">all</option>
                            </select>
                        {{/if}}
                    
                        {{#if .meta.require_all_packages == '1' && .meta.grouping}}
                            packages match
                        {{else}}
                            package matches
                        {{/if}}
                    {{else}}
                        the order matches
                    {{/if}}

                    {{#if .list.length != 1}}
                        <select value="{{.meta.mode}}">
                            <option value="and">all</option>
                            <option value="or">any</option>
                        </select>
                        conditions
                    {{else}}
                        the condition
                    {{/if}}
                    below

                    {{#if showExtended(.rule)}}
                        <Hint><?php echo $sectionHint ?></Hint>
                    {{/if}}
                </legend>
            </header>

            {{#if showExtended(.rule) }}
                <div intro-outro='slide'>
                    <label>
                        <span><em class="label">Package</em></span>

                        <span class="main-cell">
                            <select value="{{.meta.grouping}}">
                                <option value="">all items at once</option>
                                <optgroup label="Common">
                                    <option value="classes">by shipping class</option>
                                    <option value="product_variation">by order line</option>
                                    <option value="item">by item</option>
                                </optgroup>
                                <optgroup label="Additional">
                                    <option value="tags">by tag</option>
                                    <option value="categories">by category</option>
                                    <option value="product">by product</option>
                                </optgroup>
                            </select>
                            <Hint>
                                Specify how to split order items into one or more packages before
                                testing them against conditions below. Each item package is matched
                                or discarded separately. Choose the first option if unsure.
                            </Hint>
                        </span>
                    </label>

                    <label>
                        <span><em class="label">Capture</em></span>

                        <span class="main-cell">
                            <input type="checkbox" checked="{{.meta.capture}}">
                            <span class="afterlabel">Capture matching packages</span>
                            <Hint>
                                This controls which order items will be passed to the next sibling rule
                                (if there is one). When this is enabled, matching packages are "captured"
                                by this rule, and the next rule does only get items not captured by
                                this one. In the other case, the next sibling rule receives all items,
                                after they are processed by this rule; so you can process some items
                                with two or more rules.
                            </Hint>
                        </span>
                    </label>
                </div>
            {{/if}}

            {{#if !.list.length}}
                <p class="section-hint"><?php echo $sectionHint ?></p>
            {{/if}}

            <ul class="rows">
                {{#each .list}}
                    <li class="row sort-handle" decorator="sortable">
                        <span class="main-cell">
                            {{>condition}}
                        </span>
                        <span>
                            {{#if !hideDelete}}
                                <a class="button x-remove" title="Remove" on-click="remove"><i class="fa fa-remove"></i></a>
                            {{/if}}
                        </span>
                    </li>
                {{/each}}
            </ul>

            {{#if !hideAdd}}
                <span class="row row-add" intro="slide">
                    <span class="main-cell">
                        <a class="button add" on-click="add"><i class="fa fa-plus"></i> Add condition</a>
                    </span>
                    <span></span>
                </span>
            {{/if}}
        </fieldset>

        {{#partial condition}}
            <select value="{{.condition}}">
                <option value="true">Any order</option>
                <option disabled class="separator">────────</option>
                <option value="terms">Contains</option>
                <option disabled class="separator">────────</option>
                <option value="weight">Weight</option>
                <option value="price">Subtotal</option>
                <option value="count">Quantity</option>
                <option value="volume">Volume</option>
                <option value="package">Dimensions</option>
                <option disabled class="separator">────────</option>
                <option value="destination">Destination</option>
                <option value="coupons">Coupons</option>
                <option value="customer">Customer</option>
            </select>

            {{#with .[.condition]}}

                {{#if ../condition == 'terms'}}
                    <?php listTermsCondition('Select shipping classes, tags, categories') ?>

                {{elseif ../condition == 'coupons'}}
                    <?php listConditionPartial($coupons, 'Specify one or more coupons'); ?>

                {{elseif ../condition == 'destination'}}
                    <?php listConditionPartial(
                            $regions, 'Specify countries, states and postal codes',
                            sprintf('destinationList:%s,%s,%s,%s,%s',
                                json_encode(DestinationCondition::POSTAL_CODE_CONSTRAINT_DELIMITER),
                                json_encode(DestinationCondition::POSTAL_CODE_RANGE_DELIMITER === '...' ? '.' : DestinationCondition::POSTAL_CODE_RANGE_DELIMITER),
                                json_encode(DestinationCondition::POSTAL_CODE_RANGE_DELIMITER === '...' ? '…' : ''),
                                json_encode(DestinationCondition::POSTAL_CODE_RANGE_DELIMITER),
                                json_encode(DestinationCondition::POSTAL_CODE_RANGE_DELIMITER)
                            )
                    ); ?>

                {{elseif ../condition == 'customer'}}
                    <select value="{{.attribute}}" style="display: none">
                        <option value="roles">Role</option>
                    </select>
                    <?php listConditionPartial($userRoles, 'Select roles') ?>

                {{else}}
                    {{>aggregated_conditions}}
                {{/if}}
            {{/with}}
        {{/partial condition}}


        {{#partial aggregated_conditions}}

            {{#if ../condition == 'weight'}}
                {{>number_condition}}

            {{elseif ../condition == 'price'}}
                <?php priceKind() ?>
                {{>number_condition}}

            {{elseif ../condition == 'volume'}}
                {{>number_condition}}

            {{elseif ../condition == 'count'}}
                {{>number_condition}}

           {{elseif ../condition == 'package'}}
                <span class="helper">{{#!perItemGrouping}}items{{else}}item{{/}}</span>

                <select value="{{.operator}}">
                    <option value="smaller">{{#!perItemGrouping}}can be packed{{else}}fits{{/}}</option>
                    <option value="larger">{{#!perItemGrouping}}cannot be packed{{else}}does not fit{{/}}</option>
                </select>

                <span class="helper">into a box</span>

                <span class="decorated-input">
                    <input value="{{.box[0]}}" type="text" class="wc_input_decimal" placeholder="length" required>
                    {{>input_decorator}}
                </span>
                x
                <span class="decorated-input">
                    <input value="{{.box[1]}}" type="text" class="wc_input_decimal" placeholder="width" required>
                    {{>input_decorator}}
                </span>
                x
                <span class="decorated-input">
                    <input value="{{.box[2]}}" type="text" class="wc_input_decimal" placeholder="height" required>
                    {{>input_decorator}}
                </span>
            {{/if}}

        {{/partial}}


        {{#partial input_decorator}}
            {{#if ../condition == 'weight'}}
                <span class="input-decorator right"><?php esc_html_e(get_option('woocommerce_weight_unit'), 'woocommerce') ?></span>
            {{elseif ../condition == 'price'}}
                <span class="input-decorator <?php echo esc_html($currencyDecoratorPosition) ?>"><?php echo(get_woocommerce_currency_symbol()) ?></span>
            {{elseif ../condition == 'volume'}}
                <span class="input-decorator right"><?php esc_html_e(get_option('woocommerce_dimension_unit'), 'woocommerce') ?><sup>3</sup></span>
            {{elseif ../condition == 'package'}}
                <span class="input-decorator right"><?php esc_html_e(get_option('woocommerce_dimension_unit'), 'woocommerce') ?></span>
            {{elseif ../condition == 'count'}}
                <span class="input-decorator right">item(s)</span>
            {{/if}}
        {{/partial}}

        {{#partial number_condition}}
            <select value="{{.operator}}">
                <option value="btw">between</option>
                <option value="lt">below</option>
                <option value="gt">above</option>
                <option value="lte">below or equal</option>
                <option value="gte">above or equal</option>
                <option value="eq">equal</option>
                <option value="ne">not equal</option>
            </select>

            {{#if .operator == 'btw'}}
                <span class="decorated-input">
                    <input value="{{.min}}" {{#condition == 'count'}}{{>whole_number}}{{else}}{{>decimal_number}}{{/}} placeholder="min">
                    {{>input_decorator}}
                </span>
                <span class="helper">and</span>
                <span class="decorated-input">
                    <input value="{{.max}}" {{#condition == 'count'}}{{>whole_number}}{{else}}{{>decimal_number}}{{/}} placeholder="max">
                    {{>input_decorator}}
                </span>
            {{else}}
                <span class="decorated-input">
                    <input value="{{.value}}" {{#condition == 'count'}}{{>whole_number}}{{else}}{{>decimal_number}}{{/}} required>
                    {{>input_decorator}}
                </span>
            {{/if}}
        {{/partial}}

        {{#partial whole_number}}
            class="whole-number" type="number" min="1"
        {{/partial}}

        {{#partial decimal_number}}
            class="wc_input_decimal" type="text"
        {{/partial}}

        {{#partial list_operator}}
            <select value="{{.operator}}">
                {{#if isAlwaysSingle(list[@index])}}
                    <option value="intersect">is</option>
                    <option value="disjoint">is not</option>

                {{elseif list[@index].condition == 'coupons'}}
                    <option value="intersect">applied</option>
                    <option value="disjoint">not applied</option>
                    <option value="empty">none applied</option>

                {{else}}
                    <option value="any">any specified & maybe others</option>
                    <option value="any&only">any specified & no others</option>
                    <option value="all">all specified & maybe others</option>
                    <option value="all&only">all specified & no others</option>
                    <option value="no">no specified & maybe others</option>
                {{/if}}
            </select>
        {{/partial}}
    </script>

    <script id="trs_operations" type="text/html">
        <?php $sectionHint = "
            Set shipping costs for {{#showExtended(.rule)}}items{{else}}orders{{/}} matching the conditions above.
        "; ?>

        <?php // a trick to capture child rules into the local component's data object ?>
        {{#if children}}{{/if}}

        <fieldset class="operations">
            <header class="section-header">
                <legend>
                    <span class="strong">Charge</span>

                    {{#if visibleOperationsCount != 1}}
                        sum of the following fees
                    {{/if}}

                    {{#if showExtended(.rule)}}
                        <Hint><?php echo $sectionHint ?></Hint>
                    {{/if}}
                </legend>
            </header>

            {{#if showExtended(.rule)}}
                <div intro-outro='slide'>
                    <label>
                        <span><em class="label">Calculate fees</em></span>

                            <span class="main-cell">
                                <select value="{{.meta.grouping}}">
                                    <option value="">for all matching items at once</option>
                                    <optgroup label="Common">
                                        <option value="classes">for each shipping class</option>
                                        <option value="product_variation">for each order line</option>
                                        <option value="item">for each item</option>
                                    </optgroup>
                                    <optgroup label="Additional">
                                        <option value="tags">for each tag</option>
                                        <option value="categories">for each category</option>
                                        <option value="product">for each product</option>
                                    </optgroup>
                                </select>
                                <Hint>
                                    Specify how to split matching order items into one or more packages before fee calculation
                                    takes place. Item packages are processed separately. Resulting shipping costs for each
                                    package are added up. This affects child rules: they are processed for each
                                    package individually, also. Choose first option if unsure.
                                </Hint>
                            </span>
                    </label>
                </div>
            {{/if}}

            {{#if !visibleOperationsCount}}
                <p class="section-hint"><?php echo $sectionHint ?></p>
            {{/if}}

            <ul class="rows">
                {{#each .list:operationIndex}}
                    {{#if isOperationVisible(operationIndex)}}
                        <li class="operation calculator-{{.[.operation].calculator.calculator}} row sort-handle" decorator="sortable">
                            <span class="main-cell">
                                {{>operation}}
                            </span>
                            <span>
                                {{#if !hideDelete(.)}}
                                    <a class="button x-remove" title="Remove" on-click="remove"><i class="fa fa-remove"></i></a>
                                {{/if}}
                            </span>
                        </li>
                    {{/if}}
                {{/each}}
            </ul>

            {{#if !hideAdd()}}
                <span class="row" intro="slide" style="display: block; overflow: hidden">
                    <span class="main-cell">
                        <a class="button add" on-click="add"><i class="fa fa-plus"></i> Add fee</a>
                    </span>
                    <span></span>
                </span>
            {{/if}}
        </fieldset>

        {{#partial operation}}

                <select value="{{.operation}}" class="operation-{{.operation}}">
                    <option value="add">Plus</option>
                    <option value="clamp">Clamp</option>
                </select>

                {{#with .[.operation]}}
                    {{#if operation == 'add'}}
                        {{#with .calculator}}
                            <select value="{{.calculator}}">
                                <option value="free">Free</option>
                                <option disabled class="separator">────────────────────</option>
                                <option value="const">Flat fee</option>
                                <option value="percentage">Percentage</option>
                                <option disabled class="separator">────────────────────</option>
                                <option value="weight">Weight rate</option>
                                <option value="count">Quantity rate</option>
                                <option value="volume">Volume rate</option>
                                <option value="price">Subtotal rate</option>
                                <option disabled class="separator">────────────────────</option>
                                <option value="shipping_method">External rates</option>
                                {{#if children.length || .calculator == 'children'}}
                                    <option disabled class="separator">────────────────────</option>
                                    <option value="children">Child rules' rates</option>
                                {{/if}}
                            </select>

                            {{#with .[.calculator]}}
                                {{#if calculator == 'const'}}
                                    <span class="decorated-input">
                                        <input class="wc_input_decimal" type="text" value="{{.value}}" required>
                                        {{>currency_decorator}}
                                    </span>
                                {{elseif calculator == 'weight'}}
                                    {{>stepped_calculator}}
                                {{elseif calculator == 'count'}}
                                    {{>stepped_calculator}}
                                {{elseif calculator == 'volume'}}
                                    {{>stepped_calculator}}
                                {{elseif calculator == 'price'}}
                                    {{>stepped_calculator}}
                                {{elseif calculator == 'percentage'}}
                                    <span class="decorated-input">
                                        <input class="wc_input_decimal" type="text" value="{{.value}}" required>
                                        <span class="input-decorator right">%</span>
                                    </span>
                                    <span class="helper">of</span>
                                    <select value="{{.target}}">
                                        <option value="package_price">
                                            subtotal
                                        </option>
                                        <option value="current_rates" class="{{#!getVisibleOperationsCount(operationIndex)}}meaningless{{/}}">
                                            fees above ({{getVisibleOperationsCount(operationIndex)}})
                                        </option>
                                    </select>

                                    {{#if .target == 'package_price'}}
                                        <?php priceKind() ?>
                                    {{/if}}
                                {{elseif calculator == 'shipping_method'}}

                                    {{>aggregator}}

                                    <span class="nowrap">
                                        <span class="helper prefix">named</span>
                                        <input type="text" value="{{.rate_name}}" placeholder="*">
                                    </span>

                                    <HintHandle/>
                                    <br>
                                    <select multiple value="{{.ids}}" class="multiselect" decorator="select2:shipping-methods"></select>

                                    <HintContent>
                                        <p>Use wildcards for partial name match.</p>

                                        Examples:
                                        <ul>
                                            <li><code>USPS</code> matches 'USPS', 'usps'; doesn't match 'Flat Rate (USPS)'.</li>
                                            <li><code>USPS<b>*</b></code> matches 'usps', 'USPS: Flat Rate'; doesn't match 'Flat Rate (USPS)'.</li>
                                            <li><code><b>*</b>USPS<b>*</b></code> matches 'USPS', 'USPS Flat Rate', 'Flat Rate (USPS)'.</li>
                                            <li><code><b>*</b></code> matches anything</li>
                                        </ul>
                                    </HintContent>


        {{elseif calculator == 'children'}}
                                    {{>aggregator}}
                                {{/if}}
                            {{/with}}
                        {{/with}}
                    {{elseif operation == 'clamp'}}
                        <span class="helper prefix">between</span>
                        <span class="decorated-input">
                            <input type="text" class="wc_input_decimal" value="{{.min}}" placeholder="min">
                            {{>currency_decorator}}
                        </span>
                        <span class="helper">and</span>
                        <span class="decorated-input">
                            <input type="text" class="wc_input_decimal" value="{{.max}}" placeholder="max">
                            {{>currency_decorator}}
                        </span>
                    {{/if}}
                {{/with .[.operation]}}

        {{/partial}}

        {{#partial step_unit}}
            <span class="input-decorator {{#calculator=='price'}}<?php esc_html_e($currencyDecoratorPosition) ?>{{else}}right{{/}}">
                {{#if calculator == 'weight'}}
                    <?php esc_html_e(get_option('woocommerce_weight_unit'), 'woocommerce') ?>
                {{elseif calculator == 'volume'}}
                    <?php esc_html_e(get_option('woocommerce_dimension_unit'), 'woocommerce') ?><sup>3</sup>
                {{elseif calculator == 'price'}}
                    <?php echo(get_woocommerce_currency_symbol()) ?>
                {{else}}
                    item(s)
                {{/if}}
            </span>
        {{/partial}}

        {{#partial currency_decorator}}
            <span class="input-decorator <?php esc_html_e($currencyDecoratorPosition) ?>">
                <?php echo(get_woocommerce_currency_symbol()) ?>
            </span>
        {{/partial}}

        {{#partial stepped_calculator}}
            <span class="helper prefix">add</span>
            <span class="decorated-input">
                <input class="wc_input_decimal" type="text" value="{{.cost}}" required>
                {{>currency_decorator}}
            </span>

            <span class="helper prefix">for each</span>
            <span class="decorated-input">
                <input {{#calculator == 'count'}}{{>whole_number}}{{else}}{{>decimal_number}}{{/}} value="{{.step}}" placeholder="1">
                {{>step_unit}}
            </span>

            <span class="{{#!parseFloat(.skip)}}inactive-input{{/}}">
                <span class="helper prefix">over</span>
                <span class="decorated-input">
                    <input {{#calculator == 'count'}}{{>whole_number_min_zero}}{{else}}{{>decimal_number}}{{/}} value="{{.skip}}" placeholder="0">
                    {{>step_unit}}
                </span>
            </span>

            {{#if calculator == 'price'}}
                <span title="Choose the kind of subtotal used for 'for each' and 'over' fields.">
                    <span class="helper prefix">take</span>
                    <?php priceKind() ?>
                </span>
            {{/if}}
        {{/partial}}

        {{#partial whole_number}}
            class="whole-number" type="number" min="1"
        {{/partial}}

        {{#partial whole_number_min_zero}}
            class="whole-number" type="number" min="0"
        {{/partial}}

        {{#partial decimal_number}}
            class="wc_input_decimal" type="text"
        {{/partial}}

        {{#partial aggregator}}
            <span class="nowrap">
                <span class="helper prefix">add</span>
                <select value="{{.aggregator}}">
                    {{#if calculator == 'children'}}
                        <option value="sum">sum</option>
                    {{/if}}
                    <option value="min">lowest</option>
                    <option value="max">highest</option>
                    <option value="first">first</option>
                    <option value="last">last</option>
                    {{#if calculator != 'children'}}
                        <option value="sum">sum</option>
                    {{/if}}
                    <option value="all">all</option>
                </select>
                <span class="helper">
                    {{#if .aggregator == 'all'}}
                        {{#if calculator == 'children'}}child{{/if}} rates
                    {{elseif .aggregator == 'sum'}}
                        of {{#if calculator == 'children'}}child{{/if}} rates
                    {{else}}
                        {{#if calculator == 'children'}}child{{/if}} rate
                    {{/if}}
                </span>
            </span>
        {{/partial}}
    </script>

    <?php require(__DIR__.'/tpl/hint/hint.php') ?>

<?php
    function priceKind()
    {
        ?>
            <select value="{{.price_kind}}">
                <option value="<?php echo esc_html(Price::BASE) ?>">without tax and discount</option>
                <option value="<?php echo esc_html(Price::WITH_DISCOUNT) ?>">with discount</option>
                <option value="<?php echo esc_html(Price::WITH_TAX) ?>">with tax</option>
                <option value="<?php echo esc_html(Price::WITH_TAX|Price::WITH_DISCOUNT) ?>">with tax and discount</option>
            </select>
        <?php
    }

    function listTermsCondition($placeholder, $decorator = 'select2')
    {
        ?>
        {{>list_operator}}

        <select
            multiple
            value="{{.value}}"
            class="multiselect"
            decorator="<?php echo esc_html($decorator) ?>"
            data-placeholder="<?php echo esc_html($placeholder) ?>"
            data-terms="true"
        >
        </select>

        {{#if operator != 'no'}}
            <span class="nowrap">
                {{#with .subcondition}}

                    <select value="{{.condition}}">
                        <option value="">any amount</option>
                        <option value="count">quantity</option>
                        <option value="weight">weight</option>
                        <option value="price">subtotal</option>
                        <option value="volume">volume</option>
                        <option value="package">dimensions</option>
                    </select>

                    {{#with .[.condition]}}
                        {{#if ../condition}}
                            {{>aggregated_conditions}}
                        {{/if}}
                    {{/with}}
                {{/with}}
            </span>
        {{/if}}

        <?php
    }

    function listConditionPartial($items, $placeholder, $decorator = 'select2')
    {
        ?>
            {{>list_operator}}

            {{#if .operator != 'empty'}}
                <select multiple
                        value="{{.value}}"
                        class="multiselect"
                        decorator="<?php echo esc_html($decorator) ?>"
                        data-placeholder="<?php echo esc_html($placeholder) ?>"
                        {{#condition == 'destination'}}data-select2-selected-title="Click to add zip/postal codes"{{/}}
                >
                    <?php listConditionsPartialOutputChoices($items) ?>
                </select>
            {{/if}}

            {{#if condition == 'destination'}}
                <a onclick="jQuery(this).nextAll('.hint-handle')[0].click(); return false;" class="hint-link">zip/postal codes?</a>

                <Hint>
                    <p>Hold Ctrl key down to select multiple items at once.</p>
                    <p>
                        To input <b>zip/postal codes</b> select a country/state from the dropdown, then click on
                        the appeared tag and type in codes after a colon.<br>
                        Separate multiple post codes with commas: 123<b class="accent">,</b> 1234<b class="accent">,</b> 12345.<br>
                        Use asterisk for wildcards: 12<b class="accent">*</b>, XX<b class="accent">*</b>, <b class="accent">*</b>ABC<b class="accent">*</b>.<br>
                        Type in three dots for ranges: 12000<b class="accent">...</b>12299, XX100<b class="accent">...</b>XX999.<br>
                        Wildcards in ranges are <b>not</b> supported.
                    </p>

                    Examples:
                    <ul>
                        <li>United Kingdom (UK): SE*, NW*</li>
                        <li>United States (US) — New York: 10001...10034, 10040, 112*<br></li>
                    </ul>
                </Hint>
            {{/if}}
        <?php
    }

    function listConditionsPartialOutputChoices($items, $idprefix = null)
    {
        foreach ($items as $id => $name) {
            ?> <option value="<?php echo esc_html($idprefix.$id) ?>"><?php echo esc_html($name) ?></option> <?php
        }
    }
?>
