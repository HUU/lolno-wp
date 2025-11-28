<?php get_header(); ?>

<header class="site-header-row">
    <div class="logo-block">
        <div class="logo-column">
            <h1 class="site-title">
                <a href="<?= get_bloginfo( 'url' ); ?>">
                <?php
                $custom_logo_id = get_theme_mod( 'custom_logo' );
                $logo = wp_get_attachment_image_src( $custom_logo_id, 'full' )[0];
                if ( has_custom_logo() ) : ?>
                    <img class="site-logo" src="<?= esc_url( $logo ); ?>" alt="<?= get_bloginfo( 'name' ); ?>" />
                <?php else : ?>
                    <?php echo get_bloginfo( 'name' ); ?>
                <?php endif; ?>
                </a>
            </h1>
            <p class="site-description"><?= get_bloginfo( 'description' ); ?></p>
        </div>
    </div>

    <?php get_template_part( 'partials/site-nav' ); ?>
</header>
<main class="site-content-row">
    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : the_post(); ?>

            <?php get_template_part( 'partials/post-content' ); ?>

            <section class="sibling-posts">
                <div class="inner-wrap">
                    <?php
                    $prev_post = get_previous_post();
                    if ( ! empty( $prev_post ) ) :
                        $prev_post_image = get_the_post_thumbnail_url( $prev_post->ID, 'large' );
                        ?>
                        <a class="previous-post <?php if ( $prev_post_image ) : ?>has-image<?php endif; ?>" href="<?= get_permalink( $prev_post->ID ); ?>">
                            <?php if ( $prev_post_image ) : ?>
                                <div class="background-image">
                                    <div class="image blurred" style="background-image: url(<?= get_the_post_thumbnail_url( $prev_post->ID, 'thumbnail' ); ?>);"></div>
                                    <div class="image lazyload" data-src="<?= $prev_post_image; ?>"></div>
                                </div>
                            <?php endif; ?>
                            <h5><?php _e( 'Older Post', 'lolno' ); ?></h5>
                            <h3><?= get_the_title( $prev_post->ID ); ?></h3>
                            <div class="arrow-icon prev"><i></i></div>
                        </a>
                    <?php endif; ?>

                    <?php
                    $next_post = get_next_post();
                    if ( ! empty( $next_post ) ) :
                        $next_post_image = get_the_post_thumbnail_url( $next_post->ID, 'large' );
                        ?>
                        <a class="next-post <?php if ( $next_post_image ) : ?>has-image<?php endif; ?>" href="<?= get_permalink( $next_post->ID ); ?>">
                            <?php if ( $next_post_image ) : ?>
                                <div class="background-image">
                                    <div class="image blurred" style="background-image: url(<?= get_the_post_thumbnail_url( $next_post->ID, 'thumbnail' ); ?>);"></div>
                                    <div class="image lazyload" data-src="<?= $next_post_image; ?>"></div>
                                </div>
                            <?php endif; ?>
                            <h5><?php _e( 'Newer Post', 'lolno' ); ?></h5>
                            <h3><?= get_the_title( $next_post->ID ); ?></h3>
                            <div class="arrow-icon next"><i></i></div>
                        </a>
                    <?php endif; ?>
                </div>
            </section>

        <?php endwhile; ?>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
