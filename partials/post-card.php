<article class="media post-card <?php post_class(); ?> <?php if ( ! has_post_thumbnail() ) : ?>no-image<?php endif; ?>">


    <figure class="media-left has-mr-xl">
        <a class="post-card-image-link" href="<?php the_permalink(); ?>">
            <?php if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail( 'large', array( 'class' => 'post-card-image' ) ); ?>
            <?php else : ?>
                <div class="post-card-image"></div>
            <?php endif; ?>
        </a>
    </figure>


    <div class="media-content">
        <div class="content">
            <section class="post-card-metadata">
                <time class="post-date" datetime="<?= get_the_date( 'Y-m-d' ); ?>">
                    <?= get_the_date( 'F j, Y' ); ?>
                </time>
            </section>
            <h2 class="post-card-title">
                <a class="post-card-content-link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h2>
            <section class="post-card-excerpt">
                <?php the_excerpt(); ?>
            </section>
            <section class="post-card-categories">
                <?php
                $categories = get_the_category();
                if ( $categories ) {
                    foreach ( $categories as $category ) {
                        echo '<a class="tag category category-' . $category->slug . '" href="' . get_category_link( $category->term_id ) . '">' . $category->name . '</a>';
                    }
                }
                ?>
            </section>
        </div>
    </div>

</article>
