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

        comment_form([
            'class_form' => 'comment-form',
            'class_submit' => 'comment-submit',
            'title_reply' => __('Leave a comment', 'lared'),
            'title_reply_before' => '<h3 class="comment-reply-title" id="reply-title">',
            'title_reply_after' => '</h3>',
            'comment_notes_before' => '',
            'comment_notes_after' => '',
            'label_submit' => __('Post Comment', 'lared'),
            'fields' => [
                'author' => '<p class="comment-form-author lared-comment-field lared-comment-field--author"><label class="screen-reader-text" for="author">' . esc_html__('昵称', 'lared') . '</label><span class="lared-comment-field__icon" aria-hidden="true"><i class="fa-regular fa-user"></i></span><input id="author" name="author" type="text" value="' . esc_attr($name_value) . '" size="30" maxlength="245" autocomplete="name" placeholder="' . esc_attr__('昵称*', 'lared') . '" required /></p>',
                'email' => '<p class="comment-form-email lared-comment-field lared-comment-field--email"><label class="screen-reader-text" for="email">' . esc_html__('邮箱', 'lared') . '</label><span class="lared-comment-field__icon" aria-hidden="true"><i class="fa-regular fa-envelope"></i></span><input id="email" name="email" type="email" value="' . esc_attr($email_value) . '" size="30" maxlength="100" autocomplete="email" placeholder="' . esc_attr__('邮箱*', 'lared') . '" required /></p>',
                'url' => '<p class="comment-form-url lared-comment-field lared-comment-field--url"><label class="screen-reader-text" for="url">' . esc_html__('网站', 'lared') . '</label><span class="lared-comment-field__icon" aria-hidden="true"><i class="fa-regular fa-globe"></i></span><input id="url" name="url" type="url" value="' . esc_attr($url_value) . '" size="30" maxlength="200" autocomplete="url" placeholder="' . esc_attr__('网站', 'lared') . '" /></p>',
                'cookies' => '<p class="comment-form-cookies-consent"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes" /><label for="wp-comment-cookies-consent">' . esc_html__('记住我的昵称、邮箱和网站', 'lared') . '</label></p>',
            ],
            'comment_field' => '<p class="comment-form-comment lared-comment-field lared-comment-field--comment"><label class="screen-reader-text" for="comment">' . esc_html__('评论', 'lared') . '</label><span class="lared-comment-field__icon" aria-hidden="true"><i class="fa-regular fa-pen-to-square"></i></span><textarea id="comment" name="comment" cols="45" rows="8" maxlength="65525" placeholder="' . esc_attr__('评论', 'lared') . '" required></textarea></p>',
        ]);
        ?>
    </div>
</section>
