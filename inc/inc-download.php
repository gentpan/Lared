<?php
/**
 * 下载按钮短代码 [download_button] + 下载次数统计
 *
 * @package Lared
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

    $dl_key  = md5($url);
    $counts  = (array) get_option('lared_dl_counts', []);
    $count   = isset($counts[$dl_key]) ? (int) $counts[$dl_key] : 0;

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
            <div class="dl-count" title="<?php esc_attr_e('下载次数', 'lared'); ?>">
                <i class="fa-solid fa-download"></i>
                <span class="dl-count-number" data-dl-count="<?php echo esc_attr($dl_key); ?>"><?php echo esc_html(number_format($count)); ?></span>
            </div>
        </div>
        <?php if ('' !== $note) : ?>
            <div class="dl-note">
                <i class="fa-solid fa-circle-info"></i>
                <span><?php echo nl2br(esc_html($note)); ?></span>
            </div>
        <?php endif; ?>
        <a class="dl-button no-arrow" href="<?php echo $url; ?>" target="_blank" rel="noopener noreferrer"
           data-dl-track="<?php echo esc_attr($dl_key); ?>">
            <span class="dl-btn-icon"><i class="fa-solid fa-download"></i></span>
            <span class="dl-btn-text"><?php echo $text; ?></span>
        </a>
    </div>
    <?php
    return (string) ob_get_clean();
}
add_shortcode('download_button', 'lared_download_button_shortcode');

function lared_ajax_track_download(): void
{
    $dl_key = isset($_POST['dl_key']) ? sanitize_key((string) $_POST['dl_key']) : '';
    if ('' === $dl_key || !preg_match('/^[a-f0-9]{32}$/', $dl_key)) {
        wp_send_json_error(['message' => 'Invalid key']);
        return;
    }

    $counts = (array) get_option('lared_dl_counts', []);
    $counts[$dl_key] = isset($counts[$dl_key]) ? (int) $counts[$dl_key] + 1 : 1;
    update_option('lared_dl_counts', $counts, false);

    wp_send_json_success(['count' => $counts[$dl_key]]);
}
add_action('wp_ajax_lared_track_download', 'lared_ajax_track_download');
add_action('wp_ajax_nopriv_lared_track_download', 'lared_ajax_track_download');

function lared_download_tracking_script(): void
{
    if (is_admin()) {
        return;
    }
    ?>
    <script>
    (function(){
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('[data-dl-track]');
            if (!btn) return;
            var key = btn.getAttribute('data-dl-track');
            if (!key) return;
            var fd = new FormData();
            fd.append('action', 'lared_track_download');
            fd.append('dl_key', key);
            fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                method: 'POST', body: fd, credentials: 'same-origin'
            })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (d.success && d.data.count) {
                    var els = document.querySelectorAll('[data-dl-count="' + key + '"]');
                    els.forEach(function(el){ el.textContent = d.data.count.toLocaleString(); });
                }
            })
            .catch(function(){});
        });
    })();
    </script>
    <?php
}
add_action('wp_footer', 'lared_download_tracking_script', 99);
