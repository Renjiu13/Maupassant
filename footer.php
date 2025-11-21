</div>
    </div>
</div>
<footer id="footer" class="site-footer" role="contentinfo">
    <div class="container footer-container">
        <div class="footer-content">
            <div class="footer-copyright">
                &copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> 
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
                <?php
                // Display ICP number if available
                zh_cn_l10n_icp_num( ' | ', '' );
                ?>
            </div>
        </div>
    </div>
</footer>

<!-- 回到顶部按钮 -->
<button class="scroll-to-top" aria-label="<?php esc_attr_e( 'Back to top', 'maupassant' ); ?>">
    <span aria-hidden="true">↑</span>
    <span class="screen-reader-text"><?php esc_html_e( 'Back to top', 'maupassant' ); ?></span>
</button>

<?php wp_footer(); ?>
</body>
</html>
