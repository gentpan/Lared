<?php
/**
 * AI 摘要功能
 *
 * 支持模型：DeepSeek / 豆包 (Doubao) / 通义千问 (Qwen) / MiniMax / Kimi (Moonshot) / ChatGPT (OpenAI) / 自定义服务商
 * - 为文章自动生成 AI 摘要，存入 post_meta (lared_ai_summary)
 * - 后台可测试 API 连通性 & 试生成摘要
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ── 模型供应商配置 ── */
function lared_ai_providers(): array
{
    $providers = [
        'deepseek' => [
            'label'       => 'DeepSeek',
            'api_url'     => 'https://api.deepseek.com/chat/completions',
            'models'      => ['deepseek-chat', 'deepseek-reasoner'],
            'default'     => 'deepseek-chat',
        ],
        'doubao' => [
            'label'       => '豆包 (Doubao)',
            'api_url'     => 'https://ark.cn-beijing.volces.com/api/v3/chat/completions',
            'models'      => ['doubao-1-5-pro-32k', 'doubao-1-5-lite-32k', 'doubao-1-5-vision-pro-32k'],
            'default'     => 'doubao-1-5-pro-32k',
        ],
        'qwen' => [
            'label'       => '通义千问 (Qwen)',
            'api_url'     => 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions',
            'models'      => ['qwen-plus', 'qwen-turbo', 'qwen-max', 'qwen-long'],
            'default'     => 'qwen-plus',
        ],
        'minimax' => [
            'label'       => 'MiniMax',
            'api_url'     => 'https://api.minimax.chat/v1/text/chatcompletion_v2',
            'models'      => ['MiniMax-Text-01', 'abab6.5s-chat'],
            'default'     => 'MiniMax-Text-01',
        ],
        'kimi' => [
            'label'       => 'Kimi (Moonshot)',
            'api_url'     => 'https://api.moonshot.cn/v1/chat/completions',
            'models'      => ['moonshot-v1-8k', 'moonshot-v1-32k', 'moonshot-v1-128k'],
            'default'     => 'moonshot-v1-8k',
        ],
        'openai' => [
            'label'       => 'ChatGPT (OpenAI)',
            'api_url'     => 'https://api.openai.com/v1/chat/completions',
            'models'      => ['gpt-4o-mini', 'gpt-4o', 'gpt-4-turbo', 'gpt-3.5-turbo'],
            'default'     => 'gpt-4o-mini',
        ],
        'custom' => [
            'label'       => '自定义服务商',
            'api_url'     => '',
            'models'      => [],
            'default'     => '',
        ],
    ];

    // 自定义服务商：从数据库读取配置
    $custom_name   = (string) get_option('lared_ai_custom_name', '');
    $custom_url    = (string) get_option('lared_ai_custom_api_url', '');
    $custom_models = (string) get_option('lared_ai_custom_models', '');

    if ('' !== $custom_name) {
        $providers['custom']['label'] = $custom_name;
    }
    if ('' !== $custom_url) {
        $providers['custom']['api_url'] = $custom_url;
    }
    if ('' !== $custom_models) {
        $models_arr = array_filter(array_map('trim', explode(',', $custom_models)));
        if (!empty($models_arr)) {
            $providers['custom']['models']  = $models_arr;
            $providers['custom']['default'] = $models_arr[0];
        }
    }

    return $providers;
}

/* ── 获取当前启用的供应商 ── */
function lared_ai_get_provider(): string
{
    return (string) get_option('lared_ai_provider', 'deepseek');
}

function lared_ai_get_api_key(): string
{
    return (string) get_option('lared_ai_api_key', '');
}

function lared_ai_get_model(): string
{
    $provider  = lared_ai_get_provider();
    $providers = lared_ai_providers();
    $saved     = (string) get_option('lared_ai_model', '');
    if ('' !== $saved) {
        return $saved;
    }
    return $providers[$provider]['default'] ?? '';
}

function lared_ai_get_custom_endpoint(): string
{
    return (string) get_option('lared_ai_custom_endpoint', '');
}

