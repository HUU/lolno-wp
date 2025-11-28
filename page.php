<?php get_header(); ?>

<div class="columns is-fullheight">
    <header class="column site-header-column">
        <div class="logo-block magical-rainbow">
            <div class="logo-column">
                <h1 class="site-title">
                    <a href="<?= get_bloginfo( 'url' ); ?>">
                    <?php
                    $custom_logo_id = get_theme_mod( 'custom_logo' );
                    $logo = wp_get_attachment_image_src( $custom_logo_id, 'full' )[0];
                    if ( has_custom_logo() ) : ?>
                        <img class="site-logo" src="<?= esc_url( $logo ); ?>" alt="<?= get_bloginfo( 'name' ); ?>" />
                    <?php else : ?>
                        <?= get_bloginfo( 'name' ); ?>
                    <?php endif; ?>
                    </a>
                </h1>
                <p class="site-description"><?= get_bloginfo( 'description' ); ?></p>
            </div>
        </div>

        <?php get_template_part( 'partials/site-nav' ); ?>

        <aside id="secondary" class="widget-area">
            <?php dynamic_sidebar( 'sidebar-1' ); ?>
        </aside>
    </header>
    <main class="column site-content-column">
        <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post(); ?>

                <div class="leave-page-link"><a href="javascript:history.back()"><div class="arrow-icon prev"><i></i></div> Go Back</a></div>
                <?php get_template_part( 'partials/post-content', null, array( 'hide_metadata' => true ) ); ?>

            <?php endwhile; ?>
        <?php endif; ?>
    </main>
</div>

<?php get_footer(); ?>
