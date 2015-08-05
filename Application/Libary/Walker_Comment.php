<?php
require_once LIB_PATH . 'Walker.php';

/**
 * HTML comment list class.
 *
 * @uses Walker
 * 
 */
class Walker_Comment extends Walker {

    /**
     * What the class handles.
     *
     * @see Walker::$tree_type
     *
     * 
     * @var string
     */
    var $tree_type = 'comment';

    /**
     * DB fields to use.
     *
     * @see Walker::$db_fields
     *
     * 
     * @var array
     */
    var $db_fields = array('parent' => 'comment_flow', 'id' => 'comment_id', 'post_id' => 'comment_post_id', 'avatar' => 'face');
    protected $author_id = 1;
    protected $is_user_logged_in = false;
    protected $lang = array(
        'awaiting_moderation' => 'Your comment is awaiting moderation.',
        'edit' => 'Edit',
        'login_text' => 'Signin',
        'reply_text' => 'Reply'
    );

    public function set_author_id($author_id) {
        $this->author_id = $author_id;
    }

    public function setLang($key, $val) {
        if (isset($this->lang[$key])) {
            $this->lang[$key] = $val;
        }
    }

    function start_lvl(&$output, $depth = 0, $args = array()) {
        $GLOBALS['comment_depth'] = $depth + 1;
        switch ($args['style']) {
            case 'div':
                break;
            case 'ol':
                $output .= '<ol class="children">' . "\n";
                break;
            default:
            case 'ul':
                $output .= '<ul class="children">' . "\n";
                break;
        }
    }

    function end_lvl(&$output, $depth = 0, $args = array()) {
        $GLOBALS['comment_depth'] = $depth + 1;

        switch ($args['style']) {
            case 'div':
                break;
            case 'ol':
                $output .= "</ol><!-- .children -->\n";
                break;
            default:
            case 'ul':
                $output .= "</ul><!-- .children -->\n";
                break;
        }
    }

    /**
     * Traverse elements to create list from elements.
     *
     * This function is designed to enhance Walker::display_element() to
     * display children of higher nesting levels than selected inline on
     * the highest depth level displayed. This prevents them being orphaned
     * at the end of the comment list.
     *
     * Example: max_depth = 2, with 5 levels of nested content.
     * 1
     *  1.1
     *    1.1.1
     *    1.1.1.1
     *    1.1.1.1.1
     *    1.1.2
     *    1.1.2.1
     * 2
     *  2.2
     *
     * @see Walker::display_element()
     * @see wp_list_comments()
     *
     * 
     *
     * @param object $element           Data object.
     * @param array  $children_elements List of elements to continue traversing.
     * @param int    $max_depth         Max depth to traverse.
     * @param int    $depth             Depth of current element.
     * @param array  $args              An array of arguments.
     * @param string $output            Passed by reference. Used to append additional content.
     * @return null Null on failure with no changes to parameters.
     */
    function display_element($element, &$children_elements, $max_depth, $depth, $args, &$output) {

        if (!$element)
            return;

        $id_field = $this->db_fields['id'];
        $id = $element[$id_field];

        parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);

