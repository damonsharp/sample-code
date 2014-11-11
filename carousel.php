<?php 
	$work = new WP_Query(
		array(
			'post_type' => 'carousels',
			'orderby' => 'menu-order',
			'order' => 'asc',
			'posts_per_page' => 5,
			'no_found_rows' => true
		)
	);
?>
<div id="carousel-work" class="carousel slide"  data-ride="carousel">
	<?php if ( $work->have_posts() ) : $i = 0; ?>
		<ol class="carousel-indicators">
			<?php while ( $work->have_posts() ) : $work->the_post(); ?>
				<?php the_content(); ?>
				<li data-target="#carousel-work" data-slide-to="<?php echo $i; ?>" class="<?php echo ( 0 == $i ) ? 'active' : ''; ?>"></li>
			<?php $i++; endwhile; ?>
		</ol>
	<?php endif; ?>
	<div class="carousel-inner">
		<?php if ( $work->have_posts() ) : $i = 0; while ( $work->have_posts() ) : $work->the_post();
			if ( has_post_thumbnail() ) : ?>
				<div class="item<?php echo ( 0 == $i ) ? ' active' : '';  ?>">
					<?php if ( get_field( 'carousel_item_url' ) ) : ?>
						<a href="<?php the_field( 'carousel_item_url' ); ?>"><?php the_post_thumbnail(); ?></a>
					<?php else : ?>
						<?php the_post_thumbnail(); ?>
					<?php endif; ?>
				</div>
			<?php endif; $i++; ?>
		<?php endwhile; else: ?>
			<img src="http://placehold.it/1170X490" alt="Placeholder image">
		<?php endif; wp_reset_postdata(); ?>
	</div>
	<a class="left carousel-control" href="#carousel-work" role="button" data-slide="prev">
		<span class="glyphicon glyphicon-chevron-left"></span>
	</a>
	<a class="right carousel-control" href="#carousel-work" role="button" data-slide="next">
		<span class="glyphicon glyphicon-chevron-right"></span>
	</a>
</div>