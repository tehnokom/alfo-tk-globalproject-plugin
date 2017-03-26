<?php
$news = new TK_GNews($_POST['post_id']);

if ($news->isValid()) {
    $news->createPage();

    if ($news->have_posts()) {
        while ($news->have_posts()) {
            $news->the_post();
            $post = $news->post();
            ?>
            <div class="tk-news-unit">
                <div class="tk-news-title">
                    <div><h2><?php the_title(); ?></h2></div>
                </div>
                <div class="tk-news-meta">
                    <div><?php echo get_the_date('l, j F Y'); ?></div>
                </div>
                <div class="tk-news-content">
                    <div>
                        <?php the_content(); ?>
                    </div>
                </div>
                <div class="tk-news-footer">
                    <div>
                        <?php echo __('Author') . ': '; ?>
                        <?php if (!defined('BP_PLUGIN_DIR')) {
                            the_author();
                        } else {
                            echo bp_core_get_userlink(get_the_author_meta('ID'));
                        } ?>
                    </div>
                </div>
            </div>
            <?php
        }

        wp_reset_postdata();
    } else {
        echo TK_GProject::l10n('no_news');
    }
} else {
    echo TK_GProject::l10n('no_news');
}
?>