<?php

use App\Http\Controllers\DataTableDemoController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});

Route::view('index', 'index')->name('index');
Route::view('ecommerce_dashboard', 'ecommerce_dashboard')->name('ecommerce_dashboard');

Route::view('accordions', 'accordions')->name('accordions');
Route::view('add_blog', 'add_blog')->name('add_blog');
Route::view('add_product', 'add_product')->name('add_product');
Route::view('advance_table', 'advance_table')->name('advance_table');
Route::view('alert', 'alert')->name('alert');
Route::view('alignment', 'alignment')->name('alignment');
Route::view('animated_icon', 'animated_icon')->name('animated_icon');
Route::view('animation', 'animation')->name('animation');
Route::view('api', 'api')->name('api');
Route::view('area_chart', 'area_chart')->name('area_chart');
Route::view('avatar', 'avatar')->name('avatar');

Route::view('background', 'background')->name('background');
Route::view('badges', 'badges')->name('badges');
Route::view('bar_chart', 'bar_chart')->name('bar_chart');
Route::view('base_inputs', 'base_inputs')->name('base_inputs');
Route::view('basic_table', 'basic_table')->name('basic_table');
Route::view('blank', 'blank')->name('blank');
Route::view('block_ui', 'block_ui')->name('block_ui');
Route::view('blog', 'blog')->name('blog');
Route::view('blog_details', 'blog_details')->name('blog_details');
Route::view('bookmark', 'bookmark')->name('bookmark');
Route::view('bootstrap_slider', 'bootstrap_slider')->name('bootstrap_slider');
Route::view('boxplot_chart', 'boxplot_chart')->name('boxplot_chart');
Route::view('bubble_chart', 'bubble_chart')->name('bubble_chart');
Route::view('bullet', 'bullet')->name('bullet');
Route::view('buttons', 'buttons')->name('buttons');

Route::view('calendar', 'calendar')->name('calendar');
Route::view('candlestick_chart', 'candlestick_chart')->name('candlestick_chart');
Route::view('cards', 'cards')->name('cards');
Route::view('cart', 'cart')->name('cart');
Route::view('chart_js', 'chart_js')->name('chart_js');
Route::view('chat', 'chat')->name('chat');
Route::view('cheatsheet', 'cheatsheet')->name('cheatsheet');
Route::view('checkbox_radio', 'checkbox_radio')->name('checkbox_radio');
Route::view('checkout', 'checkout')->name('checkout');
Route::view('clipboard', 'clipboard')->name('clipboard');
Route::view('collapse', 'collapse')->name('collapse');
Route::view('column_chart', 'column_chart')->name('column_chart');
Route::view('coming_soon', 'coming_soon')->name('coming_soon');
Route::view('count_down', 'count_down')->name('count_down');
Route::view('count_up', 'count_up')->name('count_up');

Route::view('data_table', 'data_table')->name('data_table');
Route::view('date_picker', 'date_picker')->name('date_picker');
Route::view('default_forms', 'default_forms')->name('default_forms');
Route::view('divider', 'divider')->name('divider');
Route::view('draggable', 'draggable')->name('draggable');
Route::view('dropdown', 'dropdown')->name('dropdown');
Route::view('dual_list_boxes', 'dual_list_boxes')->name('dual_list_boxes');

Route::view('editor', 'editor')->name('editor');
Route::view('email', 'email')->name('email');
Route::view('error_400', 'error_400')->name('error_400');
Route::view('error_403', 'error_403')->name('error_403');
Route::view('error_404', 'error_404')->name('error_404');
Route::view('error_500', 'error_500')->name('error_500');
Route::view('error_503', 'error_503')->name('error_503');

Route::view('faq', 'faq')->name('faq');
Route::view('file_manager', 'file_manager')->name('file_manager');
Route::view('file_upload', 'file_upload')->name('file_upload');
Route::view('flag_icons', 'flag_icons')->name('flag_icons');
Route::view('floating_labels', 'floating_labels')->name('floating_labels');
Route::view('fontawesome', 'fontawesome')->name('fontawesome');
Route::view('footer_page', 'footer_page')->name('footer_page');
Route::view('form_validation', 'form_validation')->name('form_validation');
Route::view('form_wizard_1', 'form_wizard_1')->name('form_wizard_1');
Route::view('form_wizard_2', 'form_wizard_2')->name('form_wizard_2');
Route::view('form_wizards', 'form_wizards')->name('form_wizards');