function lared_ai_get_prompt(): string
{
    $default = '请为以下文章内容生成一段简洁的中文摘要，不超过 150 字，直接输出摘要内容，不要加任何前缀。';
    return (string) get_option('lared_ai_prompt', $default);
}

/* ── 调用 AI API ── */
function lared_ai_call(string $content, string $provider = '', string $model = '', string $api_key = ''): array
{
    $providers = lared_ai_providers();

    if ('' === $provider) {
        $provider = lared_ai_get_provider();
    }
    if (!isset($providers[$provider])) {
        return ['success' => false, 'message' => '不支持的 AI 供应商: ' . $provider];
    }

    // 自定义服务商必须填写 API 端点
    if ('custom' === $provider && '' === ($providers[$provider]['api_url'] ?? '')) {
        $custom_ep = lared_ai_get_custom_endpoint();
        if ('' === $custom_ep) {
            return ['success' => false, 'message' => '自定义服务商请填写 API 端点地址'];
        }
    }

    if ('' === $api_key) {
        $api_key = lared_ai_get_api_key();
    }
    if ('' === $api_key) {
        return ['success' => false, 'message' => '请先填写 API Key'];
    }

    if ('' === $model) {
        $model = lared_ai_get_model();
    }

    $endpoint = lared_ai_get_custom_endpoint();
    if ('' === $endpoint) {
        $endpoint = $providers[$provider]['api_url'];
    }

    $prompt = lared_ai_get_prompt();

    $body = [
        'model'    => $model,
        'messages' => [
            ['role' => 'system', 'content' => $prompt],
            ['role' => 'user',   'content' => mb_substr(wp_strip_all_tags($content), 0, 4000)],
        ],
        'max_tokens'  => 300,
        'temperature' => 0.5,
    ];

    $headers = [
        'Content-Type'  => 'application/json',
        'Authorization' => 'Bearer ' . $api_key,
    ];

    $response = wp_remote_post($endpoint, [
        'headers' => $headers,
        'body'    => wp_json_encode($body),
        'timeout' => 30,
    ]);

    if (is_wp_error($response)) {
        return ['success' => false, 'message' => $response->get_error_message()];
    }

    $code = wp_remote_retrieve_response_code($response);
    $data = json_decode(wp_remote_retrieve_body($response), true);

    if ($code < 200 || $code >= 300) {
        $err = $data['error']['message'] ?? ($data['base_resp']['status_msg'] ?? ('HTTP ' . $code));
        return ['success' => false, 'message' => $err];
    }

    $text = $data['choices'][0]['message']['content'] ?? '';
    if ('' === $text) {
        return ['success' => false, 'message' => 'API 返回空内容'];
    }

    return [
        'success' => true,
        'summary' => trim($text),
        'model'   => $model,
        'tokens'  => $data['usage']['total_tokens'] ?? 0,
    ];
}

/* ── AJAX: 测试 API 连通性 ── */
function lared_ajax_ai_test_connection(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => '无权限']);
        return;
    }

    check_ajax_referer('lared_ai_nonce', 'nonce');

    $result = lared_ai_call('这是一条测试消息，请回复"连接成功"。');

    if ($result['success']) {
        wp_send_json_success([
            'message' => 'API 连接成功',
            'reply'   => $result['summary'],
            'model'   => $result['model'] ?? '',
            'tokens'  => $result['tokens'] ?? 0,
        ]);
    } else {
        wp_send_json_error(['message' => $result['message']]);
    }
}
add_action('wp_ajax_lared_ai_test_connection', 'lared_ajax_ai_test_connection');

