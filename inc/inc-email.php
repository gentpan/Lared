<?php

if (!defined("ABSPATH")) {
    exit;
}


/**
 * Lared 主题 - 邮件发送模块
 *
 * 支持两种发送方式：
 * 1. SMTP（通过 PHPMailer / wp_mail()）
 * 2. Sendflare API（HTTP REST）
 *
 * @package Lared
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ================================================================
   1. 设置注册 — register_setting
   ================================================================ */

function lared_register_email_settings(): void
{
    $settings = [
        'lared_email_mode' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'smtp',
        ],
        // ── 发件人 ──
        'lared_email_from_address' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_email',
            'default'           => '',
        ],
        'lared_email_from_name' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ],
        // ── SMTP ──
        'lared_smtp_host' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ],
        'lared_smtp_port' => [
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 587,
        ],
        'lared_smtp_encryption' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'tls',
        ],
        'lared_smtp_username' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ],
        'lared_smtp_password' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ],
        // ── Sendflare ──
        'lared_sendflare_api_key' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ],
    ];

    foreach ($settings as $key => $args) {
        register_setting('lared_settings_email', $key, $args);
    }
}
add_action('admin_init', 'lared_register_email_settings');

/* ================================================================
   2. 邮件 HTML 模板系统（直角风格 · 主题配色）
   ================================================================ */

/**
 * 基础邮件外壳 — 直角风格，主题色 #f53004
 *
 * @param string $inner   邮件正文区域 HTML
 * @param array  $vars    ['site_name','site_url','year','admin_avatar']
 */
function lared_email_shell(string $inner, array $vars = []): string
{
    $site_name    = esc_html($vars['site_name'] ?? get_bloginfo('name'));
    $site_url     = esc_url($vars['site_url'] ?? home_url('/'));
    $year         = esc_html($vars['year'] ?? gmdate('Y'));
    $admin_avatar = $vars['admin_avatar'] ?? '';

    // 管理员头像：优先传入，否则自动获取
    if ('' === $admin_avatar) {
        $admin_email  = (string) get_option('admin_email', '');
        $admin_avatar = '' !== $admin_email
            ? esc_url(get_avatar_url($admin_email, ['size' => 64]))
            : '';
    }

    $avatar_block = '';
    if ('' !== $admin_avatar) {
        $avatar_block = '<img src="' . $admin_avatar . '" width="36" height="36" alt="avatar" style="display:block;width:36px;height:36px;object-fit:cover;" />';
    }

    return '<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
</head>
<body style="margin:0;padding:0;background:#f4f5f7;font-family:-apple-system,BlinkMacSystemFont,\'PingFang SC\',\'Microsoft YaHei\',\'Helvetica Neue\',\'Noto Sans SC\',system-ui,sans-serif;-webkit-font-smoothing:antialiased;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5f7;padding:40px 0;">
<tr><td align="center">

<!-- 外框 600px 直角 -->
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;overflow:hidden;border:1px solid #e5e5e5;">

  <!-- ▌Header — 深色 + 红色顶线 -->
  <tr><td style="height:3px;background:#f53004;font-size:0;line-height:0;">&nbsp;</td></tr>
  <tr>
    <td style="background:#21201c;padding:22px 32px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
        <td style="vertical-align:middle;">
          ' . $avatar_block . '
        </td>
        <td style="vertical-align:middle;padding-left:' . ('' !== $admin_avatar ? '12' : '0') . 'px;">
          <a href="' . $site_url . '" target="_blank" style="text-decoration:none;color:#ffffff;font-size:18px;font-weight:700;letter-spacing:.3px;">' . $site_name . '</a>
        </td>
      </tr></table>
    </td>
  </tr>

  <!-- ▌正文 -->
  ' . $inner . '

  <!-- ▌分隔线 -->
  <tr><td style="padding:0 32px;"><table role="presentation" width="100%"><tr><td style="border-top:1px solid #eee;"></td></tr></table></td></tr>

  <!-- ▌Footer -->
  <tr>
    <td style="padding:20px 32px 24px;text-align:center;">
      <p style="margin:0;font-size:12px;color:#999;line-height:1.6;">
        &copy; ' . $year . ' <a href="' . $site_url . '" style="color:#999;text-decoration:none;">' . $site_name . '</a> &middot; 此邮件由系统自动发送，请勿直接回复
      </p>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body>
</html>';
}

/**
 * 评论卡片 HTML 片段（嵌入模板正文）
 */
function lared_email_comment_card(array $c): string
{
    $avatar  = esc_url($c['avatar'] ?? '');
    $name    = esc_html($c['name'] ?? '匿名');
    $time    = esc_html($c['time'] ?? '');
    $content = wp_kses_post($c['content'] ?? '');

    $avatar_html = '';
    if ('' !== $avatar) {
        $avatar_html = '<td style="vertical-align:top;width:40px;padding-right:12px;">
            <img src="' . $avatar . '" width="40" height="40" alt="" style="display:block;width:40px;height:40px;object-fit:cover;" />
        </td>';
    }

    return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8f8f7;border-left:3px solid #f53004;margin:16px 0;">
    <tr><td style="padding:16px 20px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
            ' . $avatar_html . '
            <td style="vertical-align:top;">
                <div style="font-size:14px;font-weight:700;color:#21201d;line-height:1;">' . $name . '</div>
                <div style="font-size:12px;color:#999;margin-top:4px;">' . $time . '</div>
            </td>
        </tr></table>
        <div style="margin-top:12px;font-size:14px;line-height:1.7;color:#3c434a;word-break:break-word;">' . $content . '</div>
    </td></tr>
    </table>';
}

/**
 * 通用邮件模板（测试 / 简单通知）
 */