Route::view('gallery', 'gallery')->name('gallery');
Route::view('google_map', 'google_map')->name('google_map');
Route::view('grid', 'grid')->name('grid');

Route::view('heatmap', 'heatmap')->name('heatmap');
Route::view('helper_classes', 'helper_classes')->name('helper_classes');

Route::view('iconoir_icon', 'iconoir_icon')->name('iconoir_icon');
Route::view('input_groups', 'input_groups')->name('input_groups');
Route::view('input_masks', 'input_masks')->name('input_masks');
Route::view('invoice', 'invoice')->name('invoice');

Route::view('kanban_board', 'kanban_board')->name('kanban_board');

Route::view('landing', 'landing')->name('landing');
Route::view('leaflet_map', 'leaflet_map')->name('leaflet_map');
Route::view('line_chart', 'line_chart')->name('line_chart');
Route::view('list', 'list')->name('list');
Route::view('list_table', 'list_table')->name('list_table');
Route::view('lock_screen', 'lock_screen')->name('lock_screen');
Route::view('lock_screen_1', 'lock_screen_1')->name('lock_screen_1');

Route::view('maintenance', 'maintenance')->name('maintenance');
Route::view('misc', 'misc')->name('misc');
Route::view('mixed_chart', 'mixed_chart')->name('mixed_chart');
Route::view('modals', 'modals')->name('modals');
Route::view('notifications', 'notifications')->name('notifications');

Route::view('offcanvas', 'offcanvas')->name('offcanvas');
Route::view('orders', 'orders')->name('orders');
Route::view('order_details', 'order_details')->name('order_details');
Route::view('order_list', 'order_list')->name('order_list');

Route::view('password_create_1', 'password_create_1')->name('password_create_1');
Route::view('password_reset_1', 'password_reset_1')->name('password_reset_1');
Route::view('phosphor', 'phosphor')->name('phosphor');
Route::view('pie_charts', 'pie_charts')->name('pie_charts');
Route::view('placeholder', 'placeholder')->name('placeholder');
Route::view('pricing', 'pricing')->name('pricing');
Route::view('prismjs', 'prismjs')->name('prismjs');
Route::view('privacy_policy', 'privacy_policy')->name('privacy_policy');
Route::view('product', 'product')->name('product');
Route::view('product_details', 'product_details')->name('product_details');
Route::view('product_list', 'product_list')->name('product_list');
Route::view('profile', 'profile')->name('profile');
Route::view('progress', 'progress')->name('progress');
Route::view('project_app', 'project_app')->name('project_app');
Route::view('project_details', 'project_details')->name('project_details');
Route::view('password_create', 'password_create')->name('password_create');
Route::view('password_reset', 'password_reset')->name('password_reset');

Route::view('radar_chart', 'radar_chart')->name('radar_chart');
Route::view('radial_bar_chart', 'radial_bar_chart')->name('radial_bar_chart');
Route::view('range_slider', 'range_slider')->name('range_slider');
Route::view('ratings', 'ratings')->name('ratings');
Route::view('read_email', 'read_email')->name('read_email');
Route::view('ready_to_use_form', 'ready_to_use_form')->name('ready_to_use_form');
Route::view('ready_to_use_table', 'ready_to_use_table')->name('ready_to_use_table');
Route::view('ribbons', 'ribbons')->name('ribbons');

Route::view('scatter_chart', 'scatter_chart')->name('scatter_chart');
Route::view('scrollbar', 'scrollbar')->name('scrollbar');
Route::view('scrollpy', 'scrollpy')->name('scrollpy');
Route::view('select', 'select')->name('select');
Route::view('setting', 'setting')->name('setting');
Route::view('shadow', 'shadow')->name('shadow');
Route::view('sign_in', 'sign_in')->name('sign_in');
Route::view('sign_in_1', 'sign_in_1')->name('sign_in_1');
Route::view('sign_up', 'sign_up')->name('sign_up');
Route::view('sign_up_1', 'sign_up_1')->name('sign_up_1');
Route::view('sitemap', 'sitemap')->name('sitemap');
Route::view('slick_slider', 'slick_slider')->name('slick_slider');
Route::view('spinners', 'spinners')->name('spinners');
Route::view('sweetalert', 'sweetalert')->name('sweetalert');
Route::view('switch', 'switch')->name('switch');

