<?php
/*
Plugin Name: My Floating Button
Description: Добавляет плавающую кнопку в правый нижний угол сайта
Version: 0.1
Author: Andrew Arutunyan
*/

// Подключаем стили и скрипты
function fb_enqueue_scripts() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
    wp_enqueue_style('floating-button-style', plugin_dir_url(__FILE__) . 'style.css');
    wp_enqueue_script('floating-button-script', plugin_dir_url(__FILE__) . 'script.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'fb_enqueue_scripts');

// Добавляем HTML кнопки в футер
function fb_add_button() {
    $icon = get_option('fb_button_icon', 'fas fa-arrow-up');
    ?>
    <div class="floating-button">
        <button id="floating-btn"><i class="<?php echo esc_attr($icon); ?>"></i></button>
    </div>
    <?php
}
add_action('wp_footer', 'fb_add_button');

// Настройки в админке
function fb_admin_menu() {
    add_options_page('Floating Button Settings', 'Floating Button', 'manage_options', 'floating-button', 'fb_settings_page');
}
add_action('admin_menu', 'fb_admin_menu');

function fb_settings_page() {
    if(isset($_POST['fb_color'])) {
        update_option('fb_button_color', sanitize_hex_color($_POST['fb_color']));
    }
    if(isset($_POST['fb_icon'])) {
        update_option('fb_button_icon', sanitize_text_field($_POST['fb_icon']));
    }
    if(isset($_POST['fb_position'])) {
        update_option('fb_button_position', sanitize_text_field($_POST['fb_position']));
    }
    
    $color = get_option('fb_button_color', '#007bff');
    $icon = get_option('fb_button_icon', 'fas fa-arrow-up');
    $position = get_option('fb_button_position', 'bottom-right');
    
    $icons = array(
        'fas fa-arrow-up' => 'Стрелка вверх',
        'fas fa-phone' => 'Телефон',
        'fas fa-comment' => 'Чат',
        'fas fa-envelope' => 'Почта',
        'fas fa-question' => 'Вопрос',
        'fas fa-plus' => 'Плюс',
        'fas fa-ghost' => 'Привидение',
    );
    
    $positions = array(
        'bottom-right' => 'Низ-право',
        'bottom-left' => 'Низ-лево',
        'top-right' => 'Верх-право',
        'top-left' => 'Верх-лево'
    );
    ?>
    <div class="wrap">
        <h1>Настройки плавающей кнопки</h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th><label>Цвет кнопки:</label></th>
                    <td><input type="color" name="fb_color" value="<?php echo esc_attr($color); ?>"></td>
                </tr>
                <tr>
                    <th><label>Иконка кнопки:</label></th>
                    <td>
                        <select name="fb_icon">
                            <?php 
                            foreach($icons as $value => $label) {
                                $selected = ($icon === $value) ? 'selected' : '';
                                echo "<option value='$value' $selected>$label</option>";
                            }
                            ?>
                        </select>
                        <p class="description">Выберите иконку из списка</p>
                    </td>
                </tr>
                <tr>
                    <th><label>Положение кнопки:</label></th>
                    <td>
                        <select name="fb_position">
                            <?php 
                            foreach($positions as $value => $label) {
                                $selected = ($position === $value) ? 'selected' : '';
                                echo "<option value='$value' $selected>$label</option>";
                            }
                            ?>
                        </select>
                        <p class="description">Выберите положение кнопки на экране</p>
                    </td>
                </tr>
            </table>
            <input type="submit" value="Сохранить" class="button-primary">
        </form>
    </div>
    <?php
}

// Динамические стили
function fb_dynamic_styles() {
    $color = get_option('fb_button_color', '#007bff');
    $position = get_option('fb_button_position', 'bottom-right');
    
    // Разбиваем положение на вертикаль и горизонталь
    list($vertical, $horizontal) = explode('-', $position);
    
    $custom_css = "
        .floating-button {
            position: fixed;
            {$vertical}: 20px;
            {$horizontal}: 20px;
            z-index: 9999;
        }
        #floating-btn {
            background-color: {$color};
        }
        #floating-btn:hover {
            background-color: " . fb_darken_color($color) . ";
        }";
    wp_add_inline_style('floating-button-style', $custom_css);
}
add_action('wp_enqueue_scripts', 'fb_dynamic_styles');

function fb_darken_color($hex) {
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex,0,2));
    $g = hexdec(substr($hex,2,2));
    $b = hexdec(substr($hex,4,2));
    $r = max(0, $r - 30);
    $g = max(0, $g - 30);
    $b = max(0, $b - 30);
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}