<?php

/*
Plugin Name: Baidu Submit for Wordpress
Plugin URI: http://anubarak.com/
Description: Baidu Submit for Wordpress
Version: 0.1.0
Author: wxsm
Author URI: http://anubarak.com/
License: MIT
*/

/**
 * Class BaiduSubmitForWordpress
 * Main class of this plugin.
 */
class BaiduSubmitForWordpress
{

    private $options;
    private $links; //This is to store fetched links for manual submit

    public function __construct()
    {
        //Init admin menu
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_head', array($this, 'admin_head'));

        //Add JS to page footer
        add_action('admin_footer', array($this, 'admin_footer'));

        //Ajax function to handle manual submit
        add_action('wp_ajax_baidu_submit_for_wordpress_manual', array($this, 'manual_submit_callback'));

        $this->fetch_links();
    }

    /**
     * Fetch all links from database.
     * Including post/page(must be publish and public), categories and tags.
     *
     * For categories/tags, using default query functions of Wordpress.
     * For post/page, using custom query which select only 3 fields for the sake of not killing the server.
     */
    private function fetch_links()
    {
        global $wpdb;
        $sql = "SELECT ID,post_title,post_type FROM $wpdb->posts WHERE post_status = 'publish' AND (post_type='post' or post_type='page') AND post_password = '' order by post_date desc";

        $this->links = array();
        $this->links['categories'] = get_categories();
        $this->links['tags'] = get_tags();
        $this->links['posts'] = array();
        $this->links['pages'] = array();
        foreach ($wpdb->get_results($sql) as $article) {
            $article->post_type == 'post' ? $this->links['posts'][] = $article : $this->links['pages'][] = $article;
        }
    }

    /**
     * Add admin panel menus
     */
    public function admin_menu()
    {
        add_options_page(
            'Baidu Submit for Wordpress Settings', //page title
            'Baidu Submit', //menu title
            'administrator', //capability
            'baidu_submit_for_wordpress_settings', //menu slug
            array($this, 'create_admin_setting_page') //function
        );

        add_options_page(
            'Baidu Submit for Wordpress Manual Submit',
            'Baidu Submit for Wordpress Manual Submit',
            'administrator',
            'baidu_submit_for_wordpress_manual_submit',
            array($this, 'create_admin_manual_submit_page')
        );
    }

    /**
     * Remove unused option tabs. Keep the major one.
     */
    public function admin_head()
    {
        remove_submenu_page('options-general.php', 'baidu_submit_for_wordpress_manual_submit');
    }

    /**
     * Add admin panel setting page
     */
    public function create_admin_setting_page()
    {

        $this->options = get_option('baidu_submit_for_wordpress_options'); ?>

        <div class="wrap">
            <h2>Baidu Submit for Wordpress - Settings</h2>

            <h2 class="nav-tab-wrapper">
                <a class="nav-tab nav-tab-active" href="<?php echo admin_url() ?>/options-general.php?page=baidu_submit_for_wordpress_settings">Settings</a>
                <a class="nav-tab" href="<?php echo admin_url() ?>/options-general.php?page=baidu_submit_for_wordpress_manual_submit">Manual
                    Submit</a>
            </h2>

            <form method="post" action="options.php">

                <?php
                settings_fields('baidu_submit_for_wordpress_options_group');
                do_settings_sections('baidu_submit_for_wordpress');
                submit_button();
                ?>

            </form>
        </div>
    <?php }