function lared_email_html_template(string $subject, string $body, array $vars = []): string
{
    $inner = '<tr><td style="padding:28px 32px 24px;">
        <h2 style="margin:0 0 16px;font-size:17px;font-weight:700;color:#21201d;">' . esc_html($subject) . '</h2>
        <div style="font-size:14px;line-height:1.8;color:#63635e;">' . $body . '</div>
    </td></tr>';

    // 可选按钮
    $btn_url  = $vars['btn_url'] ?? ($vars['site_url'] ?? home_url('/'));
    $btn_text = $vars['btn_text'] ?? '访问站点';
    $inner .= '<tr><td style="padding:0 32px 28px;">
        <a href="' . esc_url($btn_url) . '" target="_blank" style="display:inline-block;padding:10px 28px;background:#f53004;color:#ffffff;text-decoration:none;font-size:13px;font-weight:600;letter-spacing:.3px;">' . esc_html($btn_text) . '</a>
    </td></tr>';

    return lared_email_shell($inner, $vars);
}

/* ── 管理员通知模板（新评论 / 待审核） ── */

function lared_email_admin_notify(array $data): string
{
    $type        = $data['type'] ?? 'comment';        // comment | pending
    $post_title  = esc_html($data['post_title'] ?? '');
    $post_url    = esc_url($data['post_url'] ?? '#');
    $approve_url = esc_url($data['approve_url'] ?? '');
    $manage_url  = esc_url($data['manage_url'] ?? admin_url('edit-comments.php'));

    $is_pending  = ('pending' === $type);
    $heading     = $is_pending ? '有一条评论待审核' : '收到新评论';
    $badge       = $is_pending
        ? '<span style="display:inline-block;padding:2px 8px;background:#fef3cd;color:#856404;font-size:11px;font-weight:600;margin-left:8px;vertical-align:middle;">待审核</span>'
        : '<span style="display:inline-block;padding:2px 8px;background:#d4edda;color:#155724;font-size:11px;font-weight:600;margin-left:8px;vertical-align:middle;">已发布</span>';

    $comment_card = lared_email_comment_card($data);

    $actions = '<a href="' . $manage_url . '" target="_blank" style="display:inline-block;padding:10px 28px;background:#21201c;color:#ffffff;text-decoration:none;font-size:13px;font-weight:600;">管理评论</a>';
    if ($is_pending && '' !== $approve_url) {
        $actions = '<a href="' . $approve_url . '" target="_blank" style="display:inline-block;padding:10px 28px;background:#f53004;color:#ffffff;text-decoration:none;font-size:13px;font-weight:600;margin-right:8px;">批准评论</a>' . $actions;
    }

    $inner = '<tr><td style="padding:28px 32px 0;">
        <h2 style="margin:0 0 4px;font-size:17px;font-weight:700;color:#21201d;">' . $heading . $badge . '</h2>
        <p style="margin:6px 0 0;font-size:13px;color:#999;">文章：<a href="' . $post_url . '" style="color:#f53004;text-decoration:none;">' . $post_title . '</a></p>
    </td></tr>
    <tr><td style="padding:8px 32px 0;">' . $comment_card . '</td></tr>
    <tr><td style="padding:8px 32px 28px;">' . $actions . '</td></tr>';

    return lared_email_shell($inner);
}

/* ── 回复通知模板（管理员回复 / 访客间回复） ── */

function lared_email_reply_notify(array $data): string
{
    $recipient_name = esc_html($data['recipient_name'] ?? '');
    $post_title     = esc_html($data['post_title'] ?? '');
    $post_url       = esc_url($data['post_url'] ?? '#');
    $comment_url    = esc_url($data['comment_url'] ?? $post_url);

    // 原评论
    $original_card = '';
    if (!empty($data['original'])) {
        $original_card = '<tr><td style="padding:0 32px;">
            <p style="margin:0 0 4px;font-size:12px;color:#999;font-weight:600;">你的评论</p>'
            . lared_email_comment_card($data['original']) .
        '</td></tr>';
    }

    // 回复评论
    $reply_card = '<tr><td style="padding:0 32px;">
        <p style="margin:0 0 4px;font-size:12px;color:#999;font-weight:600;">回复内容</p>'
        . lared_email_comment_card($data['reply']) .
    '</td></tr>';

    $inner = '<tr><td style="padding:28px 32px 0;">
        <h2 style="margin:0 0 4px;font-size:17px;font-weight:700;color:#21201d;">你收到一条新回复</h2>
        <p style="margin:6px 0 16px;font-size:13px;color:#999;">' . ($recipient_name !== '' ? esc_html($recipient_name) . '，' : '') . '你在「<a href="' . $post_url . '" style="color:#f53004;text-decoration:none;">' . $post_title . '</a>」的评论有了新回复</p>
    </td></tr>'
    . $original_card
    . $reply_card .
    '<tr><td style="padding:8px 32px 28px;">
        <a href="' . $comment_url . '" target="_blank" style="display:inline-block;padding:10px 28px;background:#f53004;color:#ffffff;text-decoration:none;font-size:13px;font-weight:600;">查看回复</a>
    </td></tr>';

    return lared_email_shell($inner);
}

/* ================================================================
   3. 发送邮件（统一入口）
   ================================================================ */

/**
 * 统一邮件发送
 *
 * @param string       $to      收件人
 * @param string       $subject 主题
 * @param string       $body    正文 HTML 内容（会包裹在模板中）
 * @param bool         $wrap    是否用模板包裹 body，默认 true
 * @param array        $vars    模板变量
 * @return true|\WP_Error
 */
function lared_send_email(string $to, string $subject, string $body, bool $wrap = true, array $vars = [])
{
    $mode = (string) get_option('lared_email_mode', 'smtp');

    if ($wrap) {
        $body = lared_email_html_template($subject, $body, $vars);
    }

    if ('sendflare' === $mode) {
        return lared_send_via_sendflare($to, $subject, $body);
    }

    return lared_send_via_smtp($to, $subject, $body);
}

/* ──────────────────────────────────────────────
   3a. SMTP 发送（hook wp_mail 的 PHPMailer）
   ────────────────────────────────────────────── */

