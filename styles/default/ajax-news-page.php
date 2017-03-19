<?php
$news = new TK_GNews($_POST['post_id']);
file_put_contents(__FILE__.".log", "1\r\n",FILE_APPEND);
if($news->isValid()) {
	$news->createPage();
	
	while($news->have_posts())
	{
		$news->the_post();
		$post = $news->post();
?>
	<div class="tk-news-unit">
		<div class="tk-news-title">
			<div><?php the_title(); ?></div>
			<div><?php echo $post->post_date; ?></div>
		</div>
		<div class="tk-news-content">
			<div>
				<?php the_content(); ?>
			</div>
		</div>
		<div class="tk-news-footer">
			<div>
				<?php the_author(); ?>
			</div>
		</div>
	</div>
<?php
	}
}
?>