/* ── AJAX: 生成摘要测试 ── */
function lared_ajax_ai_generate_summary(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => '无权限']);
        return;
    }

    check_ajax_referer('lared_ai_nonce', 'nonce');

    $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
    if ($post_id < 1) {
        wp_send_json_error(['message' => '请提供文章 ID']);
        return;
    }

    $post = get_post($post_id);
    if (!$post || 'publish' !== $post->post_status) {
        wp_send_json_error(['message' => '文章不存在或未发布']);
        return;
    }

    $content = $post->post_content;
    if ('' === trim($content)) {
        wp_send_json_error(['message' => '文章内容为空']);
        return;
    }

    $result = lared_ai_call($content);

    if ($result['success']) {
        // 保存到 post meta + post_excerpt
        $save = isset($_POST['save']) && '1' === $_POST['save'];
        if ($save) {
            update_post_meta($post_id, 'lared_ai_summary', $result['summary']);
            wp_update_post([
                'ID'           => $post_id,
                'post_excerpt' => $result['summary'],
            ]);
        }

        wp_send_json_success([
            'summary' => $result['summary'],
            'model'   => $result['model'] ?? '',
            'tokens'  => $result['tokens'] ?? 0,
            'saved'   => $save,
            'title'   => $post->post_title,
        ]);
    } else {
        wp_send_json_error(['message' => $result['message']]);
    }
}
add_action('wp_ajax_lared_ai_generate_summary', 'lared_ajax_ai_generate_summary');

/* ── AJAX: 获取摘要统计 ── */
function lared_ajax_ai_get_stats(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => '无权限']);
        return;
    }

    check_ajax_referer('lared_ai_nonce', 'nonce');

    $all_posts = get_posts([
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ]);

    $total      = count($all_posts);
    $with_summary = 0;
    $without      = [];
    $with         = [];

    foreach ($all_posts as $pid) {
        $summary = (string) get_post_meta($pid, 'lared_ai_summary', true);
        if ('' !== $summary) {
            $with_summary++;
            $with[] = [
                'id'    => $pid,
                'title' => get_the_title($pid),
                'summary' => mb_substr($summary, 0, 60) . (mb_strlen($summary) > 60 ? '…' : ''),
            ];
        } else {
            $without[] = [
                'id'    => $pid,
                'title' => get_the_title($pid),
            ];
        }
    }

    wp_send_json_success([
        'total'        => $total,
        'with_summary' => $with_summary,
        'without_summary' => $total - $with_summary,
        'with'         => $with,
        'without'      => $without,
    ]);
}
add_action('wp_ajax_lared_ai_get_stats', 'lared_ajax_ai_get_stats');

/* ── AJAX: 获取未生成摘要的文章 ID 列表 ── */
function lared_ajax_ai_get_pending_ids(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => '无权限']);
        return;
    }

    check_ajax_referer('lared_ai_nonce', 'nonce');

    $all_posts = get_posts([
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ]);

    $pending = [];
    foreach ($all_posts as $pid) {
        if ('' === (string) get_post_meta($pid, 'lared_ai_summary', true)) {
            $pending[] = $pid;
        }
    }

    wp_send_json_success(['ids' => $pending]);
}
add_action('wp_ajax_lared_ai_get_pending_ids', 'lared_ajax_ai_get_pending_ids');

/* ── AJAX: 删除全部摘要 ── */
function lared_ajax_ai_delete_all(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => '无权限']);
        return;
    }

    check_ajax_referer('lared_ai_nonce', 'nonce');

    global $wpdb;
    $deleted = $wpdb->delete($wpdb->postmeta, ['meta_key' => 'lared_ai_summary']);
    // 兼容清理旧版 meta_key
    $deleted += (int) $wpdb->delete($wpdb->postmeta, ['meta_key' => '_ai_summary']);
    $wpdb->delete($wpdb->postmeta, ['meta_key' => '_ai_summary_active_provider']);

    // 同时清空所有已发布文章的 post_excerpt
    $wpdb->query(
        "UPDATE {$wpdb->posts} SET post_excerpt = '' WHERE post_type = 'post' AND post_status = 'publish' AND post_excerpt != ''"
    );

    wp_send_json_success(['deleted' => (int) $deleted]);
}
add_action('wp_ajax_lared_ai_delete_all', 'lared_ajax_ai_delete_all');

/* ── 获取文章 AI 摘要 ── */
function lared_get_ai_summary(int $post_id): string
{
    return (string) get_post_meta($post_id, 'lared_ai_summary', true);
}