/**
 * 配置 PHPMailer — 强制 SMTP，禁用 PHP mail()
 */
function lared_configure_phpmailer(\PHPMailer\PHPMailer\PHPMailer $phpmailer): void
{
    $mode = (string) get_option('lared_email_mode', 'smtp');
    if ('smtp' !== $mode) {
        return;
    }

    // 始终强制 SMTP，绝不回退到 PHP mail()
    $phpmailer->isSMTP();

    $host = (string) get_option('lared_smtp_host', '');
    if ('' === $host) {
        return;
    }

    $phpmailer->Host       = $host;
    $phpmailer->Port       = (int) get_option('lared_smtp_port', 587);
    $phpmailer->SMTPSecure = (string) get_option('lared_smtp_encryption', 'tls');
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Username   = (string) get_option('lared_smtp_username', '');
    $phpmailer->Password   = (string) get_option('lared_smtp_password', '');

    $from_address = (string) get_option('lared_email_from_address', '');
    $from_name    = (string) get_option('lared_email_from_name', get_bloginfo('name'));
    if ('' !== $from_address) {
        $phpmailer->setFrom($from_address, $from_name);
    }
}
add_action('phpmailer_init', 'lared_configure_phpmailer', 10, 1);

/**
 * 记录所有 wp_mail 失败 — 方便排查 WordPress 核心邮件（如修改邮箱确认）
 */
add_action('wp_mail_failed', function ($wp_error) {
    if ($wp_error instanceof \WP_Error) {
        error_log('[Lared Mail Failed] ' . $wp_error->get_error_message());
        $data = $wp_error->get_error_data();
        if (!empty($data['to'])) {
            error_log('[Lared Mail Failed] To: ' . (is_array($data['to']) ? implode(', ', $data['to']) : $data['to']));
        }
    }
});

/* ──────────────────────────────────────────────
   3a-bis. Sendflare 模式下拦截 wp_mail() — 让 WordPress 系统邮件走 Sendflare API
   ────────────────────────────────────────────── */

/**
 * 当邮件模式为 sendflare 时，拦截 wp_mail() 调用并通过 Sendflare API 发送。
 * 这样 WordPress 核心邮件（如修改邮箱确认、密码重置等）也能正常送达。
 */
add_filter('pre_wp_mail', function ($null, $atts) {
    $mode = (string) get_option('lared_email_mode', 'smtp');
    if ('sendflare' !== $mode) {
        return null; // SMTP 模式走默认 PHPMailer
    }

    $to      = $atts['to'] ?? '';
    $subject = $atts['subject'] ?? '';
    $message = $atts['message'] ?? '';
    $headers = $atts['headers'] ?? '';

    if (is_array($to)) {
        $to = implode(', ', $to);
    }

    // 检测 Content-Type — WordPress 系统邮件通常是纯文本
    $is_html = false;
    $header_lines = is_array($headers) ? $headers : explode("\n", (string) $headers);
    foreach ($header_lines as $line) {
        if (str_contains(strtolower((string) $line), 'content-type') && str_contains(strtolower((string) $line), 'text/html')) {
            $is_html = true;
            break;
        }
    }

    // 纯文本转 HTML（保留换行）
    if (!$is_html) {
        $message = nl2br(esc_html($message));
    }

    $result = lared_send_via_sendflare($to, $subject, $message);

    if (is_wp_error($result)) {
        /** @var \WP_Error $result */
        do_action('wp_mail_failed', new \WP_Error('wp_mail_failed', $result->get_error_message(), $atts));
        return false;
    }

    return true;
}, 10, 2);

function lared_send_via_smtp(string $to, string $subject, string $html_body)
{
    // 预先检查 SMTP 必要配置，避免回退到不可用的 PHP mail()
    $host = (string) get_option('lared_smtp_host', '');
    if ('' === $host) {
        return new \WP_Error('smtp_no_host', 'SMTP 服务器未配置，请在邮件设置中填写并保存');
    }

    $username = (string) get_option('lared_smtp_username', '');
    $password = (string) get_option('lared_smtp_password', '');
    if ('' === $username || '' === $password) {
        return new \WP_Error('smtp_no_auth', 'SMTP 用户名或密码未配置');
    }

    // 设置 Content-Type
    add_filter('wp_mail_content_type', 'lared_set_html_content_type');

    $from_address = (string) get_option('lared_email_from_address', '');
    $from_name    = (string) get_option('lared_email_from_name', get_bloginfo('name'));
    $headers      = [];
    if ('' !== $from_address) {
        $headers[] = 'From: ' . $from_name . ' <' . $from_address . '>';
    }

    $result = wp_mail($to, $subject, $html_body, $headers);

    remove_filter('wp_mail_content_type', 'lared_set_html_content_type');

    if ($result) {
        return true;
    }

    global $phpmailer;
    $error_msg = '发送失败';
    if (isset($phpmailer) && $phpmailer instanceof \PHPMailer\PHPMailer\PHPMailer) {
        $error_msg = $phpmailer->ErrorInfo ?: $error_msg;
    }

    return new \WP_Error('smtp_error', $error_msg);
}

function lared_set_html_content_type(): string
{
    return 'text/html';
}

/* ──────────────────────────────────────────────
   3b. Sendflare API 发送
   ────────────────────────────────────────────── */

function lared_send_via_sendflare(string $to, string $subject, string $html_body)
{
    $api_key      = (string) get_option('lared_sendflare_api_key', '');
    $from_address = (string) get_option('lared_email_from_address', '');
    $from_name    = (string) get_option('lared_email_from_name', get_bloginfo('name'));

    if ('' === $api_key) {
        return new \WP_Error('sendflare_no_key', 'Sendflare API Key 未配置');
    }

    if ('' === $from_address) {
        return new \WP_Error('sendflare_no_from', '发件人地址未配置');
    }

    $response = wp_remote_post('https://api.sendflare.com/v1/send', [
        'timeout' => 30,
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ],
        'body' => wp_json_encode([
            'from'    => $from_name . ' <' . $from_address . '>',
            'to'      => $to,
            'subject' => $subject,
            'body'    => $html_body,
        ]),
    ]);

    if (is_wp_error($response)) {
        return new \WP_Error('sendflare_http_error', $response->get_error_message());
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    // Sendflare 返回 200 但 success=false 表示业务错误
    if (is_array($body) && isset($body['success']) && true === $body['success']) {
        return true;
    }

    $err_msg = $body['message'] ?? ('HTTP ' . $code);
    return new \WP_Error('sendflare_api_error', $err_msg);
}

