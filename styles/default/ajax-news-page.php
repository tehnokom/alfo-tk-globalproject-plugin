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
                <?php if (defined('BP_PLUGIN_DIR')) {
                    $author = new BP_Core_User($post->post_author);
                    ?>
                    <div class="tk-bp-xprofile">
                        <div class="tk-bp-avatar">
                            <a href="<?php echo $author->user_url; ?>">
                                <?php echo $author->avatar_thumb; ?>
                            </a>
                        </div>
                        <div>
                            <div class="tk-bp-profile">
                                <div>
                                    <a href="<?php echo $author->user_url; ?>"><?php echo $author->fullname; ?></a>
                                </div>
                            </div>
                            <div class="tk-bp-date tk-news-meta">
                                <div>
                                    <?php echo get_the_date('l, j F Y'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="tk-bp-title tk-news-title">
                            <div>
                                <h2><?php the_title(); ?></h2>
                            </div>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="tk-news-title">
                        <div><h2><?php the_title(); ?></h2></div>
                    </div>
                    <div class="tk-news-meta">
                        <div><?php echo get_the_date('l, j F Y'); ?></div>
                    </div>
                <?php } ?>
                <div class="tk-news-content">
                    <div>
                        <?php the_content(); ?>
                    </div>
                </div>
                <div class="tk-news-footer">
                    <div>
                        <?php if (!defined('BP_PLUGIN_DIR')) {
                            echo __('Author') . ': ';
                            the_author();
                        }
                        ?>
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