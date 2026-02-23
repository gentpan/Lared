<?php
/**
 * 下载按钮短代码 [download_button]
 *
 * 参数:
 *   dl_url     - 下载链接（必填）
 *   dl_name    - 文件名称（默认"未知文件"）
 *   dl_text    - 按钮文字（默认"立即下载"）
 *   dl_size    - 文件大小
 *   dl_format  - 文件格式
 *   dl_version - 版本号
 *   dl_note    - 备注说明
 */

if (!defined('ABSPATH')) {
    exit;
}

function lared_download_button_shortcode(array $atts): string
{
    $atts = shortcode_atts([
        'dl_url'     => '',
        'dl_name'    => '未知文件',
        'dl_text'    => '立即下载',
        'dl_size'    => '',
        'dl_format'  => '',
        'dl_version' => '',
        'dl_note'    => '',
    ], $atts, 'download_button');

    $url = esc_url(trim($atts['dl_url']));
    if ('' === $url) {
        return '<div class="download-error">错误：请提供下载链接</div>';
    }

    $name    = esc_html(trim($atts['dl_name']));
    $text    = esc_html(trim($atts['dl_text']));
    $size    = esc_html(trim($atts['dl_size']));
    $format  = esc_html(trim($atts['dl_format']));
    $version = esc_html(trim($atts['dl_version']));
    $note    = trim($atts['dl_note']);

    ob_start();
    ?>
    <div class="lared-download-box">
        <div class="dl-header">
            <div class="dl-icon">
                <i class="fa-solid fa-box-archive"></i>
            </div>
            <div class="dl-info">
                <h4 class="dl-name"><?php echo $name; ?></h4>
                <?php if ('' !== $format || '' !== $version || '' !== $size) : ?>
                    <div class="dl-badges">
                        <?php if ('' !== $format) : ?>
                            <span class="dl-badge dl-format"><?php echo $format; ?></span>
                        <?php endif; ?>
                        <?php if ('' !== $version) : ?>
                            <span class="dl-badge dl-version"><?php echo $version; ?></span>
                        <?php endif; ?>
                        <?php if ('' !== $size) : ?>
                            <span class="dl-badge dl-size"><?php echo $size; ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php if ('' !== $note) : ?>
            <div class="dl-note">
                <i class="fa-solid fa-circle-info"></i>
                <span><?php echo nl2br(esc_html($note)); ?></span>
            </div>
        <?php endif; ?>
        <a class="dl-button no-arrow" href="<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
            <span class="dl-btn-icon"><i class="fa-solid fa-download"></i></span>
            <span class="dl-btn-text"><?php echo $text; ?></span>
        </a>
    </div>
    <?php
    return (string) ob_get_clean();
}
add_shortcode('download_button', 'lared_download_button_shortcode');
