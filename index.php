<?php
require_once '../../wp-blog-header.php';
global $wpdb;

if (isset($_POST['urls'])) {
    if (is_user_logged_in() && current_user_can('manage_options')) {
        $urls = $_POST['urls'];
        $api = 'http://data.zz.baidu.com/urls?site=anubarak.com&token=UAAupwyHsxulANWw';
        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $api,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => implode("\n", $urls),
            CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        echo $result;
    } else {
        echo 'Request denied. Please login as admin first.';
    }
} else { ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Baidu Submit Functions</title>
        <link href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
        <script src="//cdn.bootcss.com/jquery/1.11.0/jquery.min.js"></script>
        <script src="//cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
        <style>
            html, body {
                height: 100%;
            }

            body {
                padding-top: 70px;
                position: relative;
                overflow-y: scroll;;
            }
        </style>
    </head>
    <body data-spy="scroll" data-target="#main-nav">
    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-nav" aria-expanded="false">
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
                            <th style="width: 70px"><input type="checkbox" value="checkAll"></th>
                            <th>Title</th>
                            <th>URL</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $posts = $wpdb->get_results("SELECT ID,post_title FROM $wpdb->posts WHERE post_status = 'publish' AND post_type='post' AND post_password = '' order by post_date desc");
                        foreach ($posts as $post) {
                            $permalink = get_permalink($post->ID);
                            ?>
                            <tr>
                            <td style="width: 70px"><input type="checkbox" value="<?php echo $permalink; ?>"></td>
                            <td><?php echo $post->post_title; ?></td>
                            <td><?php echo $permalink; ?></td>
                            </tr><?php } ?>
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
                            <th style="width: 70px"><input type="checkbox" value="checkAll"></th>
                            <th>Title</th>
                            <th>URL</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $pages = $wpdb->get_results("SELECT ID,post_title FROM $wpdb->posts WHERE post_status = 'publish' AND post_type='page' AND post_password = '' order by post_date desc");
                        foreach ($pages as $page) {
                            $permalink = get_permalink($page->ID);
                            ?>
                            <tr>
                            <td style="width: 70px"><input type="checkbox" value="<?php echo $permalink; ?>"></td>
                            <td><?php echo $page->post_title; ?></td>
                            <td><?php echo $permalink; ?></td>
                            </tr><?php } ?>
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
                            <th style="width: 70px"><input type="checkbox" value="checkAll"></th>
                            <th>Title</th>
                            <th>URL</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $categories = get_categories();
                        foreach ($categories as $category) {
                            $permalink = get_category_link($category->term_id);
                            ?>
                            <tr>
                            <td style="width: 70px"><input type="checkbox" value="<?php echo $permalink; ?>"></td>
                            <td><?php echo $category->name; ?></td>
                            <td><?php echo $permalink; ?></td>
                            </tr><?php } ?>
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
                            <th style="width: 70px"><input type="checkbox" value="checkAll"></th>
                            <th>Title</th>
                            <th>URL</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $tags = get_tags();
                        foreach ($tags as $tag) {
                            $permalink = get_category_link($tag->term_id);
                            ?>
                            <tr>
                            <td style="width: 70px"><input type="checkbox" value="<?php echo $permalink; ?>"></td>
                            <td><?php echo $tag->name; ?></td>
                            <td><?php echo $permalink; ?></td>
                            </tr><?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(function () {
            var urlTable = $('#url-table'),
                submitBtn = $('#submit'),
                log = $('#log'),
                checkAll = $('input[value=checkAll]');

            checkAll.click(function () {
                var $this = $(this);
                console.log($this.is(':checked'));
                $this.parents('table').find('tbody').find('input[type=checkbox]').prop('checked', $this.is(':checked'));
            }).click();

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
        })
    </script>
    </body>
    </html>
<?php } ?>