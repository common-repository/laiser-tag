<script type="text/javascript">
    function ltoc_switch_tabs(tabname) {
        jQuery('.ltoc-tab').hide();
        jQuery('.nav-tab').removeClass('active');
        jQuery('#ltoc-tab-' + tabname).show();
        jQuery('#nav-tab-' + tabname).addClass('active');
    }

    var ltoc_batch_tagging_url = '<?php echo home_url('?action=ltoc-batch-tagging') ?>';
    var ltoc_untagged_posts_url = '<?php echo home_url('?action=ltoc-untagged-posts') ?>';
    var ltoc_log_output = '<?php echo home_url('?action=ltoc-log-output') ?>';

</script>
<?php
$ltoc_api_key = get_option('ltoc_api_key');
if ((isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'settings') || empty($ltoc_api_key)) : ?>
    <script>
        jQuery(document).ready(function () {
            ltoc_switch_tabs('settings');
        });
    </script>
<?php endif; ?>
<div class="laisertag-pg">

    <div id="header">
        <div class="logo-title">
            <img id="logo" src="<?php echo LTOC_ASSETS_URL . '/images/lt_logo.png'; ?>"/>
        </div>
        <div class="insights-desc">
            <p>Laiser Tag is an automated tagging plugin that uses the Open Calais API to automatically generate tags
                for created content within a WordPress Site. It was developed by Pacific Coast Information Systems Ltd.
                and continues to be maintained and improved.
            </p>
        </div>
        <div class="insights-extras">
            <div class="insights-update">
                <p><strong><a href="http://www.laisertag.com" target="_blank">Visit our website</a></strong> for current
                    tag trends, high performing tag data, and additional plugins from our Laiser Tag Suite.</p>
            </div>
        </div>
    </div>

    <div class="nav-tab-parent">
        <div class="nav-tab-wrapper">
            <a href="#" id="nav-tab-tracking" class="nav-tab active" onclick="ltoc_switch_tabs('tracking')">Tag
                Processing</a>
            <a href="#" id="nav-tab-settings" class="nav-tab" onclick="ltoc_switch_tabs('settings')">Settings</a>
        </div>

        <div class="postbox ltoc_postbox insights-blue-border ltoc-tab" id="ltoc-tab-settings" style="display:none;">
            <div class="laisertag-subtitles">
                <h1>Settings</h1>
            </div>
            <div class="flex-parent">
                <div class="insights-rightbox">
                    <p>
                        If you don't have an OpenCalais API key, you can register for one here. All you'll need is
                        an email address.
                        <a class="insights-button" href="https://developers.refinitiv.com/open-permid/intelligent-tagging-restful-api" target="_blank">
                            Register a key
                        </a>
                        <img class="calais-logo" src="<?php echo LTOC_ASSETS_URL . 'images/calais-logo.png'; ?>" alt="Open Calais Logo" />
                    </p>
                    <hr>
                    <p>Need help?</p>
                    <a class="insights-button" href="https://wordpress.org/plugins/laiser-tag/" target="_blank">Wordpress.org
                        site</a>
                    <a class="insights-button" href="https://wordpress.org/support/plugin/laiser-tag" target="_blank">Support
                        Site </a>
                    <a class="insights-button" href="https://wordpress.org/plugins/laiser-tag/#faq"
                       target="_blank">FAQs</a>
                    <hr>
                    <p>Need to contact support?</p>
                    <a class="insights-button" href="mailto:support@pcis.com">Reach out</a>
                    <hr>
                    <p>Don't forget to save your changes at the bottom of the page!</p>
                </div>

                <div class="insights-leftbox">
                    <?php if (empty($ltoc_api_key)) : ?>
                        <div class="lt-first-visit-box">
                            <p><strong>Thank you for installing Laiser Tag! Just a few steps to follow below, and your
                                    site will be all set up.</strong></p>
                        </div>
                    <?php else: ?>

                        <p><strong>Welcome back. When making changes to this page, remember to save your work at the
                                bottom of the page!</strong></p>
                    <?php endif; ?>

                    <form method="post"
                          action="<?php echo admin_url('options.php?page=' . LTOC_PLUGIN_NAME) ?>&tab=settings">

                        <h2 class="lt-section-title">Your Key</h2>
                        <div class="lt-form-chunk">
                            <label>Enter your OpenCalais API Key
                                <hr>
                                <em class="light-txt">Note: the batch tagging process will not run unless a valid Open
                                    Calais API key is added here.</em>
                            </label>
                            <input class="widefat" name="ltoc_api_key" type="text"
                                   value="<?php echo get_option('ltoc_api_key') ?>">
                        </div>

                        <h2 class="lt-section-title">Choose your categories</h2>
                        <div class="lt-form-chunk">
                            <label>Select which top level categories to include
                                <hr>
                                <em class="light-txt">(Hint: press command/control while clicking to select more than
                                    one). Leave all unselected to tag all posts</em>
                            </label>
                            <?php
                            $args = array(
                                'orderby' => 'name',
                                'hierarchical' => 1,
                                'style' => 'none',
                                'taxonomy' => 'category',
                                'hide_empty' => 0,
                                'depth' => 1,
                                'title_li' => '',
                                'parent' => 0
                            );

                            $categories = get_categories($args);
                            $included = get_option('ltoc_included_categories');
                            if (empty($included) || $included == "") {
                                $included = [];
                            }
                            if(is_string($included)) {
                                $included = [$included];
                            }
                            ?>
                            <select multiple class="widefat" name="ltoc_included_categories[]">
                                <?php foreach ($categories as $cat) : ?>
                                    <option value="<?php echo $cat->term_id; ?>"<?php if (in_array($cat->term_id, $included)) : ?> selected="selected"<?php endif; ?>>
                                        <?php echo $cat->name; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <h2 class="lt-section-title">Tag Setup</h2>
                        <div class="lt-form-chunk">
                            <label>Tag Relevance Percentage
                                <hr>
                                <em class="light-txt">
                                    Ignore tags below this relevance percentage
                                </em>
                            </label>
                            <input
                                    name="ltoc_tag_relevance"
                                    id="ltoc_tag_relevance"
                                    type="hidden"
                                    value="<?php $ltoc_tag_relevance = get_option('ltoc_tag_relevance');
                                    if (empty($ltoc_tag_relevance)) {
                                        echo '50';
                                    } else {
                                        echo $ltoc_tag_relevance;
                                    } ?>"
                            />
                            <div class="lt-form-subchunk">
                                <div id='ltoc_tag_relevance_slider'>
                                    <div id="custom-handle" class="ui-slider-handle"></div>
                                </div>
                            </div>
                        </div>
                        <hr class="section-spacing">
                        <div class="lt-form-chunk">
                            <label>Add Tags on Post Update
                                <hr>
                                <em class="light-txt">
                                    Every time you manually edit a post, tag that post
                                </em>
                            </label>
                            <div class="lt-form-subchunk">
                                <input
                                        id="ltoc_add_tag_on_save"
                                        name="ltoc_add_tag_on_save"
                                        type="checkbox"
                                        class="subchunk-checkbox"
                                    <?php checked(get_option('ltoc_add_tag_on_save'), 'on'); ?>
                                />
                            </div>
                        </div>

                        <hr class="section-spacing">
                        <div class="lt-form-chunk">
                            <label>Tag Blacklist
                                <hr>
                                <em class="light-txt">Add tags, one per line, which will be excluded by Laisertag when adding tags to posts.</em>
                            </label>
                            <div class="lt-form-subchunk">
                                <textarea class="widefat" name="ltoc_tag_blacklist"
                                          rows="4"><?php echo esc_html(get_option("ltoc_tag_blacklist")); ?></textarea>
                            </div>
                        </div>

                        <h2 class="lt-section-title">Batch Tagging</h2>
                        <div class="lt-form-chunk">

                            <label>Number of posts per batch tagging
                                <hr>
                                <em class="light-txt">The batch tagging process runs automatically every hour.</em>
                            </label>


                            <div class="lt-form-subchunk">
                                <input
                                        name="ltoc_batch_posts"
                                        id="ltoc_batch_posts"
                                        type="number"
                                        min="1"
                                        max="1000"
                                        size="5"
                                        value="<?php $ltoc_batch_posts = get_option('ltoc_batch_posts');
                                        if (empty($ltoc_batch_posts)) {
                                            echo '50';
                                        } else {
                                            echo $ltoc_batch_posts;
                                        } ?>"
                                />
                            </div>
                        </div>
                        <p>
                            The tagging process for one Post lasts approximately 1.5 seconds, with a 2 second delay to
                            ensure the
                            process doesn't exceed the OpenCalais API requests per second limit.
                        </p>
                        <hr class="section-spacing">
                        <div class="lt-form-chunk">
                            <label>Disable batch tagging
                                <hr>
                                <em class="light-txt">
                                    If you wish to disable the batch tagging process, please make sure that Add Tags on
                                    Post Update is <strong>enabled</strong>.
                                </em>
                            </label>
                            <div class="lt-form-subchunk">
                                <input
                                        id="ltoc_add_tag_on_save"
                                        name="ltoc_disable_batch"
                                        type="checkbox"
                                        class="subchunk-checkbox"
                                    <?php checked(get_option('ltoc_disable_batch'), 'on'); ?>
                                />
                            </div>
                        </div>

                        <h2 class="lt-section-title">Add Your Sitemap</h2>
                        <div class="lt-form-chunk">

                            <label>For optimal use of the Laiser Tag suite of plugins, please add the following tag
                                sitemap to Google Webmaster Tools.</label>
                            <div class="lt-form-subchunk">
                                <input type="text" value="<?php echo get_site_url(); ?>/laiser-tag-sitemap.xml"
                                       readonly="readonly"/>
                                <p><em>Hint: Select the link and copy/paste it instead of typing it in.</em></p>
                            </div>
                        </div>


                        <hr class="section-spacing">
                        <div class="lt-form-chunk">
                            <input type="submit" name="ltoc_submit" value="Save All Changes"/>
                        </div>
                    </form>
                </div><!-- insights-leftbox -->
            </div><!-- flex-parent -->
        </div><!-- postbox -->

        <div class="postbox ltoc_postbox insights-blue-border ltoc-tab" id="ltoc-tab-tracking">
            <div class="laisertag-subtitles">
                <h1>Tag Processing - Current Status</h1>
            </div>
            <div class="insights-inner-padding">
                <p>See below for the results of the most recent batch tagging process. Posts that have been successfully
                    tagged will not be shown individually.</p>

                <div class="ltoc_logoutput">
                    <?php
                    if (file_exists(LTOC_BATCH_LOG)) {
                        $logoutput = file_get_contents(LTOC_BATCH_LOG);
                    } else {
                        $logoutput = "";
                    }
                    ?>
                    <div class="lt-form-chunk">
                        <h4>Results</h4>
                        <p id="batch_process_untagged_posts">
                            <em>There are
                                <strong><?php \LTOC\Tagging::getInstance()->numberOfUntaggedPosts() ?></strong>
                                untagged posts left.</em>
                        </p>
                    </div>
                    <textarea name="logoutput" class="widefat" disabled rows="10"
                              id="ltoc-log-output"><?php echo $logoutput; ?></textarea>

                    <div class="lt-form-chunk">
                        <p id="batch_process">
                            <button id="run_batch_process" type="button" class="insights-button extra-padding">Run Batch
                                Process Manually
                            </button>
                        </p>
                    </div>
                </div><!-- ltoc-logoutput -->
            </div><!-- insights-inner-padding -->
        </div><!-- postbox -->
    </div><!-- nav-tab-parent -->
</div><!-- laisertag-pg -->