        // If we're at the max depth, and the current element still has children, loop over those and display them at this level
        // This is to prevent them being orphaned to the end of the list.
        if ($max_depth <= $depth + 1 && isset($children_elements[$id])) {
            foreach ($children_elements[$id] as $child)
                $this->display_element($child, $children_elements, $max_depth, $depth, $args, $output);

            unset($children_elements[$id]);
        }
    }

    function start_el(&$output, $comment, $depth = 0, $args = array(), $id = 0) {
        $depth++;
        $GLOBALS['comment_depth'] = $depth;
        $GLOBALS['comment'] = $comment;

        if (!empty($args['callback'])) {
            ob_start();
            call_user_func($args['callback'], $comment, $args, $depth);
            $output .= ob_get_clean();
            return;
        }

        if (( 'pingback' == $comment['comment_type'] || 'trackback' == $comment['comment_type'] ) && $args['short_ping']) {
            ob_start();
            $this->ping($comment, $depth, $args);
            $output .= ob_get_clean();
        } elseif ('html5' === $args['format']) {
            ob_start();
            $this->html5_comment($comment, $depth, $args);
            $output .= ob_get_clean();
        } else {
            ob_start();
            $this->comment($comment, $depth, $args);
            $output .= ob_get_clean();
        }
    }

    function end_el(&$output, $comment, $depth = 0, $args = array()) {
        if (!empty($args['end-callback'])) {
            ob_start();
            call_user_func($args['end-callback'], $comment, $args, $depth);
            $output .= ob_get_clean();
            return;
        }
        if ('div' == $args['style'])
            $output .= "</div><!-- #comment-## -->\n";
        else
            $output .= "</li><!-- #comment-## -->\n";
    }

    protected function ping($comment, $depth, $args) {
        $tag = ( 'div' == $args['style'] ) ? 'div' : 'li';
        ?>
        <<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
        <div class="comment-body">
            <?php _e('Pingback:'); ?> <?php comment_author_link(); ?> <?php edit_comment_link(__('Edit'), '<span class="edit-link">', '</span>'); ?>
        </div>
        <?php
    }

    protected function comment($comment, $depth, $args) {
        if ('div' == $args['style']) {
            $tag = 'div';
            $add_below = 'comment';
        } else {
            $tag = 'li';
            $add_below = 'div-comment';
        }
        $id_field = $this->db_fields['id'];
        $comment_id = $comment[$id_field];
        ?>
        <<?php echo $tag; ?> <?php echo $this->comment_class(empty($args['has_children']) ? '' : 'parent', $comment_id); ?> id="comment-<?php echo $comment_id; ?>">
        <?php if ('div' != $args['style']) : ?>
            <div id="div-comment-<?php echo $comment_id; ?>" class="comment-body">
            <?php endif; ?>
            <div class="comment-author vcard">
                <?php if (0 != $args['avatar_size']) echo $this->get_avatar($comment['comment_author_email'], $args['avatar_size']); ?>
                <?php printf('<cite class="fn">%s</cite> <span class="says">:</span>', $this->get_comment_author_link($comment['comment_author'], $comment['comment_author_url'])); ?>
            </div>
            <?php if ('0' == $comment['comment_approved']) : ?>
                <em class="comment-awaiting-moderation"><?php echo $this->lang['awaiting_moderation']; ?></em>
                <br />
            <?php endif; ?>
            <div class="comment-meta commentmetadata"><a href="<?php echo $this->get_comment_link($comment, $args); ?>"><?php echo $this->get_comment_time($comment); ?></a><?php echo $this->edit_comment_link('(Edit)', '&nbsp;&nbsp;', ''); ?>
            </div>
            <div class="comment-content">
                <?php echo $this->get_comment_text($comment); ?>
            </div><!-- .comment-content -->
            <div class="reply">
                <?php echo $this->comment_reply_link($comment, $add_below); ?>
            </div>
            <?php if ('div' != $args['style']) : ?>
            </div>
        <?php endif; ?>
        <?php
    }

    protected function html5_comment($comment, $depth, $args) {
        $tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
        $id_field = $this->db_fields['id'];
        $comment_id = $comment[$id_field];
        ?>
        <article id="div-comment-<?php echo $comment_id; ?>" class="comment-body">
            <footer class="comment-meta">
                <div class="comment-author vcard"><?php echo $comment['comment_author']; ?></div><!-- .comment-author -->
                <div class="comment-metadata">
                    <a href="<?php echo $comment[$id_field]; ?>">
                        <time datetime=""><?php echo $this->get_comment_time($comment); ?></time>
                    </a>
                </div><!-- .comment-metadata -->
                <p class="comment-awaiting-moderation"></p>
            </footer><!-- .comment-meta -->

            <div class="comment-content">
                <?php echo $this->get_comment_text($comment); ?>
            </div><!-- .comment-content -->

            <div class="reply">
                <?php echo $this->comment_reply_link($comment, $add_below); ?>
            </div><!-- .reply -->
        </article><!-- .comment-body -->
        <?php
    }

    function comment_class($class = '', $comment_id = null, $post_id = null, $echo = true) {
        // Separates classes with a single space, collates classes for comment DIV
        $class = 'class="' . join(' ', $this->get_comment_class($class, $comment_id, $post_id)) . '"';
        if ($echo)
            echo $class;
        else
            return $class;
    }

    function get_comment_class($class = '', $comment_id = null, $post_id = null) {
        $comment = $this->get_comment($comment_id);
        $classes = array();

        // Get the comment type (comment, trackback),
        $classes[] = ( empty($comment['comment_type']) ) ? 'comment' : $comment['comment_type'];

        // If the comment author has an id (registered), then print the log in name
        // For all registered users, 'byuser'
        $classes[] = 'byuser';
        $classes[] = 'comment-author-' . $this->sanitize_html_class($comment['comment_author'], $comment['user_id']);
        // For comment authors who are the author of the post
        if ($comment['user_id'] === $this->author_id) {
            $classes[] = 'bypostauthor';
        }

        if (empty($comment_alt))
            $comment_alt = 0;
        if (empty($comment_depth))
            $comment_depth = 1;
        if (empty($comment_thread_alt))
            $comment_thread_alt = 0;

        if ($comment_alt % 2) {
            $classes[] = 'odd';
            $classes[] = 'alt';
        } else {
            $classes[] = 'even';
        }

        $comment_alt++;

        // Alt for top-level comments
        if (1 == $comment_depth) {
            if ($comment_thread_alt % 2) {
                $classes[] = 'thread-odd';
                $classes[] = 'thread-alt';
            } else {
                $classes[] = 'thread-even';
            }
            $comment_thread_alt++;
        }

        $classes[] = "depth-$comment_depth";

        if (!empty($class)) {
            if (!is_array($class)) {
                $class = preg_split('#\s+#', $class);
            }
            $classes = array_merge($classes, $class);
        }
        return $classes;
    }

    public function get_comment($comment_id) {
        foreach ($this->elements as $key => $val) {
            if ($val[$this->db_fields['id']] == $comment_id) {
                return $val;
            }
        }
    }

    public function sanitize_html_class($class, $fallback = '') {
        //Strip out any % encoded octets
        $sanitized = preg_replace('|%[a-fA-F0-9][a-fA-F0-9]|', '', $class);

        //Limit to A-Z,a-z,0-9,_,-
        $sanitized = preg_replace('/[^A-Za-z0-9_-]/', '', $sanitized);

        if ('' == $sanitized) {
            $sanitized = $fallback;
        }
        return $sanitized;
    }

    public function get_avatar($email, $size = '96', $default = 'monsterid', $alt = false) {
        $avatar_defaults = array(
            'mystery',
            'blank',
            'gravatar_default',
            'identicon',
            'wavatar',
            'monsterid',
            'retro'
        );
        $email_hash = md5(strtolower(trim($email)));
        if ($this->is_ssl()) {
            $host = 'https://secure.gravatar.com';
        } else {
            $host = sprintf("http://%d.gravatar.com", ( hexdec($email_hash[0]) % 2));
        }

        if ('mystery' == $default) {
            $default = "$host/avatar/ad516503a11cd5ca435acc9bb6523536?s={$size}"; // ad516503a11cd5ca435acc9bb6523536 == md5('unknown@gravatar.com')
        } elseif (!empty($email) && 'gravatar_default' == $default) {
            $default = '';
        } elseif ('gravatar_default' == $default) {
            $default = "$host/avatar/?s={$size}";
        } elseif (empty($email)) {
            $default = "$host/avatar/?d=$default&amp;s={$size}";
        }

        if (!empty($email)) {
            $out = "$host/avatar/";
            $out .= $email_hash;
            $out .= '?s=' . $size;
            $out .= '&amp;d=' . urlencode($default);

            $ratings = array('G', 'PG', 'R', 'X');
            $rating = 'R';
            $out .= "&amp;r={$rating}";

            $out = str_replace('&#038;', '&amp;', $out);
            $avatar = "<img alt='{$alt}' src='{$out}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
        } else {
            $out = $default;
            $avatar = "<img alt='{$alt}' src='{$out}' class='avatar avatar-{$size} photo avatar-default' height='{$size}' width='{$size}' />";
        }
        return $avatar;
    }

    public function get_comment_author_link($author, $url = '') {
        if ($url == '') {
            $url = '#';
        }
        if (strpos("http://", "#") !== 0) {
            $url = 'http://' . $url;
        }
        $link = "<a href='$url' rel='external nofollow' class='url'>$author</a>";
        return $link;
    }

    public function get_comment_link($comment = null, $args = array()) {
        $link = '';
        $comment_id = $comment['comment_id'];
        $link .= 'id="comment-reply-link-' . $comment_id . '"';
        return "#";
    }

    public function get_comment_time($comment = null) {
        return $comment['comment_date'];
    }

    public function edit_comment_link($comment, $before = '', $after = '') {
        $link = '<a class="comment-edit-link" href="#">' . $this->lang['edit'] . '</a>';
        return $before . $link . $after;
    }

    public function get_comment_text($comment, $args = array()) {
        return $comment['comment_content'];
    }

    public function comment_reply_link($comment, $add_below) {
        $respond_id = 'response';
        $comment_id = $comment[$this->db_fields['id']];
        $post_id = $comment[$this->db_fields['post_id']];
        $reply_text = $this->lang['reply_text'];
        $link = "<a class='comment-reply-link' href='#' onclick='return addComment.moveForm(\"$add_below-$comment_id\", \"$comment_id\", \"$respond_id\", \"$post_id\")'>$reply_text</a>";
        return $link;
    }

    /**
     * Determine if SSL is used.
     *
     * @since 2.6.0
     *
     * @return bool True if SSL, false if not used.
     */
    public function is_ssl() {
        if (isset($_SERVER['HTTPS'])) {
            if ('on' == strtolower($_SERVER['HTTPS']))
                return true;
            if ('1' == $_SERVER['HTTPS'])
                return true;
        } elseif (isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] )) {
            return true;
        }
        return false;
    }

}