    /**
     * Add admin panel manual submit page
     */
    public function create_admin_manual_submit_page()
    { ?>
        <div class="wrap">
            <h2>Baidu Submit for Wordpress - Manual Submit</h2>

            <h2 class="nav-tab-wrapper">
                <a class="nav-tab" href="<?php echo admin_url() ?>/options-general.php?page=baidu_submit_for_wordpress_settings">Settings</a>
                <a class="nav-tab nav-tab-active" href="<?php echo admin_url() ?>/options-general.php?page=baidu_submit_for_wordpress_manual_submit">Manual
                    Submit</a>
            </h2>

            <br/>

            <div>
                <button type="button" class="button-primary" data-role="baidu-submit-for-wordpress-submit-btn">Submit</button>
                <span data-role="baidu-submit-for-wordpress-log"></span>
            </div>
            <br/>
            <hr/>


            <div id="baidu-submit-for-wordpress-url-table">
                <h2>Posts</h2>
                <table data-role="url-table">
                    <thead>
                    <tr>
                        <th><input title="Check all" type="checkbox" value="checkAll"></th>
                        <th>Title</th>
                        <th>URL</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($this->links['posts'] as $post) {
                        $permalink = get_permalink($post->ID);
                        echo "<tr><td><input type=\"checkbox\" value=\"{$permalink}\"></td><td>{$post->post_title}</td><td>{$permalink}</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
                <h2>Pages</h2>
                <table data-role="url-table">
                    <thead>
                    <tr>
                        <th><input title="Check all" type="checkbox" value="checkAll"></th>
                        <th>Title</th>
                        <th>URL</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($this->links['pages'] as $page) {
                        $permalink = get_permalink($page->ID);
                        echo "<tr><td><input type=\"checkbox\" value=\"{$permalink}\"></td><td>{$page->post_title}</td><td>{$permalink}</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
                <h2>Categories</h2>
                <table data-role="url-table">
                    <thead>
                    <tr>
                        <th><input title="Check all" type="checkbox" value="checkAll"></th>
                        <th>Title</th>
                        <th>URL</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($this->links['categories'] as $category) {
                        $permalink = get_category_link($category->term_id);
                        echo "<tr><td><input type=\"checkbox\" value=\"{$permalink}\"></td><td>{$category->name}</td><td>{$permalink}</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
                <h2>Tags</h2>
                <table data-role="url-table">
                    <thead>
                    <tr>
                        <th><input title="Check all" type="checkbox" value="checkAll"></th>
                        <th>Title</th>
                        <th>URL</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($this->links['tags'] as $tag) {
                        $permalink = get_tag_link($tag->term_id);
                        echo "<tr><td><input type=\"checkbox\" value=\"{$permalink}\"></td><td>{$tag->name}</td><td>{$permalink}</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <hr/>
            <br/>

            <div>
                <button type="button" class="button-primary" data-role="baidu-submit-for-wordpress-submit-btn">Submit</button>
                <span data-role="baidu-submit-for-wordpress-log"></span>
            </div>
        </div>
        <?php
    }

    /**
     * Register setting sections and fields
     */
    public function admin_init()
    {
        register_setting(
            'baidu_submit_for_wordpress_options_group',
            'baidu_submit_for_wordpress_options',
            array($this, 'sanitize')
        );

        add_settings_section(
            'baidu_submit_for_wordpress_setting_section_id',
            '',
            array($this, 'print_section_info'),
            'baidu_submit_for_wordpress'
        );

        add_settings_field(
            'site_domain',
            'Site Domain',
            array($this, 'site_domain_callback'),
            'baidu_submit_for_wordpress',
            'baidu_submit_for_wordpress_setting_section_id'
        );

        add_settings_field(
            'site_token',
            'Site Token',
            array($this, 'site_token_callback'),
            'baidu_submit_for_wordpress',
            'baidu_submit_for_wordpress_setting_section_id'
        );
    }

    /**
     * Sanitize user input after submit
     *
     * @param $input
     * @return mixed
     */
    public function sanitize($input)
    {
        //TODO
        return $input;
    }

    /**
     * Junk function
     */
    public function print_section_info()
    {
        //TODO
        print '';
    }

    /**
     * Create input field for setting field 'site_domain'
     */
    public function site_domain_callback()
    {
        printf(
            '<input type="text" name="baidu_submit_for_wordpress_options[site_domain]" value="%s"/><p class="description">The exact site domain you register on Baidu, without <code>http</code> or <code>https</code> prefix.</p>',
            isset($this->options['site_domain']) ? esc_attr($this->options['site_domain']) : ''
        );
    }

    /**
     * Create input field for setting field 'site_token'
     */
    public function site_token_callback()
    {
        printf(
            '<input type="text" name="baidu_submit_for_wordpress_options[site_token]" value="%s" /><p class="description">The entry token given by Baidu after you register you site.</p>',
            isset($this->options['site_token']) ? esc_attr($this->options['site_token']) : ''
        );
    }

    /**
     * Insert JavaScript snippet to the footer of page
     */
    public function admin_footer()
    {
        ?>
        <script>
            jQuery(document).ready(function ($) {
                var urlTable = $('#baidu-submit-for-wordpress-url-table'),
                    submitBtn = $('button[data-role=baidu-submit-for-wordpress-submit-btn]'),
                    log = $('span[data-role=baidu-submit-for-wordpress-log]'),
                    checkAll = $('input[value=checkAll]');

                checkAll.click(function () {
                    var $this = $(this);
                    $this.parents('table[data-role=url-table]').find('tbody').find('input[type=checkbox]').prop('checked', $this.is(':checked'));
                });

                submitBtn.click(function () {
                    var checkboxes = urlTable.find('tbody input[type=checkbox]:checked');
                    var urls = [];
                    for (var i = 0; i < checkboxes.length; i++) {
                        urls.push(checkboxes[i].value);
                    }
                    if (urls.length > 0) {
                        submitBtn.prop('disabled', true);
                        $.post(ajaxurl, {
                            action: 'baidu_submit_for_wordpress_manual',
                            urls: urls
                        }, function (data) {
                            log.html('[INFO]' + new Date().toLocaleString() + ': ' + data);
                            submitBtn.prop('disabled', false);
                        });
                    }
                })
            })
        </script>
        <?php
    }

    /**
     * Handle manual submit Ajax request.
     * Collect the links user checked with and submit them to Baidu API
     */
    public function manual_submit_callback()
    {
        $options = get_option('baidu_submit_for_wordpress_options');
        if ($options && isset($_POST['urls'])) {
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => "http://data.zz.baidu.com/urls?site={$options['site_domain']}&token={$options['site_token']}",
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => implode("\n", $_POST['urls']),
                CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
            ));
            echo curl_exec($ch);
        }
        die(); // this is required to return a proper result
    }

}

//Create plugin instance in admin panel
if (is_admin()) {
    $baidu_submit_for_wordpress = new BaiduSubmitForWordpress();
}

?>