Route::view('tabler_icons', 'tabler_icons')->name('tabler_icons');
Route::view('tabs', 'tabs')->name('tabs');
Route::view('team', 'team')->name('team');
Route::view('terms_condition', 'terms_condition')->name('terms_condition');
Route::view('textarea', 'textarea')->name('textarea');
Route::view('ticket', 'ticket')->name('ticket');
Route::view('ticket_details', 'ticket_details')->name('ticket_details');
Route::view('timeline', 'timeline')->name('timeline');
Route::view('timeline_range_charts', 'timeline_range_charts')->name('timeline_range_charts');
Route::view('to_do', 'to_do')->name('to_do');
Route::view('tooltips_popovers', 'tooltips_popovers')->name('tooltips_popovers');
Route::view('touch_spin', 'touch_spin')->name('touch_spin');
Route::view('tour', 'tour')->name('tour');
Route::view('tree-view', 'tree-view')->name('tree-view');
Route::view('treemap_chart', 'treemap_chart')->name('treemap_chart');
Route::view('two_step_verification', 'two_step_verification')->name('two_step_verification');
Route::view('two_step_verification_1', 'two_step_verification_1')->name('two_step_verification_1');
Route::view('typeahead', 'typeahead')->name('typeahead');

Route::view('vector_map', 'vector_map')->name('vector_map');
Route::view('video_embed', 'video_embed')->name('video_embed');
Route::view('weather_icon', 'weather_icon')->name('weather_icon');
Route::view('widget', 'widget')->name('widget');
Route::view('wishlist', 'wishlist')->name('wishlist');
Route::view('wrapper', 'wrapper')->name('wrapper');

Route::view('index', 'index')->name('index');
Route::view('ecommerce_dashboard', 'ecommerce_dashboard')->name('ecommerce_dashboard');

Route::view('accordions', 'accordions')->name('accordions');
Route::view('add_blog', 'add_blog')->name('add_blog');
Route::view('add_product', 'add_product')->name('add_product');
Route::view('advance_table', 'advance_table')->name('advance_table');
Route::view('alert', 'alert')->name('alert');
Route::view('alignment', 'alignment')->name('alignment');
Route::view('animated_icon', 'animated_icon')->name('animated_icon');
Route::view('animation', 'animation')->name('animation');
Route::view('api', 'api')->name('api');
Route::view('area_chart', 'area_chart')->name('area_chart');
Route::view('avatar', 'avatar')->name('avatar');

Route::view('background', 'background')->name('background');
Route::view('badges', 'badges')->name('badges');
Route::view('bar_chart', 'bar_chart')->name('bar_chart');
Route::view('base_inputs', 'base_inputs')->name('base_inputs');
Route::view('basic_table', 'basic_table')->name('basic_table');
Route::view('blank', 'blank')->name('blank');
Route::view('block_ui', 'block_ui')->name('block_ui');
Route::view('blog', 'blog')->name('blog');
Route::view('blog_details', 'blog_details')->name('blog_details');
Route::view('bookmark', 'bookmark')->name('bookmark');
Route::view('bootstrap_slider', 'bootstrap_slider')->name('bootstrap_slider');
Route::view('boxplot_chart', 'boxplot_chart')->name('boxplot_chart');
Route::view('bubble_chart', 'bubble_chart')->name('bubble_chart');
Route::view('bullet', 'bullet')->name('bullet');
Route::view('buttons', 'buttons')->name('buttons');

Route::view('calendar', 'calendar')->name('calendar');
Route::view('candlestick_chart', 'candlestick_chart')->name('candlestick_chart');
Route::view('cards', 'cards')->name('cards');
Route::view('cart', 'cart')->name('cart');
Route::view('chart_js', 'chart_js')->name('chart_js');
Route::view('chat', 'chat')->name('chat');
Route::view('cheatsheet', 'cheatsheet')->name('cheatsheet');
Route::view('checkbox_radio', 'checkbox_radio')->name('checkbox_radio');
Route::view('checkout', 'checkout')->name('checkout');
Route::view('clipboard', 'clipboard')->name('clipboard');
Route::view('collapse', 'collapse')->name('collapse');
Route::view('column_chart', 'column_chart')->name('column_chart');
Route::view('coming_soon', 'coming_soon')->name('coming_soon');
Route::view('count_down', 'count_down')->name('count_down');
Route::view('count_up', 'count_up')->name('count_up');