/* ================================================================
   4. 测试发送 AJAX
   ================================================================ */

function lared_ajax_test_email(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => '无权限']);
        return;
    }

    check_ajax_referer('lared_email_nonce', 'nonce', false) || wp_send_json_error(['message' => '安全验证失败，请刷新页面']);

    $to = sanitize_email((string) ($_POST['to'] ?? ''));
    if ('' === $to) {
        wp_send_json_error(['message' => '请输入有效的收件人邮箱']);
        return;
    }

    $mode    = (string) get_option('lared_email_mode', 'smtp');
    $subject = '✉️ Lared 邮件测试';
    $body    = '<p>🎉 恭喜！你的邮件配置正常工作。</p>'
             . '<p>发送方式：<strong>' . esc_html(strtoupper($mode)) . '</strong></p>'
             . '<p>发送时间：' . esc_html(wp_date('Y-m-d H:i:s')) . '</p>';

    $result = lared_send_email($to, $subject, $body, true, [
        'btn_text' => '访问站点',
    ]);

    if (true === $result) {
        wp_send_json_success([
            'message' => '测试邮件已发送至 ' . $to,
            'mode'    => $mode,
        ]);
    } else {
        $err = is_wp_error($result) ? $result->get_error_message() : '未知错误';
        wp_send_json_error(['message' => '发送失败：' . $err, 'mode' => $mode]);
    }
}
add_action('wp_ajax_lared_test_email', 'lared_ajax_test_email');

/* ================================================================
   5. 模板预览 AJAX
   ================================================================ */

function lared_ajax_preview_email_template(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => '无权限']);
        return;
    }

    check_ajax_referer('lared_email_nonce', 'nonce', false) || wp_send_json_error(['message' => '安全验证失败']);

    $type = sanitize_text_field((string) ($_POST['template_type'] ?? 'test'));

    $admin_email  = (string) get_option('admin_email', '');
    $admin_avatar = '' !== $admin_email ? get_avatar_url($admin_email, ['size' => 40]) : '';
    $now          = wp_date('Y-m-d H:i');

    $html = match ($type) {
        'admin_comment' => lared_email_admin_notify([
            'type'       => 'comment',
            'post_title' => '如何搭建一个现代化的 WordPress 博客',
            'post_url'   => home_url('/sample-post/'),
            'manage_url' => admin_url('edit-comments.php'),
            'avatar'     => $admin_avatar,
            'name'       => '张三',
            'time'       => $now,
            'content'    => '写得很棒！这篇文章帮我解决了困扰很久的问题，感谢分享。请问有没有推荐的插件方案？',
        ]),
        'admin_pending' => lared_email_admin_notify([
            'type'        => 'pending',
            'post_title'  => '如何搭建一个现代化的 WordPress 博客',
            'post_url'    => home_url('/sample-post/'),
            'approve_url' => admin_url('comment.php?action=approve&c=1'),
            'manage_url'  => admin_url('edit-comments.php'),
            'avatar'      => $admin_avatar,
            'name'        => '匿名访客',
            'time'        => $now,
            'content'     => '你好，请问这个方案也适用于多站点网络吗？另外性能方面有什么需要注意的？',
        ]),
        'reply' => lared_email_reply_notify([
            'recipient_name' => '张三',
            'post_title'     => '如何搭建一个现代化的 WordPress 博客',
            'post_url'       => home_url('/sample-post/'),
            'comment_url'    => home_url('/sample-post/#comment-2'),
            'original' => [
                'avatar'  => $admin_avatar,
                'name'    => '张三',
                'time'    => wp_date('Y-m-d H:i', strtotime('-1 hour')),
                'content' => '写得很棒！这篇文章帮我解决了困扰很久的问题，感谢分享。',
            ],
            'reply' => [
                'avatar'  => $admin_avatar,
                'name'    => get_bloginfo('name') . ' (博主)',
                'time'    => $now,
                'content' => '谢谢支持！如果还有其他问题欢迎随时留言，我会尽快回复。',
            ],
        ]),
        default => lared_email_html_template('✉️ 邮件测试', '<p>🎉 恭喜！你的邮件配置正常工作。</p><p>发送方式：<strong>' . esc_html(strtoupper((string) get_option('lared_email_mode', 'smtp'))) . '</strong></p><p>发送时间：' . esc_html($now) . '</p>'),
    };

    wp_send_json_success(['html' => $html]);
}
add_action('wp_ajax_lared_preview_email_template', 'lared_ajax_preview_email_template');

/* ================================================================
   6. 后台 Tab 渲染
   ================================================================ */

