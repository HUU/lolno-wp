<nav class="site-nav">
    <?php
    wp_nav_menu( array(
        'theme_location' => 'primary',
        'container'      => false,
        'menu_class'     => 'nav',
        'walker'         => new Lolno_Walker_Nav_Menu(),
    ) );
    ?>
</nav>
