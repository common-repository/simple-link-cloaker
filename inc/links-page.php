<?php

if(!class_exists('WPSC_Table')){
    require_once( 'class-wpsc-table.php' );
}

if(!class_exists('WPSC_DB')){
    require_once( 'class-wpsc-db.php' );
}

function wpsc_add_menu_items(){
    global $wpsc_link_page_hook;
    $wpsc_link_page_hook = add_menu_page('Simple Link Cloaker', 'Simple Link Cloaker', 'activate_plugins', 'wpsc_links_page', 'wpsc_render_list_page');
}
add_action('admin_menu', 'wpsc_add_menu_items');

function wpsc_add_link_page_scripts($hook){
    global $wpsc_link_page_hook;
    if($wpsc_link_page_hook != $hook)
        return;
    wp_enqueue_script('wpsc-link-page-script', plugins_url('static/js/link-page.js', dirname(__FILE__)), array('jquery'), '1.0');
    wp_enqueue_style( 'wpsc-link-page-style', plugins_url('static/css/link-page.css', dirname(__FILE__)), null, '1.0', 'all' );
}
add_action('admin_enqueue_scripts', 'wpsc_add_link_page_scripts');

function wpsc_db_change_admin_notice() {
    if(!isset($_GET['link']) || !isset($_GET['action']))
        return;
    $message = '';
    if($_GET['action'] === 'delete')
        $message = count($_GET['link'])." record(s) deleted from database";

    if(!$message) return;
?>
    <div class="updated">
        <p><?=$message?></p>
    </div>
<?php
}
add_action( 'admin_notices', 'wpsc_db_change_admin_notice' );

function wpsc_render_list_page(){
    $db = new WPSC_DB();
?>
    <div class="wrap">
        <h2>Links</h2>
        <div id="icon-users" class="icon32"><br/></div>
        <?php if(isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['link'])): ?>
            <?php
                if(isset($_POST['update_link'])){
                    if(!empty($_POST['name']) && !empty($_POST['url'])){
                        $db->update($_GET['link'], $_POST['name'], $_POST['slug'], $_POST['url'], intval($_POST['status']));
                        $message = 'Item updated successfully!';
                    }
                    else{
                        $message = 'Item could not be updated. Please make sure name and URL fields are not empty.';
                        $class = 'error';
                    }
                }
                $link_item = $db->get($_GET['link']);
            ?>
            <?php if(isset($message)): ?>
                <div class="updated <?=isset($class)?$class:''?>">
                    <p><?=$message?></p>
                </div>
            <?php endif; ?>
            <form method="POST">
                <table class="form-table">
                    <tbody>
                        <tr class="form-field">
                            <th><label for="link-name">Name</label></th>
                            <td>
                                <input name="name" id="link-name" size="40" value="<?=$link_item['name']?>" type="text"/>
                                <p class="description">The name for this item. It won't appear anywhere on the website.</p>
                            </td>
                        </tr>
                        <tr class="form-field">
                            <th><label for="link-slug">Slug</label></th>
                            <td>
                                <input name="slug" id="link-slug" size="40" value="<?=$link_item['slug']?>" type="text"/>
                                <p class="description">The slug for this like. For instance if the slug is link-1 your URL will be http://yourdomain.com/visit/link-1/</p>
                            </td>
                        </tr>
                        <tr class="form-field">
                            <th><label for="link-url">URL</label></th>
                            <td>
                                <input name="url" id="link-url" size="40" value="<?=$link_item['url']?>" type="text"/>
                                <p class="description">Link of the page where you want the user to be redirected to.</p>
                            </td>
                        </tr>
                        <tr class="form-field">
                            <th><label for="link-status">Redirection Type</label></th>
                            <td>
                                <select name="status" id="link-status">
                                    <option value="301" <?php selected($link_item['status'], 301); ?>>301</option>
                                    <option value="302" <?php selected($link_item['status'], 302); ?>>302</option>
                                </select>
                                <p class="description">Redirection type for this link.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php submit_button('Update Link', 'primary', 'update_link'); ?>
            </form>
        <?php else: ?>
            <div id="add-link-form-container" class="form-wrap">
                <h3>Add a Link</h3>
                <form method="POST">
                    <div class="form-field form-required">
                        <label for="link-name">Name</label>
                        <input name="name" id="link-name" size="40" type="text"/>
                        <p>The name for this item. It won't appear anywhere on the website.</p>
                    </div>
                    <div class="form-field">
                        <label for="link-slug">Slug</label>
                        <input name="slug" id="link-slug" size="40" type="text"/>
                        <p>The slug for this like. For instance if the slug is link-1 your URL will be http://yourdomain.com/visit/link-1/</p>
                    </div>

                    <div class="form-field form-required">
                        <label for="link-url">URL</label>
                        <input name="url" id="link-url" size="40" type="text"/>
                        <p>Link of the page where you want the user to be redirected to.</p>
                    </div>

                    <div class="form-field">
                        <label for="link-status">Redirection Type</label>
                        <select name="status" id="link-status">
                            <option value="301">301</option>
                            <option value="302">302</option>
                        </select>
                        <p>Redirection type for this link.</p>
                    </div>
                    <?php submit_button('Add', 'primary', 'wpsc_add_link', false); ?><img class="loading-img" src="<?php echo plugins_url('static/img/ajax-loading.gif', dirname(__FILE__)); ?>"/>
                </form>
            </div>

            <div id="links-table-container">
                <form method="get">
                    <input type="hidden" name="page" id="wpsc_page" value="<?php echo $_REQUEST['page'] ?>" />
                    <div class="table-wrap">
                        <?php
                            $linksTable = new WPSC_Table();
                            $linksTable->prepare_items();
                            $linksTable->display();
                        ?>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <?php
}