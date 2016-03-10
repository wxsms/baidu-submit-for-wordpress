<?php

/*
Plugin Name: Baidu Submit for Wordpress
Plugin URI: http://anubarak.com/
Description: Baidu Submit for Wordpress
Version: 0.0.1
Author: wxsm
Author URI: http://anubarak.com/
License: MIT
*/

class BaiduSubmitForWordpressSettingsPage
{

    private $options;
    private $articles;
    private $categories;
    private $tags;
    private $posts;
    private $pages;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'baidu_submit_for_wordpress_menu'));
        add_action('admin_init', array($this, 'page_init'));
        add_action('admin_footer', array($this, 'page_footer'));
        add_action('wp_ajax_baidu_submit_for_wordpress_manual', array($this, 'baidu_submit_for_wordpress_manual_callback'));

        global $wpdb;
        $this->articles = $wpdb->get_results("SELECT ID,post_title,post_type FROM $wpdb->posts WHERE post_status = 'publish' AND (post_type='post' or post_type='page') AND post_password = '' order by post_date desc");
        $this->categories = get_categories();
        $this->tags = get_tags();
        $this->posts = array();
        $this->pages = array();
        foreach ($this->articles as $article) {
            $article->post_type == 'post' ? $this->posts[] = $article : $this->pages[] = $article;
        }
    }

    public function baidu_submit_for_wordpress_menu()
    {
        add_options_page(
            'Baidu Submit for Wordpress', //page title
            'Baidu Submit for Wordpress', //menu title
            'administrator', //capability
            'baidu_submit_for_wordpress', //menu slug
            array($this, 'create_admin_page') //function
        );
    }

    public function create_admin_page()
    {

        $this->options = get_option('baidu_submit_for_wordpress_options'); ?>

        <div class="wrap">
            <h2>Baidu Submit for Wordpress Settings</h2>

            <form method="post" action="options.php">

                <?php
                settings_fields('baidu_submit_for_wordpress_options_group');
                do_settings_sections('baidu_submit_for_wordpress');
                submit_button();
                ?>

            </form>
            <br/>

            <h1>Manually Submit Pages</h1>
            <hr/>
            <div id="baidu-submit-for-wordpress-url-table">
                <h3 class="page-header">Posts</h3>
                <table class="table">
                    <thead>
                    <tr>
                        <th><input type="checkbox" value="checkAll"></th>
                        <th>Title</th>
                        <th>URL</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($this->posts as $post) {
                        $permalink = get_permalink($post->ID);
                        echo "<tr><td><input type=\"checkbox\" value=\"{$permalink}\"></td><td>{$post->post_title}</td><td>{$permalink}</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
                <h2 class="page-header">Pages</h2>
                <table class="table">
                    <thead>
                    <tr>
                        <th><input type="checkbox" value="checkAll"></th>
                        <th>Title</th>
                        <th>URL</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($this->pages as $page) {
                        $permalink = get_permalink($page->ID);
                        echo "<tr><td><input type=\"checkbox\" value=\"{$permalink}\"></td><td>{$page->post_title}</td><td>{$permalink}</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
                <h2 class="page-header">Categories</h2>
                <table class="table">
                    <thead>
                    <tr>
                        <th><input type="checkbox" value="checkAll"></th>
                        <th>Title</th>
                        <th>URL</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($this->categories as $category) {
                        $permalink = get_category_link($category->term_id);
                        echo "<tr><td><input type=\"checkbox\" value=\"{$permalink}\"></td><td>{$category->name}</td><td>{$permalink}</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
                <h2 class="page-header">Tags</h2>
                <table class="table">
                    <thead>
                    <tr>
                        <th><input type="checkbox" value="checkAll"></th>
                        <th>Title</th>
                        <th>URL</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($this->tags as $tag) {
                        $permalink = get_tag_link($tag->term_id);
                        echo "<tr><td><input type=\"checkbox\" value=\"{$permalink}\"></td><td>{$tag->name}</td><td>{$permalink}</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <br/>

            <div>
                <button type="button" class="button-primary" id="baidu-submit-for-wordpress-submit-btn">Submit</button>
            </div>
            <p id="baidu-submit-for-wordpress-log"></p>
        </div>
    <?php }

    public function page_init()
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

    public function sanitize($input)
    {
        //TODO
        return $input;
    }

    public function print_section_info()
    {
        //TODO
        print '';
    }

    public function site_domain_callback()
    {
        $siteUrl = get_site_url();
        printf(
            '<input type="text" id="theme_id" name="baidu_submit_for_wordpress_options[site_domain]" value="%s"/>
       <p class="description">The exact site domain you register on Baidu, without <code>http</code> or <code>https</code> prefix.</p>
      ',
            isset($this->options['site_domain']) ? esc_attr($this->options['site_domain']) : ''
        );
    }

    public function site_token_callback()
    {
        printf(
            '<input type="text" id="theme_id" name="baidu_submit_for_wordpress_options[site_token]" value="%s" />
      <p class="description">The entry token given by Baidu after you register you site.</p>
      ',
            isset($this->options['site_token']) ? esc_attr($this->options['site_token']) : ''
        );
    }

    public function page_footer()
    {
        ?>
        <script>
            jQuery(document).ready(function ($) {
                var urlTable = $('#baidu-submit-for-wordpress-url-table'),
                    submitBtn = $('#baidu-submit-for-wordpress-submit-btn'),
                    log = $('#baidu-submit-for-wordpress-log'),
                    checkAll = $('input[value=checkAll]');

                checkAll.click(function () {
                    var $this = $(this);
                    $this.parents('table').find('tbody').find('input[type=checkbox]').prop('checked', $this.is(':checked'));
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
                            log.html(log.html() + '<br/>' + '[INFO]' + new Date().toLocaleString() + ': ' + data);
                            submitBtn.prop('disabled', false);
                        });
                    }
                })
            })
        </script>
        <?php
    }

    public function baidu_submit_for_wordpress_manual_callback()
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

if (is_admin()) {
    $baidu_submit_for_wordpress_settings_page = new BaiduSubmitForWordpressSettingsPage();
}


?>