Route::view('data_table', 'data_table')->name('data_table');
Route::view('date_picker', 'date_picker')->name('date_picker');
Route::view('default_forms', 'default_forms')->name('default_forms');
Route::view('divider', 'divider')->name('divider');
Route::view('draggable', 'draggable')->name('draggable');
Route::view('dropdown', 'dropdown')->name('dropdown');
Route::view('dual_list_boxes', 'dual_list_boxes')->name('dual_list_boxes');

Route::view('editor', 'editor')->name('editor');
Route::view('email', 'email')->name('email');
Route::view('error_400', 'error_400')->name('error_400');
Route::view('error_403', 'error_403')->name('error_403');
Route::view('error_404', 'error_404')->name('error_404');
Route::view('error_500', 'error_500')->name('error_500');
Route::view('error_503', 'error_503')->name('error_503');

Route::view('faq', 'faq')->name('faq');
Route::view('file_manager', 'file_manager')->name('file_manager');
Route::view('file_upload', 'file_upload')->name('file_upload');
Route::view('flag_icons', 'flag_icons')->name('flag_icons');
Route::view('floating_labels', 'floating_labels')->name('floating_labels');
Route::view('fontawesome', 'fontawesome')->name('fontawesome');
Route::view('footer_page', 'footer_page')->name('footer_page');
Route::view('form_validation', 'form_validation')->name('form_validation');
Route::view('form_wizard_1', 'form_wizard_1')->name('form_wizard_1');
Route::view('form_wizard_2', 'form_wizard_2')->name('form_wizard_2');
Route::view('form_wizards', 'form_wizards')->name('form_wizards');

Route::view('gallery', 'gallery')->name('gallery');
Route::view('google_map', 'google_map')->name('google_map');
Route::view('grid', 'grid')->name('grid');

Route::view('heatmap', 'heatmap')->name('heatmap');
Route::view('helper_classes', 'helper_classes')->name('helper_classes');

Route::view('iconoir_icon', 'iconoir_icon')->name('iconoir_icon');
Route::view('input_groups', 'input_groups')->name('input_groups');
Route::view('input_masks', 'input_masks')->name('input_masks');
Route::view('invoice', 'invoice')->name('invoice');

Route::view('kanban_board', 'kanban_board')->name('kanban_board');

Route::view('landing', 'landing')->name('landing');
Route::view('leaflet_map', 'leaflet_map')->name('leaflet_map');
Route::view('line_chart', 'line_chart')->name('line_chart');
Route::view('list', 'list')->name('list');
Route::view('list_table', 'list_table')->name('list_table');
Route::view('lock_screen', 'lock_screen')->name('lock_screen');
Route::view('lock_screen_1', 'lock_screen_1')->name('lock_screen_1');

Route::view('maintenance', 'maintenance')->name('maintenance');
Route::view('misc', 'misc')->name('misc');
Route::view('mixed_chart', 'mixed_chart')->name('mixed_chart');
Route::view('modals', 'modals')->name('modals');
Route::view('notifications', 'notifications')->name('notifications');

Route::view('offcanvas', 'offcanvas')->name('offcanvas');
Route::view('orders', 'orders')->name('orders');
Route::view('order_details', 'order_details')->name('order_details');
Route::view('order_list', 'order_list')->name('order_list');

Route::view('password_create_1', 'password_create_1')->name('password_create_1');
Route::view('password_reset_1', 'password_reset_1')->name('password_reset_1');
Route::view('phosphor', 'phosphor')->name('phosphor');
Route::view('pie_charts', 'pie_charts')->name('pie_charts');
Route::view('placeholder', 'placeholder')->name('placeholder');
Route::view('pricing', 'pricing')->name('pricing');
Route::view('prismjs', 'prismjs')->name('prismjs');
Route::view('privacy_policy', 'privacy_policy')->name('privacy_policy');
Route::view('product', 'product')->name('product');
Route::view('product_details', 'product_details')->name('product_details');
Route::view('product_list', 'product_list')->name('product_list');
Route::view('profile', 'profile')->name('profile');
Route::view('progress', 'progress')->name('progress');
Route::view('project_app', 'project_app')->name('project_app');
Route::view('project_details', 'project_details')->name('project_details');
Route::view('password_create', 'password_create')->name('password_create');
Route::view('password_reset', 'password_reset')->name('password_reset');

