<article class="post-full <?php post_class(); ?> <?php if ( ! has_post_thumbnail() ) : ?>no-image<?php endif; ?>">

    <header class="post-full-header">
        <h1 class="post-full-title"><?php the_title(); ?></h1>
        <?php if ( ! ( isset( $args['hide_metadata'] ) && $args['hide_metadata'] ) ) : ?>
        <section class="post-full-meta">
            <span class="post-full-meta-author">
                <?= get_avatar( get_the_author_meta( 'ID' ), 24 ); ?>
                <?php the_author(); ?>,
            </span>
            <time class="post-full-meta-date" datetime="<?= get_the_date( 'Y-m-d' ); ?>">
                <?= get_the_date( 'F j, Y' ); ?>
            </time>
            <?php
                $categories = get_the_category();
                if ( $categories ) {
                    echo ', posted in ';
                    foreach ( $categories as $category ) {
                        echo '<a class="tag category category-' . $category->slug . '" href="' . get_category_link( $category->term_id ) . '">' . $category->name . '</a>';
                    }
                }
            ?>
        </section>
        <?php endif; ?>
    </header>

    <?php if ( has_post_thumbnail() ) : ?>
        <figure class="post-full-image">
            <?php the_post_thumbnail( 'full' ); ?>
        </figure>
    <?php endif; ?>

    <section class="post-full-content">
        <div class="content">
            <?php the_content(); ?>
        </div>
    </section>

</article>
