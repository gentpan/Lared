<?php
if (!defined('ABSPATH')) {
    exit;
}

if (post_password_required()) {
    return;
}
?>
<section class="comments-shell" id="comments">
    <div class="comments-inner">
        <?php
        $approved_comments = get_comments([
            'post_id' => get_the_ID(),
            'status' => 'approve',
            'type' => 'comment',
        ]);

        $unique_commenters = [];
        foreach ($approved_comments as $single_comment) {
            $commenter_key = '';

            if ((int) $single_comment->user_id > 0) {
                $commenter_key = 'user:' . (string) $single_comment->user_id;
            } elseif ('' !== trim((string) $single_comment->comment_author_email)) {
                $commenter_key = 'email:' . strtolower(trim((string) $single_comment->comment_author_email));
            } else {
                $commenter_key = 'name:' . strtolower(trim((string) $single_comment->comment_author));
            }

            if ('' !== $commenter_key) {
                $unique_commenters[$commenter_key] = true;
            }
        }

        $commenter_count = count($unique_commenters);
        $comment_total = (int) get_comments_number();
        ?>

        <div class="comments-header" aria-label="Comments Header">
            <div class="comments-header__left">
                <span class="comments-title-icon" aria-hidden="true"><i class="fa-regular fa-comments"></i></span>
                <span class="comments-header__title"><?php echo esc_html('《' . get_the_title() . '》'); ?></span>
            </div>

            <div class="comments-header__stats" aria-label="Comment Stats">
                <span class="comments-header__num"><?php echo esc_html(number_format_i18n($commenter_count)); ?></span><span><?php esc_html_e('位吃瓜群众', 'lared'); ?></span>
                <span class="comments-header__sep" aria-hidden="true">·</span>
                <span class="comments-header__num"><?php echo esc_html(number_format_i18n($comment_total)); ?></span><span><?php esc_html_e('条评论', 'lared'); ?></span>
            </div>
        </div>

        <?php if (have_comments()) : ?>
            <ol class="comment-list">
                <?php
                wp_list_comments([
                    'style' => 'ol',
                    'short_ping' => true,
                    'avatar_size' => 44,
                    'callback' => 'lared_custom_comment_callback',
                ]);
                ?>
            </ol>

            <?php the_comments_navigation(); ?>
        <?php endif; ?>

        <?php
        $commenter = wp_get_current_commenter();
        $name_value = isset($commenter['comment_author']) ? (string) $commenter['comment_author'] : '';
        $email_value = isset($commenter['comment_author_email']) ? (string) $commenter['comment_author_email'] : '';
        $url_value = isset($commenter['comment_author_url']) ? (string) $commenter['comment_author_url'] : '';

        // 决定标题前的头像/图标
        $current_user = wp_get_current_user();
        $avatar_html = '';
        if ($current_user->ID > 0) {
            // 已登录：使用用户头像
            $avatar_html = get_avatar($current_user->ID, 24, '', '', ['class' => 'lared-title-avatar', 'extra_attr' => 'style="width:24px;height:24px;border-radius:2px;object-fit:cover;vertical-align:middle;"']);
        } elseif (!empty($email_value)) {
            // 有 cookie 记录：使用 Gravatar（包裹在 #lared-title-avatar-wrap 中，以便 JS 在邮箱修改时能动态切换）
            $avatar_html = '<span id="lared-title-avatar-wrap">' . get_avatar($email_value, 24, '', '', ['class' => 'lared-title-avatar', 'extra_attr' => 'style="width:24px;height:24px;border-radius:2px;object-fit:cover;vertical-align:middle;"']) . '</span>';
        } else {
            // 默认图标（可被 JS 动态替换为 Gravatar）
            $avatar_html = '<span id="lared-title-avatar-wrap"><i class="fa-regular fa-comment-dots" style="color:var(--color-accent,#f53004);font-size:16px;"></i></span>';
        }

        // 判断是否为回头访客（有 cookie 记录）
        $is_returning_guest = (!$current_user->ID && !empty($name_value));

        // 右侧信息
        $title_right = '';
        if ($current_user->ID > 0) {
            $title_right = '<span class="lared-title-meta lared-title-meta--logged-in">'
                . sprintf(
                    /* translators: %s: user display name */
                    __('以 %s 的身份登录。', 'lared'),
                    '<strong class="lared-meta-name">' . esc_html($current_user->display_name) . '</strong>'
                )
                . ' <a href="' . esc_url(wp_logout_url(get_permalink())) . '" class="lared-meta-logout">' . __('注销？', 'lared') . '</a>'
                . '</span>';
        } elseif ($is_returning_guest) {
            $title_right = '<span class="lared-title-meta lared-title-meta--returning">'
                . sprintf(
                    __('欢迎回来，%s', 'lared'),
                    '<strong>' . esc_html($name_value) . '</strong>'
                )
                . ' <a href="#" class="lared-edit-info-toggle" onclick="return false;"><i class="fa-regular fa-pen-to-square" style="font-size:11px"></i> ' . __('编辑信息', 'lared') . '</a>'
                . '</span>';
        } elseif (get_option('require_name_email')) {
            $title_right = '<span class="lared-title-meta">'
                . __('必填项已用 <span class="required">*</span> 标注', 'lared')
                . '</span>';
        }

        $title_reply_html = $avatar_html . ' ' . __('发表评论', 'lared');

        comment_form([
            'class_form' => 'comment-form' . ($is_returning_guest ? ' lared-returning-guest' : ''),
            'class_submit' => 'comment-submit',
            'title_reply' => $title_reply_html,
            'title_reply_before' => '<h3 class="comment-reply-title" id="reply-title">',
            'title_reply_after' => $title_right . '</h3>',
            'cancel_reply_before' => ' ',
            'cancel_reply_after' => '',
            'cancel_reply_link' => '<i class="fa-solid fa-xmark"></i>',
            'logged_in_as' => '',
            'comment_notes_before' => '',
            'comment_notes_after' => '',
            'label_submit' => __('提交评论', 'lared'),
            'fields' => $current_user->ID > 0 ? [] : array_filter([
                'author' => '<p class="comment-form-author lared-comment-field lared-comment-field--author"><label class="screen-reader-text" for="author">' . esc_html__('昵称', 'lared') . '</label><span class="lared-comment-field__icon" aria-hidden="true"><i class="fa-regular fa-user"></i></span><input id="author" name="author" type="text" value="' . esc_attr($name_value) . '" size="30" maxlength="245" autocomplete="name" placeholder="' . esc_attr__('昵称*', 'lared') . '" required /></p>',
                'email' => '<p class="comment-form-email lared-comment-field lared-comment-field--email"><label class="screen-reader-text" for="email">' . esc_html__('邮箱', 'lared') . '</label><span class="lared-comment-field__icon" aria-hidden="true"><i class="fa-regular fa-envelope"></i></span><input id="email" name="email" type="email" value="' . esc_attr($email_value) . '" size="30" maxlength="100" autocomplete="email" placeholder="' . esc_attr__('邮箱*', 'lared') . '" required /></p>',
                'url' => '<p class="comment-form-url lared-comment-field lared-comment-field--url"><label class="screen-reader-text" for="url">' . esc_html__('网站', 'lared') . '</label><span class="lared-comment-field__icon" aria-hidden="true"><i class="fa-regular fa-globe"></i></span><input id="url" name="url" type="url" value="' . esc_attr($url_value) . '" size="30" maxlength="200" autocomplete="url" placeholder="' . esc_attr__('网站', 'lared') . '" /></p>',
                'cookies' => get_option('show_comments_cookies_opt_in') ? '<p class="comment-form-cookies-consent"><span class="checkbox-wrapper-12"><span class="cbx"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes" /><label for="wp-comment-cookies-consent"></label><svg width="15" height="14" viewBox="0 0 15 14" fill="none"><path d="M2 8.36364L6.23077 12L13 2"></path></svg></span><svg xmlns="http://www.w3.org/2000/svg" version="1.1"><defs><filter id="goo-12"><feGaussianBlur in="SourceGraphic" stdDeviation="4" result="blur"></feGaussianBlur><feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 22 -7" result="goo-12"></feColorMatrix><feBlend in="SourceGraphic" in2="goo-12"></feBlend></filter></defs></svg></span><label class="checkbox-wrapper-12-text" for="wp-comment-cookies-consent">' . esc_html__('记住我的昵称、邮箱和网站', 'lared') . '</label></p>' : null,
            ]),
            'comment_field' => '<div class="comment-form-comment lared-comment-field lared-comment-field--comment"><label class="screen-reader-text" for="comment">' . esc_html__('评论', 'lared') . '</label><span class="lared-comment-field__icon" aria-hidden="true"><i class="fa-regular fa-pen-to-square"></i></span><textarea id="comment" name="comment" cols="45" rows="8" maxlength="65525" placeholder="' . esc_attr__('评论', 'lared') . '" required></textarea><div class="lared-emoji-bar"><button type="button" class="lared-emoji-toggle" title="表情"><i class="fa-regular fa-face-smile"></i></button><div class="lared-emoji-panel" style="display:none;"></div></div></div>',
        ]);
        ?>
    </div>
</section>