Route::view('radar_chart', 'radar_chart')->name('radar_chart');
Route::view('radial_bar_chart', 'radial_bar_chart')->name('radial_bar_chart');
Route::view('range_slider', 'range_slider')->name('range_slider');
Route::view('ratings', 'ratings')->name('ratings');
Route::view('read_email', 'read_email')->name('read_email');
Route::view('ready_to_use_form', 'ready_to_use_form')->name('ready_to_use_form');
Route::view('ready_to_use_table', 'ready_to_use_table')->name('ready_to_use_table');
Route::view('ribbons', 'ribbons')->name('ribbons');

Route::view('scatter_chart', 'scatter_chart')->name('scatter_chart');
Route::view('scrollbar', 'scrollbar')->name('scrollbar');
Route::view('scrollpy', 'scrollpy')->name('scrollpy');
Route::view('select', 'select')->name('select');
Route::view('setting', 'setting')->name('setting');
Route::view('shadow', 'shadow')->name('shadow');
Route::view('sign_in', 'sign_in')->name('sign_in');
Route::view('sign_in_1', 'sign_in_1')->name('sign_in_1');
Route::view('sign_up', 'sign_up')->name('sign_up');
Route::view('sign_up_1', 'sign_up_1')->name('sign_up_1');
Route::view('sitemap', 'sitemap')->name('sitemap');
Route::view('slick_slider', 'slick_slider')->name('slick_slider');
Route::view('spinners', 'spinners')->name('spinners');
Route::view('sweetalert', 'sweetalert')->name('sweetalert');
Route::view('switch', 'switch')->name('switch');

Route::view('tabler_icons', 'tabler_icons')->name('tabler_icons');
Route::view('tabs', 'tabs')->name('tabs');
Route::view('team', 'team')->name('team');
Route::view('terms_condition', 'terms_condition')->name('terms_condition');
Route::view('textarea', 'textarea')->name('textarea');
Route::view('ticket', 'ticket')->name('ticket');
Route::view('ticket_details', 'ticket_details')->name('ticket_details');
Route::view('timeline', 'timeline')->name('timeline');
Route::view('timeline_range_charts', 'timeline_range_charts')->name('timeline_range_charts');
Route::view('to_do', 'to_do')->name('to_do');
Route::view('tooltips_popovers', 'tooltips_popovers')->name('tooltips_popovers');
Route::view('touch_spin', 'touch_spin')->name('touch_spin');
Route::view('tour', 'tour')->name('tour');
Route::view('tree-view', 'tree-view')->name('tree-view');
Route::view('treemap_chart', 'treemap_chart')->name('treemap_chart');
Route::view('two_step_verification', 'two_step_verification')->name('two_step_verification');
Route::view('two_step_verification_1', 'two_step_verification_1')->name('two_step_verification_1');
Route::view('typeahead', 'typeahead')->name('typeahead');

Route::view('vector_map', 'vector_map')->name('vector_map');
Route::view('video_embed', 'video_embed')->name('video_embed');
Route::view('weather_icon', 'weather_icon')->name('weather_icon');
Route::view('widget', 'widget')->name('widget');
Route::view('wishlist', 'wishlist')->name('wishlist');
Route::view('wrapper', 'wrapper')->name('wrapper');

Route::get('/demos/datatable-static', [DataTableDemoController::class, 'staticDemo'])->name('datatable.static');

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::get('/test-dynamic-datatable', function () {
    return view('test-datatable');
})->name('test.dynamic.datatable');

/**
 * Include routes modules
 */
require __DIR__.'/web/auth.php';
require __DIR__.'/web/dashboard.php';

require __DIR__.'/web/admin.php';
require __DIR__.'/web/orders.php';
require __DIR__.'/web/users.php';
require __DIR__.'/web/bottles.php';
require __DIR__.'/web/deliveries.php';
require __DIR__.'/web/reports.php';
require __DIR__.'/web/settings.php';

require __DIR__.'/web/warehouses.php';
require __DIR__.'/web/supplies.php';
require __DIR__.'/web/orders.php';
