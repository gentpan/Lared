<?php
/**
 * Lared Theme – 关于页面 侧边栏设置
 *
 * 后台「主题设置 → 关于页面」Tab，用于配置：
 *   - 爱好列表（逗号分隔）
 *   - 计划列表（每行一条）
 *   - 关键词 / 标签云（逗号分隔）
 *
 * @package Lared
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ────────────────────────────────────────────
 *  注册 Settings
 * ──────────────────────────────────────────── */
function lared_register_about_settings(): void
{
    register_setting('lared_settings_about', 'lared_about_hobbies', [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_textarea_field',
        'default'           => '',
    ]);

    register_setting('lared_settings_about', 'lared_about_plans', [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_textarea_field',
        'default'           => '',
    ]);

    register_setting('lared_settings_about', 'lared_about_tags', [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_textarea_field',
        'default'           => '',
    ]);
}
add_action('admin_init', 'lared_register_about_settings');

/* ────────────────────────────────────────────
 *  获取数据（供模板使用）
 * ──────────────────────────────────────────── */

/**
 * 获取关于页面侧边栏数据
 *
 * @return array{hobbies: string[], plans: string[], tags: string[]}
 */
function lared_get_about_sidebar_data(): array
{
    $parse = static function (string $raw, string $sep = ','): array {
        if ('' === $raw) {
            return [];
        }
        $items = ',' === $sep
            ? explode(',', $raw)
            : explode("\n", $raw);
        return array_values(array_filter(array_map('trim', $items), static fn($v) => '' !== $v));
    };

    return [
        'hobbies' => $parse((string) get_option('lared_about_hobbies', ''), ','),
        'plans'   => $parse((string) get_option('lared_about_plans', ''), "\n"),
        'tags'    => $parse((string) get_option('lared_about_tags', ''), ','),
    ];
}

/* ────────────────────────────────────────────
 *  后台 Tab 渲染
 * ──────────────────────────────────────────── */
function lared_render_tab_about(): void
{
    $hobbies = (string) get_option('lared_about_hobbies', '');
    $plans   = (string) get_option('lared_about_plans', '');
    $tags    = (string) get_option('lared_about_tags', '');
    ?>
    <form method="post" action="options.php">
        <?php settings_fields('lared_settings_about'); ?>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="lared_about_hobbies"><?php esc_html_e('爱好列表', 'lared'); ?></label></th>
                <td>
                    <input id="lared_about_hobbies" name="lared_about_hobbies" type="text" class="large-text" value="<?php echo esc_attr($hobbies); ?>" placeholder="摄影, 编程, 阅读, 旅行" />
                    <p class="description"><?php esc_html_e('用英文逗号分隔，例如：摄影, 编程, 阅读', 'lared'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="lared_about_plans"><?php esc_html_e('计划列表', 'lared'); ?></label></th>
                <td>
                    <textarea id="lared_about_plans" name="lared_about_plans" rows="5" class="large-text" placeholder="学习 Rust&#10;完成一次马拉松&#10;独立开发一个 App"><?php echo esc_textarea($plans); ?></textarea>
                    <p class="description"><?php esc_html_e('每行一条计划。', 'lared'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="lared_about_tags"><?php esc_html_e('关键词 / 标签', 'lared'); ?></label></th>
                <td>
                    <input id="lared_about_tags" name="lared_about_tags" type="text" class="large-text" value="<?php echo esc_attr($tags); ?>" placeholder="WordPress, 极简主义, 开源, 独立博客" />
                    <p class="description"><?php esc_html_e('用英文逗号分隔，将在关于页面以标签云形式展示。', 'lared'); ?></p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
    <?php
}