function lared_render_tab_email(): void
{
    $mode           = (string) get_option('lared_email_mode', 'smtp');
    $from_address   = (string) get_option('lared_email_from_address', '');
    $from_name      = (string) get_option('lared_email_from_name', '');
    $smtp_host      = (string) get_option('lared_smtp_host', '');
    $smtp_port      = (int) get_option('lared_smtp_port', 587) ?: 587;
    $smtp_encryption = (string) get_option('lared_smtp_encryption', 'tls');
    $smtp_username  = (string) get_option('lared_smtp_username', '');
    $smtp_password  = (string) get_option('lared_smtp_password', '');
    $sendflare_api_key = (string) get_option('lared_sendflare_api_key', '');
    $email_nonce    = wp_create_nonce('lared_email_nonce');
    ?>

    <form method="post" action="options.php" id="lared-email-settings-form">
        <?php settings_fields('lared_settings_email'); ?>

        <table class="form-table" role="presentation">
            <!-- 发送模式 -->
            <tr>
                <th scope="row"><?php esc_html_e('发送模式', 'lared'); ?></th>
                <td>
                    <fieldset>
                        <label style="display:inline-flex;align-items:center;gap:6px;margin-right:28px;cursor:pointer;">
                            <input type="radio" name="lared_email_mode" value="smtp" <?php checked($mode, 'smtp'); ?> class="lared-email-mode-radio" />
                            <strong>SMTP</strong>
                            <span class="description">— 标准邮件协议</span>
                        </label>
                        <label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;">
                            <input type="radio" name="lared_email_mode" value="sendflare" <?php checked($mode, 'sendflare'); ?> class="lared-email-mode-radio" />
                            <strong>Sendflare API</strong>
                            <span class="description">— 现代邮件 API</span>
                        </label>
                    </fieldset>
                </td>
            </tr>
            <!-- 发件人信息（通用） -->
            <tr>
                <th scope="row"><label for="lared_email_from_name"><?php esc_html_e('发件人名称', 'lared'); ?></label></th>
                <td>
                    <input id="lared_email_from_name" name="lared_email_from_name" type="text" class="regular-text" value="<?php echo esc_attr($from_name); ?>" placeholder="<?php echo esc_attr(get_bloginfo('name')); ?>" />
                    <p class="description"><?php esc_html_e('留空则使用站点名称。', 'lared'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="lared_email_from_address"><?php esc_html_e('发件人邮箱', 'lared'); ?></label></th>
                <td>
                    <input id="lared_email_from_address" name="lared_email_from_address" type="email" class="regular-text" value="<?php echo esc_attr($from_address); ?>" placeholder="noreply@example.com" />
                    <p class="description"><?php esc_html_e('所有发出的邮件将使用此地址作为发件人。', 'lared'); ?></p>
                </td>
            </tr>
        </table>

        <!-- SMTP 配置区 -->
        <div id="lared-email-smtp-section" style="margin:16px 0;padding:16px 20px;background:#f9f9f9;border:1px solid #e0e0e0;">
            <h3 style="margin:0 0 8px;font-size:14px;color:#1d2327;">⚙ <?php esc_html_e('SMTP 配置', 'lared'); ?></h3>
            <table class="form-table" role="presentation" style="margin-top:0;">
                <tr>
                    <th scope="row"><label for="lared_smtp_host"><?php esc_html_e('SMTP 服务器', 'lared'); ?></label></th>
                    <td>
                        <input id="lared_smtp_host" name="lared_smtp_host" type="text" class="regular-text code" value="<?php echo esc_attr($smtp_host); ?>" placeholder="smtp.gmail.com" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="lared_smtp_port"><?php esc_html_e('端口', 'lared'); ?></label></th>
                    <td>
                        <input id="lared_smtp_port" name="lared_smtp_port" type="number" class="small-text" value="<?php echo esc_attr((string) $smtp_port); ?>" min="1" max="65535" />
                        <span class="description" style="margin-left:6px;">25 / 465（SSL）/ 587（TLS）</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="lared_smtp_encryption"><?php esc_html_e('加密方式', 'lared'); ?></label></th>
                    <td>
                        <select id="lared_smtp_encryption" name="lared_smtp_encryption">
                            <option value="" <?php selected($smtp_encryption, ''); ?>><?php esc_html_e('无', 'lared'); ?></option>
                            <option value="tls" <?php selected($smtp_encryption, 'tls'); ?>>TLS</option>
                            <option value="ssl" <?php selected($smtp_encryption, 'ssl'); ?>>SSL</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="lared_smtp_username"><?php esc_html_e('用户名', 'lared'); ?></label></th>
                    <td><input id="lared_smtp_username" name="lared_smtp_username" type="text" class="regular-text code" value="<?php echo esc_attr($smtp_username); ?>" autocomplete="off" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="lared_smtp_password"><?php esc_html_e('密码', 'lared'); ?></label></th>
                    <td>
                        <input id="lared_smtp_password" name="lared_smtp_password" type="password" class="regular-text code" value="<?php echo esc_attr($smtp_password); ?>" autocomplete="new-password" />
                        <p class="description"><?php esc_html_e('Gmail / Outlook 等建议使用应用专用密码。', 'lared'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Sendflare 配置区 -->
        <div id="lared-email-sendflare-section" style="margin:16px 0;padding:16px 20px;background:#f9f9f9;border:1px solid #e0e0e0;">
            <h3 style="margin:0 0 8px;font-size:14px;color:#1d2327;">⚡ <?php esc_html_e('Sendflare API 配置', 'lared'); ?></h3>
            <table class="form-table" role="presentation" style="margin-top:0;">
                <tr>
                    <th scope="row"><label for="lared_sendflare_api_key"><?php esc_html_e('API Key', 'lared'); ?></label></th>
                    <td>
                        <input id="lared_sendflare_api_key" name="lared_sendflare_api_key" type="password" class="large-text code" value="<?php echo esc_attr($sendflare_api_key); ?>" placeholder="live_xxxxxxxxx" autocomplete="new-password" />
                        <p class="description"><?php esc_html_e('在 Sendflare 控制台获取 API Key：', 'lared'); ?> <a href="https://sendflare.com" target="_blank" rel="noopener">sendflare.com</a></p>
                    </td>
                </tr>
            </table>
        </div>

        <?php submit_button(); ?>
    </form>

    <hr />

    <!-- 测试发送 -->
    <h2><?php esc_html_e('测试发送', 'lared'); ?></h2>
    <table class="form-table" role="presentation">
        <tr>
            <th scope="row"><label for="lared-email-test-to"><?php esc_html_e('收件人', 'lared'); ?></label></th>
            <td>
                <div style="display:flex;gap:8px;align-items:center;">
                    <input id="lared-email-test-to" type="email" class="regular-text" value="<?php echo esc_attr((string) wp_get_current_user()->user_email); ?>" placeholder="test@example.com" />
                    <button type="button" id="lared-email-test-btn" class="button button-primary"><?php esc_html_e('发送测试邮件', 'lared'); ?></button>
                </div>
                <div id="lared-email-test-result" style="margin-top:8px;"></div>
            </td>
        </tr>
    </table>

    <hr />

    <!-- 邮件模板预览 -->
    <h2><?php esc_html_e('邮件模板预览', 'lared'); ?></h2>
    <p class="description"><?php esc_html_e('选择模板类型预览不同场景的邮件样式。', 'lared'); ?></p>
    <div style="margin-top:12px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <select id="lared-email-preview-type" class="regular-text">
            <option value="test"><?php esc_html_e('测试邮件', 'lared'); ?></option>
            <option value="admin_comment"><?php esc_html_e('管理员通知 — 新评论', 'lared'); ?></option>
            <option value="admin_pending"><?php esc_html_e('管理员通知 — 待审核', 'lared'); ?></option>
            <option value="reply"><?php esc_html_e('回复通知 — 评论被回复', 'lared'); ?></option>
        </select>
        <button type="button" id="lared-email-preview-btn" class="button button-secondary"><?php esc_html_e('加载预览', 'lared'); ?></button>
        <button type="button" id="lared-email-preview-fullscreen-btn" class="button button-secondary" style="display:none;"><?php esc_html_e('全屏查看', 'lared'); ?></button>
    </div>
    <div id="lared-email-preview-wrap" style="display:none;margin-top:16px;max-width:720px;border:1px solid #ddd;overflow:hidden;background:#f4f5f7;">
        <iframe id="lared-email-preview-iframe" style="width:100%;height:600px;border:none;" sandbox="allow-same-origin allow-scripts"></iframe>
    </div>

    <!-- 全屏预览弹窗 -->
    <div id="lared-email-preview-overlay" style="display:none;position:fixed;inset:0;z-index:100000;background:rgba(0,0,0,.6);backdrop-filter:blur(3px);">
        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:90%;max-width:700px;max-height:90vh;background:#fff;overflow:hidden;box-shadow:0 12px 48px rgba(0,0,0,.2);">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 20px;background:#f0f0f1;border-bottom:1px solid #ddd;">
                <strong><?php esc_html_e('邮件模板预览', 'lared'); ?></strong>
                <button type="button" id="lared-email-preview-close" class="button button-secondary" style="min-height:28px;line-height:26px;padding:0 12px;">✕ <?php esc_html_e('关闭', 'lared'); ?></button>
            </div>
            <iframe id="lared-email-preview-iframe-full" style="width:100%;height:calc(90vh - 52px);border:none;" sandbox="allow-same-origin allow-scripts"></iframe>
        </div>
    </div>

    <script>
    (function() {
        var ajaxUrl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
        var nonce   = '<?php echo esc_attr($email_nonce); ?>';

        function post(action, extra) {
            var fd = new FormData();
            fd.append('action', action);
            fd.append('nonce', nonce);
            if (extra) { for (var k in extra) fd.append(k, extra[k]); }
            return fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function(r){ return r.json(); });
        }

        // ── 模式切换 ──
        var modeRadios  = document.querySelectorAll('.lared-email-mode-radio');
        var smtpSection = document.getElementById('lared-email-smtp-section');
        var sendflareSection = document.getElementById('lared-email-sendflare-section');

        function toggleSections() {
            var mode = document.querySelector('.lared-email-mode-radio:checked');
            var val  = mode ? mode.value : 'smtp';
            var isSMTP = val === 'smtp';
            smtpSection.style.display      = isSMTP ? 'block' : 'none';
            sendflareSection.style.display  = isSMTP ? 'none' : 'block';
            smtpSection.querySelectorAll('input,select').forEach(function(el){ el.disabled = !isSMTP; });
            sendflareSection.querySelectorAll('input,select').forEach(function(el){ el.disabled = isSMTP; });
        }
        modeRadios.forEach(function(r) { r.addEventListener('change', toggleSections); });
        toggleSections();

        // ── 测试发送 ──
        var testBtn    = document.getElementById('lared-email-test-btn');
        var testInput  = document.getElementById('lared-email-test-to');
        var testResult = document.getElementById('lared-email-test-result');

        testBtn.addEventListener('click', function() {
            var to = testInput.value.trim();
            if (!to) { testResult.innerHTML = '<span style="color:#d63638;">请输入收件人邮箱</span>'; return; }
            testBtn.disabled = true;
            testBtn.textContent = '<?php echo esc_js(__('发送中…', 'lared')); ?>';
            testResult.innerHTML = '';

            post('lared_test_email', { to: to })
            .then(function(d) {
                if (d.success) {
                    testResult.innerHTML = '<span style="color:#00a32a;">✓ ' + d.data.message + '（' + d.data.mode.toUpperCase() + '）</span>';
                } else {
                    testResult.innerHTML = '<span style="color:#d63638;">✗ ' + (d.data && d.data.message ? d.data.message : '未知错误') + '</span>';
                }
            })
            .catch(function(e) {
                testResult.innerHTML = '<span style="color:#d63638;">✗ 网络错误: ' + e.message + '</span>';
            })
            .finally(function() {
                testBtn.disabled = false;
                testBtn.textContent = '<?php echo esc_js(__('发送测试邮件', 'lared')); ?>';
            });
        });

        // ── 模板预览 ──
        var previewBtn       = document.getElementById('lared-email-preview-btn');
        var previewType      = document.getElementById('lared-email-preview-type');
        var previewWrap      = document.getElementById('lared-email-preview-wrap');
        var previewIframe    = document.getElementById('lared-email-preview-iframe');
        var fullscreenBtn    = document.getElementById('lared-email-preview-fullscreen-btn');
        var previewOverlay   = document.getElementById('lared-email-preview-overlay');
        var previewIframeFull= document.getElementById('lared-email-preview-iframe-full');
        var closeBtn         = document.getElementById('lared-email-preview-close');
        var cachedHtml       = '';

        function loadPreview() {
            previewBtn.disabled = true;
            previewBtn.textContent = '<?php echo esc_js(__('加载中…', 'lared')); ?>';

            post('lared_preview_email_template', { template_type: previewType.value })
            .then(function(d) {
                if (d.success && d.data.html) {
                    cachedHtml = d.data.html;
                    previewWrap.style.display = 'block';
                    fullscreenBtn.style.display = 'inline-block';
                    var doc = previewIframe.contentDocument || previewIframe.contentWindow.document;
                    doc.open(); doc.write(cachedHtml); doc.close();
                } else {
                    alert('加载失败');
                }
            })
            .catch(function(e) { alert('网络错误: ' + e.message); })
            .finally(function() {
                previewBtn.disabled = false;
                previewBtn.textContent = '<?php echo esc_js(__('加载预览', 'lared')); ?>';
            });
        }

        previewBtn.addEventListener('click', loadPreview);
        previewType.addEventListener('change', loadPreview);

        fullscreenBtn.addEventListener('click', function() {
            if (!cachedHtml) return;
            previewOverlay.style.display = 'block';
            var doc = previewIframeFull.contentDocument || previewIframeFull.contentWindow.document;
            doc.open(); doc.write(cachedHtml); doc.close();
        });

        closeBtn.addEventListener('click', function() { previewOverlay.style.display = 'none'; });
        previewOverlay.addEventListener('click', function(e) { if (e.target === previewOverlay) previewOverlay.style.display = 'none'; });
    })();
    </script>

    <?php
}

/* ================================================================
   7. 评论通知 — WordPress 钩子
   ================================================================ */

/**
 * 新评论通知管理员
 * hook: comment_post（评论插入后触发）
 *
 * - 垃圾 / 回收站评论：不发送任何邮件
 * - 待审核评论：不立即发送，累积到每日摘要邮件
 * - 已审核评论：立即发送通知
 */
function lared_notify_admin_new_comment(int $comment_id, $comment_approved): void
{
    // 垃圾评论 / 回收站 → 完全跳过
    if ('spam' === $comment_approved || 'trash' === $comment_approved) {
        return;
    }

    // 检查邮件模式是否配置
    $from = (string) get_option('lared_email_from_address', '');
    if ('' === $from) {
        return;
    }

    $comment = get_comment($comment_id);
    if (!$comment) {
        return;
    }

    // 管理员自己的评论不通知
    $admin_email = (string) get_option('admin_email', '');
    if (strtolower($comment->comment_author_email) === strtolower($admin_email)) {
        return;
    }

    $post = get_post((int) $comment->comment_post_ID);
    if (!$post) {
        return;
    }

    $is_pending = (1 !== (int) $comment_approved);

    // 待审核评论 → 累积，不立即发送
    if ($is_pending) {
        lared_queue_pending_comment($comment_id);
        return;
    }

    // 已审核评论 → 立即发送
    $post_url = get_permalink($post);

    $data = [
        'type'        => 'comment',
        'post_title'  => get_the_title($post),
        'post_url'    => $post_url,
        'manage_url'  => admin_url('edit-comments.php'),
        'avatar'      => get_avatar_url($comment->comment_author_email, ['size' => 40]),
        'name'        => $comment->comment_author,
        'time'        => wp_date('Y-m-d H:i', strtotime($comment->comment_date_gmt . ' UTC')),
        'content'     => wp_strip_all_tags($comment->comment_content),
    ];

    $subject = '💬 [' . get_bloginfo('name') . '] 收到新评论';
    $html    = lared_email_admin_notify($data);

    lared_send_email($admin_email, $subject, $html, false);
}
add_action('comment_post', 'lared_notify_admin_new_comment', 20, 2);

/* ================================================================
   7b. 待审核评论每日摘要
   ================================================================ */

/**
 * 将待审核评论 ID 加入队列（option 存储）
 */
function lared_queue_pending_comment(int $comment_id): void
{
    $queue = (array) get_option('lared_pending_comment_queue', []);
    $queue[] = $comment_id;
    update_option('lared_pending_comment_queue', array_unique($queue), false);
}

/**
 * 注册每日定时任务
 */
function lared_schedule_pending_comment_digest(): void
{
    if (wp_next_scheduled('lared_send_pending_comment_digest')) {
        return;
    }
    // 每天上午 9 点（站点时区）发送
    $timezone  = wp_timezone();
    $now       = new DateTimeImmutable('now', $timezone);
    $nine_am   = $now->setTime(9, 0, 0);
    if ($nine_am <= $now) {
        $nine_am = $nine_am->modify('+1 day');
    }
    wp_schedule_event($nine_am->getTimestamp(), 'daily', 'lared_send_pending_comment_digest');
}
add_action('wp', 'lared_schedule_pending_comment_digest');

/**
 * 主题切换时清除定时任务
 */
function lared_clear_pending_comment_digest_schedule(): void
{
    wp_clear_scheduled_hook('lared_send_pending_comment_digest');
}
add_action('switch_theme', 'lared_clear_pending_comment_digest_schedule');

/**
 * 执行每日摘要：查询数据库中实际存在的待审核评论，发送汇总邮件
 */
function lared_send_pending_comment_digest_email(): void
{
    global $wpdb;

    // 清空队列（无论是否发送）
    delete_option('lared_pending_comment_queue');

    // 检查邮件配置
    $from = (string) get_option('lared_email_from_address', '');
    if ('' === $from) {
        return;
    }

    // 直接查数据库：当前有多少条待审核评论
    $pending_count = (int) $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = '0'"
    );

    if ($pending_count < 1) {
        return; // 没有待审核评论，不发送
    }

    // 获取最近 5 条待审核评论用于预览
    $recent_pending = $wpdb->get_results(
        "SELECT comment_ID, comment_author, comment_author_email, comment_content, comment_date, comment_post_ID
         FROM {$wpdb->comments}
         WHERE comment_approved = '0'
         ORDER BY comment_date_gmt DESC
         LIMIT 5"
    );

    $admin_email = (string) get_option('admin_email', '');
    $manage_url  = admin_url('edit-comments.php?comment_status=moderated');
    $site_name   = get_bloginfo('name');

    // 构建评论预览卡片
    $cards_html = '';
    foreach ($recent_pending as $c) {
        $post = get_post((int) $c->comment_post_ID);
        $cards_html .= lared_email_comment_card([
            'avatar'  => get_avatar_url($c->comment_author_email, ['size' => 40]),
            'name'    => $c->comment_author,
            'time'    => wp_date('Y-m-d H:i', strtotime($c->comment_date_gmt . ' UTC')),
            'content' => wp_trim_words(wp_strip_all_tags($c->comment_content), 30, '…'),
        ]);
    }

    $more_hint = $pending_count > 5
        ? '<p style="margin:8px 0 0;font-size:12px;color:#999;">…还有 ' . ($pending_count - 5) . ' 条待审核评论</p>'
        : '';

    $inner = '<tr><td style="padding:28px 32px 0;">
        <h2 style="margin:0 0 4px;font-size:17px;font-weight:700;color:#21201d;">📋 评论审核日报</h2>
        <p style="margin:6px 0 0;font-size:13px;color:#999;">当前共有 <strong style="color:#f53004;">' . $pending_count . '</strong> 条评论等待审核</p>
    </td></tr>
    <tr><td style="padding:8px 32px 0;">' . $cards_html . $more_hint . '</td></tr>
    <tr><td style="padding:12px 32px 28px;">
        <a href="' . esc_url($manage_url) . '" target="_blank" style="display:inline-block;padding:10px 28px;background:#f53004;color:#ffffff;text-decoration:none;font-size:13px;font-weight:600;">前往审核</a>
    </td></tr>';

    $html    = lared_email_shell($inner);
    $subject = '📋 [' . $site_name . '] 有 ' . $pending_count . ' 条评论待审核';

    lared_send_email($admin_email, $subject, $html, false);
}
add_action('lared_send_pending_comment_digest', 'lared_send_pending_comment_digest_email');

