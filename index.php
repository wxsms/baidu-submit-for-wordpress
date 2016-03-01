<?php
require_once 'config.php';
require_once $config->header_location;

if (isset($_POST['urls'])) {
    if (is_user_logged_in() && current_user_can('manage_options')) {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => "http://data.zz.baidu.com/urls?site={$config->site_domain}&token={$config->site_token}",
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => implode("\n", $_POST['urls']),
            CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
        ));
        echo curl_exec($ch);
    } else {
        echo 'Request denied. Please login as admin first.';
    }
    exit;
}

global $wpdb;

$articles = $wpdb->get_results("SELECT ID,post_title,post_type FROM $wpdb->posts WHERE post_status = 'publish' AND (post_type='post' or post_type='page') AND post_password = '' order by post_date desc");
$categories = get_categories();
$tags = get_tags();
$posts = $pages = array();
foreach ($articles as $article) {
    $article->post_type == 'post' ? $posts[] = $article : $pages[] = $article;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>
    <title>Baidu Submit Functions</title>
    <link href="//cdn.bootcss.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
        }

        body {
            padding-top: 70px;
            position: relative;
            overflow-y: scroll;;
        }

        table tr > th:first-child,
        table tr > td:first-child {
            width: 70px;
        }
    </style>
</head>
<body data-spy="scroll" data-target="#main-nav">
<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-nav"
                    aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">Baidu Submit</a>
        </div>
        <div class="collapse navbar-collapse" id="main-nav">
            <ul class="nav navbar-nav">
                <li><a href="#posts">Posts</a></li>
                <li><a href="#pages">Pages</a></li>
                <li><a href="#categories">Categories</a></li>
                <li><a href="#tags">Tags</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <div class="jumbotron">
                <h1>Baidu Submit for Wordpress</h1>

                <p>Select the links you want to submit to Baidu and do it.</p>

                <p id="log">Result log goes here.</p>
                <br/>
                <button type="button" class="btn btn-primary btn-lg" id="submit">Submit</button>

            </div>
        </div>
    </div>
    <div class="row" id="url-table">
        <div class="col-xs-12">
            <h2 class="page-header" id="posts">Posts</h2>

            <div class="table-responsive">
                <table class="table table-hover table-condensed">
                    <thead>
                    <tr>
                        <th><input type="checkbox" value="checkAll"></th>
                        <th>Title</th>
                        <th>URL</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($posts as $post) {
                        $permalink = get_permalink($post->ID);
                        echo "<tr><td><input type=\"checkbox\" value=\"{$permalink}\"></td><td>{$post->post_title}</td><td>{$permalink}</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-xs-12">
            <h2 class="page-header" id="pages">Pages</h2>

            <div class="table-responsive">
                <table class="table table-hover table-condensed">
                    <thead>
                    <tr>
                        <th><input type="checkbox" value="checkAll"></th>
                        <th>Title</th>
                        <th>URL</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($pages as $page) {
                        $permalink = get_permalink($page->ID);
                        echo "<tr><td><input type=\"checkbox\" value=\"{$permalink}\"></td><td>{$page->post_title}</td><td>{$permalink}</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-xs-12">
            <h2 class="page-header" id="categories">Categories</h2>

            <div class="table-responsive">
                <table class="table table-hover table-condensed">
                    <thead>
                    <tr>
                        <th><input type="checkbox" value="checkAll"></th>
                        <th>Title</th>
                        <th>URL</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($categories as $category) {
                        $permalink = get_category_link($category->term_id);
                        echo "<tr><td><input type=\"checkbox\" value=\"{$permalink}\"></td><td>{$category->name}</td><td>{$permalink}</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-xs-12">
            <h2 class="page-header" id="tags">Tags</h2>

            <div class="table-responsive">
                <table class="table table-hover table-condensed">
                    <thead>
                    <tr>
                        <th><input type="checkbox" value="checkAll"></th>
                        <th>Title</th>
                        <th>URL</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($tags as $tag) {
                        $permalink = get_tag_link($tag->term_id);
                        echo "<tr><td><input type=\"checkbox\" value=\"{$permalink}\"></td><td>{$tag->name}</td><td>{$permalink}</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="//cdn.bootcss.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<script>
    $(function () {
        var urlTable = $('#url-table'),
            submitBtn = $('#submit'),
            log = $('#log'),
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
                $.post('index.php', {
                    urls: urls
                }, function (data) {
                    log.html(log.html() + '<br/>' + '[INFO]' + new Date().toLocaleString() + ': ' + data);
                    submitBtn.prop('disabled', false);
                });
            }
        })
    });
</script>
</body>
</html>