/**
 * 评论被回复时通知原评论者
 * hook: comment_post（评论插入后触发，仅对已审核的回复生效）
 */
function lared_notify_reply(int $comment_id, $comment_approved): void
{
    // 仅对已审核的评论发送回复通知
    if (1 !== (int) $comment_approved) {
        return;
    }

    // 检查邮件是否配置
    $from = (string) get_option('lared_email_from_address', '');
    if ('' === $from) {
        return;
    }

    $reply = get_comment($comment_id);
    if (!$reply || 0 === (int) $reply->comment_parent) {
        return; // 不是回复，跳过
    }

    $parent = get_comment((int) $reply->comment_parent);
    if (!$parent || '' === trim((string) $parent->comment_author_email)) {
        return; // 父评论没有邮箱
    }

    // 不通知自己
    if (strtolower($reply->comment_author_email) === strtolower($parent->comment_author_email)) {
        return;
    }

    // 管理员回复不发送邮件通知（匹配站点管理员邮箱 或 登录的管理员用户）
    $admin_email = strtolower((string) get_option('admin_email', ''));
    if ('' !== $admin_email && strtolower($reply->comment_author_email) === $admin_email) {
        return;
    }
    $reply_user_id = (int) $reply->user_id;
    if ($reply_user_id > 0 && user_can($reply_user_id, 'manage_options')) {
        return;
    }

    $post = get_post((int) $reply->comment_post_ID);
    if (!$post) {
        return;
    }

    $post_url    = get_permalink($post);
    $comment_url = $post_url . '#comment-' . $comment_id;

    $data = [
        'recipient_name' => $parent->comment_author,
        'post_title'     => get_the_title($post),
        'post_url'       => $post_url,
        'comment_url'    => $comment_url,
        'original' => [
            'avatar'  => get_avatar_url($parent->comment_author_email, ['size' => 40]),
            'name'    => $parent->comment_author,
            'time'    => wp_date('Y-m-d H:i', strtotime($parent->comment_date_gmt . ' UTC')),
            'content' => wp_strip_all_tags($parent->comment_content),
        ],
        'reply' => [
            'avatar'  => get_avatar_url($reply->comment_author_email, ['size' => 40]),
            'name'    => $reply->comment_author,
            'time'    => wp_date('Y-m-d H:i', strtotime($reply->comment_date_gmt . ' UTC')),
            'content' => wp_strip_all_tags($reply->comment_content),
        ],
    ];

    $subject = '💬 [' . get_bloginfo('name') . '] 你的评论收到了新回复';
    $html    = lared_email_reply_notify($data);

    lared_send_email($parent->comment_author_email, $subject, $html, false);
}
add_action('comment_post', 'lared_notify_reply', 21, 2);

/**
 * 评论从待审核变为已批准时，通知原评论者的回复
 * hook: wp_set_comment_status
 */
function lared_notify_reply_on_approve(int $comment_id, string $new_status): void
{
    if ('approve' !== $new_status) {
        return;
    }

    $comment = get_comment($comment_id);
    if (!$comment || 0 === (int) $comment->comment_parent) {
        return;
    }

    // 借用回复通知逻辑
    lared_notify_reply($comment_id, 1);
}
add_action('wp_set_comment_status', 'lared_notify_reply_on_approve', 20, 2);

/**
 * 禁用 WordPress 默认评论通知邮件（避免重复）
 */
add_filter('notify_post_author', '__return_false');
add_filter('notify_moderator', '__return_false');
