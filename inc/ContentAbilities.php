<?php
/** Limited WordPress content abilities for MCP OAuth Companion. */
declare(strict_types=1);
namespace McpOAuthCompanion;

final class ContentAbilities {
    public static function boot(): void {
        add_action('wp_abilities_api_categories_init', [self::class, 'register_category']);
        add_action('wp_abilities_api_init', [self::class, 'register_abilities'], 20);
    }

    public static function register_category(): void {
        wp_register_ability_category('mcp-content', [
            'label' => 'MCP Content',
            'description' => 'WordPress post editing, publishing and revision operations for authenticated editors.',
        ]);
    }

    private static function schema(array $properties, array $required = []): array {
        return ['type' => 'object', 'properties' => $properties, 'required' => $required, 'additionalProperties' => false];
    }

    public static function register_abilities(): void {
        if (!function_exists('wp_register_ability')) { return; }
        $int = ['type' => 'integer'];
        $str = ['type' => 'string'];
        $ids = ['type' => 'array', 'items' => $int];
        $fields = [
            'title' => $str,
            'content' => $str,
            'excerpt' => $str,
            'categories' => $ids,
            'tags' => $ids,
            'featured_media' => $int,
            'author' => $int,
        ];
        $defs = [
            'mcp-content/trash-content' => [
                'Move Post or Page to Trash', 'Move an editable post or page to WordPress Trash (recoverable), with exact title and modified-time confirmation.',
                self::schema(['id' => $int, 'expected_title' => $str, 'expected_modified_gmt' => $str, 'confirm' => ['type' => 'string', 'enum' => ['TRASH']]], ['id', 'expected_title', 'expected_modified_gmt', 'confirm']),
                [self::class, 'trash_content'], false,
            ],
            'mcp-media/trash' => [
                'Move Media to Trash', 'Move an image attachment to WordPress Trash when media trash is enabled, with exact URL confirmation. Never permanently delete.',
                self::schema(['id' => $int, 'expected_url' => $str, 'confirm' => ['type' => 'string', 'enum' => ['TRASH']]], ['id', 'expected_url', 'confirm']),
                [self::class, 'trash_media'], false,
            ],
            'mcp-content/edit-snippet' => [
                'Edit Exact Content Snippet', 'Replace one exact occurrence in post or page content, with modification conflict protection.',
                self::schema(['id' => $int, 'expected_modified_gmt' => $str, 'old_content' => $str, 'new_content' => $str], ['id', 'expected_modified_gmt', 'old_content', 'new_content']),
                [self::class, 'edit_snippet'], false,
            ],
            'mcp-content/create-page-draft' => [
                'Create Page Draft', 'Create an unpublished WordPress page.',
                self::schema(['title' => $str, 'content' => $str, 'excerpt' => $str], ['title']),
                [self::class, 'create_page_draft'], false,
            ],
            'mcp-content/update-page' => [
                'Update Page', 'Update an editable draft or published WordPress page without changing its publication status.',
                self::schema(['id' => $int, 'expected_modified_gmt' => $str, 'title' => $str, 'content' => $str, 'excerpt' => $str], ['id', 'expected_modified_gmt']),
                [self::class, 'update_page'], false,
            ],
            'mcp-content/publish-page' => [
                'Publish Page', 'Publish an editable page draft, with conflict protection.',
                self::schema(['id' => $int, 'expected_modified_gmt' => $str], ['id', 'expected_modified_gmt']),
                [self::class, 'publish_page'], false,
            ],
            'mcp-media/search' => [
                'Search Media', 'Search the existing WordPress media library for attachments.',
                self::schema(['query' => $str, 'page' => ['type' => 'integer', 'minimum' => 1], 'per_page' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50]]),
                [self::class, 'search_media'], true,
            ],
            'mcp-media/update-alt' => [
                'Update Image Alt Text', 'Update alt text for an image attachment.',
                self::schema(['id' => $int, 'alt_text' => $str], ['id', 'alt_text']),
                [self::class, 'update_media_alt'], false,
            ],
            'mcp-media/upload-image' => [
                'Upload Image', 'Upload a PNG, JPEG, GIF or WebP image to the media library using base64 content, limited to 5 MiB.',
                self::schema(['filename' => $str, 'mime_type' => ['type' => 'string', 'enum' => ['image/png', 'image/jpeg', 'image/gif', 'image/webp']], 'base64' => $str, 'alt_text' => $str], ['filename', 'mime_type', 'base64']),
                [self::class, 'upload_image'], false,
            ],
            'mcp-content/list-posts' => [
                'List Posts', 'List accessible WordPress posts and drafts with pagination.',
                self::schema(['page' => ['type' => 'integer', 'minimum' => 1], 'per_page' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50], 'status' => ['type' => 'string', 'enum' => ['draft', 'publish', 'pending', 'private', 'future']]]),
                [self::class, 'list_posts'], true,
            ],
            'mcp-content/search-content' => [
                'Search Content', 'Search accessible posts and pages by title, text, slug, or exact URL.',
                self::schema([
                    'query' => $str,
                    'url' => $str,
                    'slug' => $str,
                    'post_type' => ['type' => 'string', 'enum' => ['post', 'page', 'any']],
                    'status' => ['type' => 'string', 'enum' => ['any', 'draft', 'publish', 'pending', 'private', 'future']],
                    'page' => ['type' => 'integer', 'minimum' => 1],
                    'per_page' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50],
                ]), [self::class, 'search_content'], true,
            ],
            'mcp-content/get-content' => [
                'Read Content', 'Read an accessible post or page by ID.',
                self::schema(['id' => $int], ['id']),
                [self::class, 'get_content'], true,
            ],
            'mcp-content/list-authors' => [
                'List Authors', 'List users eligible to be assigned as authors.',
                self::schema(['query' => $str, 'per_page' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50]]),
                [self::class, 'list_authors'], true,
            ],
            'mcp-content/set-author' => [
                'Set Post Author', 'Explicitly assign an eligible author to an editable post with conflict protection.',
                self::schema(['id' => $int, 'author' => $int, 'expected_modified_gmt' => $str], ['id', 'author', 'expected_modified_gmt']),
                [self::class, 'set_author'], false,
            ],
            'mcp-content/get-post' => [
                'Read Post', 'Read an accessible WordPress post by ID.',
                self::schema(['id' => $int], ['id']),
                [self::class, 'get_post'], true,
            ],
            'mcp-content/create-draft' => [
                'Create Draft', 'Create a new unpublished WordPress post draft; cannot publish.',
                self::schema($fields, ['title']),
                [self::class, 'create_draft'], false,
            ],
            'mcp-content/update-draft' => [
                'Update Draft', 'Update an existing draft post only; published posts cannot be changed.',
                self::schema(['id' => $int] + $fields, ['id']),
                [self::class, 'update_draft'], false,
            ],
            'mcp-content/update-published' => [
                'Update Published Post', 'Edit a published post without unpublishing it; preserves WordPress revision history.',
                self::schema(['id' => $int, 'expected_modified_gmt' => $str] + $fields, ['id', 'expected_modified_gmt']),
                [self::class, 'update_published'], false,
            ],
            'mcp-content/publish-draft' => [
                'Publish Draft', 'Publish an existing draft post with an explicit publish action.',
                self::schema(['id' => $int, 'expected_modified_gmt' => $str], ['id', 'expected_modified_gmt']),
                [self::class, 'publish_draft'], false,
            ],
            'mcp-content/list-revisions' => [
                'List Post Revisions', 'List recent revisions of an accessible post.',
                self::schema(['id' => $int, 'per_page' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50]], ['id']),
                [self::class, 'list_revisions'], true,
            ],
            'mcp-content/get-revision' => [
                'Read Post Revision', 'Read a revision of an accessible post for comparison.',
                self::schema(['id' => $int, 'revision_id' => $int], ['id', 'revision_id']),
                [self::class, 'get_revision'], true,
            ],
        ];
        foreach ($defs as $name => [$label, $description, $input, $callback, $readonly]) {
            wp_register_ability($name, [
                'label' => $label,
                'description' => $description,
                'category' => 'mcp-content',
                'input_schema' => $input,
                'execute_callback' => $callback,
                'permission_callback' => static function () use ($name): bool {
                    if (!is_user_logged_in()) { return false; }
                    if (str_starts_with($name, 'mcp-media/')) {
                        return current_user_can('upload_files');
                    }
                    if (in_array($name, ['mcp-content/create-page-draft', 'mcp-content/update-page', 'mcp-content/publish-page'], true)) {
                        return current_user_can('edit_pages');
                    }
                    return current_user_can('edit_posts') || current_user_can('edit_pages');
                },
                'meta' => [
                    'show_in_rest' => true,
                    'mcp' => ['public' => true],
                    'annotations' => ['readonly' => $readonly, 'destructive' => in_array($name, ['mcp-content/trash-content', 'mcp-media/trash'], true), 'idempotent' => $readonly],
                ],
            ]);
        }
    }

    public static function can_edit_posts(): bool {
        return is_user_logged_in() && current_user_can('edit_posts');
    }

    private static function present(\WP_Post $post, bool $full = false): array {
        $out = [
            'id' => (int) $post->ID,
            'title' => get_the_title($post),
            'status' => $post->post_status,
            'url' => get_permalink($post),
            'modified_gmt' => $post->post_modified_gmt,
            'slug' => $post->post_name,
            'post_type' => $post->post_type,
            'author' => (int) $post->post_author,
            'author_name' => get_the_author_meta('display_name', (int) $post->post_author),
        ];
        if ($full) {
            $out['content'] = $post->post_content;
            $out['excerpt'] = $post->post_excerpt;
            $out['categories'] = array_map('intval', wp_get_post_categories($post->ID));
            $out['tags'] = array_map('intval', wp_get_post_tags($post->ID, ['fields' => 'ids']));
            $out['featured_media'] = (int) get_post_thumbnail_id($post->ID);
        }
        return $out;
    }

    public static function list_posts(array $input): array {
        $page = max(1, (int) ($input['page'] ?? 1));
        $per_page = min(50, max(1, (int) ($input['per_page'] ?? 10)));
        $status = $input['status'] ?? 'draft';
        $allowed = ['draft', 'publish', 'pending', 'private', 'future'];
        if (!in_array($status, $allowed, true)) { return ['error' => 'Invalid post status.']; }
        $query = new \WP_Query([
            'post_type' => 'post', 'post_status' => $status,
            'posts_per_page' => $per_page, 'paged' => $page,
            'orderby' => 'modified', 'order' => 'DESC',
        ]);
        $posts = [];
        foreach ($query->posts as $post) {
            if (current_user_can('read_post', $post->ID)) { $posts[] = self::present($post); }
        }
        return ['posts' => $posts, 'page' => $page, 'total_found' => null, 'total_pages' => null];
    }

    public static function get_post(array $input) {
        $post = get_post((int) $input['id']);
        if (!$post || $post->post_type !== 'post' || !current_user_can('read_post', $post->ID)) {
            return new \WP_Error('mcp_post_forbidden', 'Post not found or access denied.');
        }
        return self::present($post, true);
    }


    private static function readable_content($post): bool {
        return $post instanceof \WP_Post
            && in_array($post->post_type, ['post', 'page'], true)
            && current_user_can('read_post', $post->ID);
    }

    public static function get_content(array $input) {
        $post = get_post((int) $input['id']);
        if (!self::readable_content($post)) {
            return new \WP_Error('mcp_content_forbidden', 'Content not found or access denied.');
        }
        return self::present($post, true);
    }

    public static function search_content(array $input) {
        $page = max(1, (int) ($input['page'] ?? 1));
        $per_page = min(50, max(1, (int) ($input['per_page'] ?? 20)));
        $type = $input['post_type'] ?? 'any';
        $status = $input['status'] ?? 'any';
        $query = trim((string) ($input['query'] ?? ''));
        $slug = trim((string) ($input['slug'] ?? ''));
        $url = trim((string) ($input['url'] ?? ''));
        if ($query === '' && $slug === '' && $url === '') {
            return new \WP_Error('mcp_search_required', 'Provide query, slug, or URL.');
        }
        if ($url !== '') {
            $site_host = wp_parse_url(home_url(), PHP_URL_HOST);
            $url_host = wp_parse_url($url, PHP_URL_HOST);
            if (!$url_host || strtolower((string) $url_host) !== strtolower((string) $site_host)) {
                return new \WP_Error('mcp_external_url', 'URL must belong to this WordPress site.');
            }
            $resolved = url_to_postid($url);
            if ($resolved) {
                $post = get_post($resolved);
                if (self::readable_content($post) && ($type === 'any' || $post->post_type === $type)
                    && ($status === 'any' || $post->post_status === $status)) {
                    return ['posts' => [self::present($post)], 'page' => 1, 'total_found' => 1, 'total_pages' => 1];
                }
            }
            // Broken permalink? Fall back to its last path segment as a slug.
            $path = trim((string) wp_parse_url($url, PHP_URL_PATH), '/');
            $slug = sanitize_title(rawurldecode(basename($path)));
            if ($slug === '') {
                return ['posts' => [], 'page' => 1, 'total_found' => 0, 'total_pages' => 0];
            }
        }
        $args = [
            'post_type' => $type === 'any' ? ['post', 'page'] : $type,
            'post_status' => $status === 'any' ? ['publish', 'draft', 'pending', 'private', 'future'] : $status,
            'posts_per_page' => $per_page, 'paged' => $page,
            'orderby' => 'relevance', 'order' => 'DESC',
        ];
        if ($slug !== '') {
            $args['name'] = sanitize_title($slug);
            $args['orderby'] = 'modified';
        } else {
            $args['s'] = $query;
        }
        $found = new \WP_Query($args);
        $posts = [];
        foreach ($found->posts as $post) {
            if (self::readable_content($post)) { $posts[] = self::present($post); }
        }
        return [
            'posts' => $posts, 'page' => $page,
            'total_found' => (int) $found->found_posts,
            'total_pages' => (int) $found->max_num_pages,
        ];
    }

    public static function list_authors(array $input): array {
        $args = [
            'number' => min(50, max(1, (int) ($input['per_page'] ?? 20))),
            'who' => 'authors', 'orderby' => 'display_name', 'order' => 'ASC',
        ];
        if (!empty($input['query'])) { $args['search'] = '*' . sanitize_text_field($input['query']) . '*'; }
        $users = get_users($args);
        return ['authors' => array_map(static function ($user) {
            return ['id' => (int) $user->ID, 'display_name' => $user->display_name];
        }, $users)];
    }

    public static function set_author(array $input) {
        $id = (int) $input['id'];
        $post = get_post($id);
        if (!$post || $post->post_type !== 'post' || !current_user_can('edit_post', $id)) {
            return new \WP_Error('mcp_author_forbidden', 'Post not found or access denied.');
        }
        $fresh = self::check_fresh($post, $input);
        if (is_wp_error($fresh)) { return $fresh; }
        $author = get_userdata((int) $input['author']);
        if (!$author || !user_can($author, 'edit_posts')) {
            return new \WP_Error('mcp_author_invalid', 'Author must be an existing user allowed to write posts.');
        }
        // Editors may reassign authors; authors cannot reassign posts to other users.
        if (!current_user_can('edit_others_posts') && (int) $author->ID !== get_current_user_id()) {
            return new \WP_Error('mcp_author_forbidden', 'Not permitted to assign another author.');
        }
        $result = wp_update_post(['ID' => $id, 'post_author' => (int) $author->ID], true);
        if (is_wp_error($result)) { return $result; }
        return self::present(get_post($id), true);
    }


    public static function trash_content(array $input) {
        $id = (int) $input['id'];
        $post = get_post($id);
        if (!$post || !in_array($post->post_type, ['post', 'page'], true)
            || in_array($post->post_status, ['trash', 'auto-draft'], true)
            || !current_user_can('delete_post', $id)) {
            return new \WP_Error('mcp_trash_forbidden', 'Post/page not found or trashing not permitted.');
        }
        if ($input['confirm'] !== 'TRASH' || $input['expected_title'] !== get_the_title($post)) {
            return new \WP_Error('mcp_trash_confirmation', 'Confirmation or exact title does not match.');
        }
        $fresh = self::check_fresh($post, $input);
        if (is_wp_error($fresh)) { return $fresh; }
        if (!EMPTY_TRASH_DAYS) {
            return new \WP_Error('mcp_trash_unavailable', 'WordPress trash is disabled; permanent deletion is not supported.');
        }
        $trashed = wp_trash_post($id);
        if (!$trashed || is_wp_error($trashed)) {
            return new \WP_Error('mcp_trash_failed', 'Could not move the post/page to Trash.');
        }
        return ['id' => $id, 'post_type' => $post->post_type, 'status' => get_post_status($id), 'recoverable' => true];
    }

    public static function trash_media(array $input) {
        $id = (int) $input['id'];
        $post = get_post($id);
        if (!$post || $post->post_type !== 'attachment' || !wp_attachment_is_image($id)
            || !current_user_can('delete_post', $id)) {
            return new \WP_Error('mcp_media_trash_forbidden', 'Image not found or trashing not permitted.');
        }
        if ($input['confirm'] !== 'TRASH' || $input['expected_url'] !== wp_get_attachment_url($id)) {
            return new \WP_Error('mcp_media_trash_confirmation', 'Confirmation or exact image URL does not match.');
        }
        if (!defined('MEDIA_TRASH') || !MEDIA_TRASH || !EMPTY_TRASH_DAYS) {
            return new \WP_Error('mcp_media_trash_unavailable', 'Recoverable media trash is disabled; permanent deletion is not supported.');
        }
        $trashed = wp_trash_post($id);
        if (!$trashed || is_wp_error($trashed)) {
            return new \WP_Error('mcp_media_trash_failed', 'Could not move image to Trash.');
        }
        return ['id' => $id, 'status' => get_post_status($id), 'recoverable' => true];
    }

    public static function edit_snippet(array $input) {
        $post = get_post((int) $input['id']);
        if (!$post || !in_array($post->post_type, ['post', 'page'], true)
            || !in_array($post->post_status, ['draft', 'publish', 'private', 'pending', 'future'], true)
            || !current_user_can('edit_post', $post->ID)) {
            return new \WP_Error('mcp_snippet_forbidden', 'Content not found or editing not permitted.');
        }
        $fresh = self::check_fresh($post, $input);
        if (is_wp_error($fresh)) { return $fresh; }
        $old = (string) $input['old_content'];
        if ($old === '') { return new \WP_Error('mcp_snippet_empty', 'The matching snippet must not be empty.'); }
        $count = substr_count($post->post_content, $old);
        if ($count !== 1) {
            return new \WP_Error('mcp_snippet_match', $count === 0
                ? 'Exact snippet not found; refresh the content and retry.'
                : 'Snippet occurs more than once; provide a longer unique passage.');
        }
        $updated = str_replace($old, (string) $input['new_content'], $post->post_content);
        if ($updated === $post->post_content) {
            return new \WP_Error('mcp_snippet_unchanged', 'Replacement is identical; nothing changed.');
        }
        $result = wp_update_post(wp_slash(['ID' => $post->ID, 'post_content' => $updated]), true);
        if (is_wp_error($result)) { return $result; }
        $current = get_post($post->ID);
        return ['id' => (int) $current->ID, 'status' => $current->post_status,
            'modified_gmt' => $current->post_modified_gmt, 'replacements' => 1];
    }

    public static function create_page_draft(array $input) {
        if (!current_user_can('edit_pages')) {
            return new \WP_Error('mcp_page_forbidden', 'Not permitted to create pages.');
        }
        $data = ['post_type' => 'page', 'post_status' => 'draft', 'post_title' => $input['title']];
        foreach (['content' => 'post_content', 'excerpt' => 'post_excerpt'] as $field => $key) {
            if (array_key_exists($field, $input)) { $data[$key] = $input[$field]; }
        }
        $id = wp_insert_post(wp_slash($data), true);
        return is_wp_error($id) ? $id : self::present(get_post($id), true);
    }

    public static function update_page(array $input) {
        $post = get_post((int) $input['id']);
        if (!$post || $post->post_type !== 'page' || !current_user_can('edit_post', $post->ID)) {
            return new \WP_Error('mcp_page_forbidden', 'Page not found or access denied.');
        }
        $fresh = self::check_fresh($post, $input);
        if (is_wp_error($fresh)) { return $fresh; }
        $data = ['ID' => $post->ID];
        foreach (['title' => 'post_title', 'content' => 'post_content', 'excerpt' => 'post_excerpt'] as $field => $key) {
            if (array_key_exists($field, $input)) { $data[$key] = $input[$field]; }
        }
        if (count($data) === 1) { return new \WP_Error('mcp_page_empty', 'No page changes supplied.'); }
        $id = wp_update_post(wp_slash($data), true);
        return is_wp_error($id) ? $id : self::present(get_post($id), true);
    }

    public static function publish_page(array $input) {
        $post = get_post((int) $input['id']);
        if (!$post || $post->post_type !== 'page' || $post->post_status !== 'draft'
            || !current_user_can('publish_pages') || !current_user_can('edit_post', $post->ID)) {
            return new \WP_Error('mcp_page_forbidden', 'Only authorized users can publish an editable page draft.');
        }
        $fresh = self::check_fresh($post, $input);
        if (is_wp_error($fresh)) { return $fresh; }
        $id = wp_update_post(['ID' => $post->ID, 'post_status' => 'publish'], true);
        return is_wp_error($id) ? $id : self::present(get_post($id), true);
    }

    private static function media_details(\WP_Post $post): array {
        return ['id' => (int) $post->ID, 'title' => get_the_title($post),
            'url' => wp_get_attachment_url($post->ID),
            'mime_type' => $post->post_mime_type,
            'alt_text' => (string) get_post_meta($post->ID, '_wp_attachment_image_alt', true)];
    }

    public static function search_media(array $input): array {
        $page = max(1, (int) ($input['page'] ?? 1));
        $query = new \WP_Query([
            'post_type' => 'attachment', 'post_status' => 'inherit',
            'post_mime_type' => 'image', 's' => (string) ($input['query'] ?? ''),
            'posts_per_page' => min(50, max(1, (int) ($input['per_page'] ?? 20))),
            'paged' => $page, 'orderby' => 'date', 'order' => 'DESC',
        ]);
        $items = [];
        foreach ($query->posts as $post) {
            if (current_user_can('read_post', $post->ID)) { $items[] = self::media_details($post); }
        }
        return ['media' => $items, 'page' => $page,
            'total_found' => null, 'total_pages' => null];
    }

    public static function update_media_alt(array $input) {
        $post = get_post((int) $input['id']);
        if (!$post || $post->post_type !== 'attachment' || !wp_attachment_is_image($post->ID)
            || !current_user_can('edit_post', $post->ID)) {
            return new \WP_Error('mcp_media_forbidden', 'Image not found or access denied.');
        }
        update_post_meta($post->ID, '_wp_attachment_image_alt', sanitize_text_field($input['alt_text']));
        return self::media_details($post);
    }

    public static function upload_image(array $input) {
        if (!current_user_can('upload_files')) {
            return new \WP_Error('mcp_media_forbidden', 'Not permitted to upload media.');
        }
        $name = sanitize_file_name((string) $input['filename']);
        $mime = (string) $input['mime_type'];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $types = ['png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif', 'webp' => 'image/webp'];
        if (!$name || !isset($types[$ext]) || $types[$ext] !== $mime) {
            return new \WP_Error('mcp_media_type', 'Filename extension and MIME type must match a supported image format.');
        }
        $encoded = (string) $input['base64'];
        if (strlen($encoded) > 7200000) {
            return new \WP_Error('mcp_media_size', 'Image exceeds the 5 MiB limit.');
        }
        $bytes = base64_decode($encoded, true);
        if ($bytes === false || strlen($bytes) > 5 * 1024 * 1024 || strlen($bytes) === 0) {
            return new \WP_Error('mcp_media_data', 'Invalid base64 image or image exceeds 5 MiB.');
        }
        $info = @getimagesizefromstring($bytes);
        if (!$info || ($info['mime'] ?? '') !== $mime) {
            return new \WP_Error('mcp_media_data', 'Uploaded bytes are not a valid image of the declared type.');
        }
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $upload = wp_upload_bits($name, null, $bytes);
        if (!empty($upload['error'])) {
            return new \WP_Error('mcp_media_upload', (string) $upload['error']);
        }
        $attachment = ['post_mime_type' => $mime,
            'post_title' => sanitize_text_field(pathinfo($name, PATHINFO_FILENAME)),
            'post_status' => 'inherit'];
        $id = wp_insert_attachment($attachment, $upload['file'], 0, true);
        if (is_wp_error($id)) { @unlink($upload['file']); return $id; }
        $metadata = wp_generate_attachment_metadata($id, $upload['file']);
        if (is_array($metadata)) { wp_update_attachment_metadata($id, $metadata); }
        if (isset($input['alt_text'])) {
            update_post_meta($id, '_wp_attachment_image_alt', sanitize_text_field($input['alt_text']));
        }
        return self::media_details(get_post($id));
    }

    private static function validate_terms(array $input) {
        foreach (['categories' => 'category', 'tags' => 'post_tag'] as $field => $taxonomy) {
            if (!array_key_exists($field, $input)) { continue; }
            if (!current_user_can('assign_terms', $taxonomy) && !current_user_can('edit_posts')) {
                return new \WP_Error('mcp_terms_forbidden', 'Not permitted to assign terms.');
            }
            foreach ($input[$field] as $id) {
                if (!term_exists((int) $id, $taxonomy)) {
                    return new \WP_Error('mcp_invalid_term', 'Invalid category or tag ID.');
                }
            }
        }
        if (array_key_exists('featured_media', $input) && (int) $input['featured_media'] !== 0) {
            $attachment = get_post((int) $input['featured_media']);
            if (!$attachment || $attachment->post_type !== 'attachment' || !wp_attachment_is_image($attachment->ID)) {
                return new \WP_Error('mcp_invalid_image', 'Featured media must be an existing image attachment.');
            }
        }
        return true;
    }

    private static function save_fields(array $input, ?int $id = null) {
        $valid = self::validate_terms($input);
        if (is_wp_error($valid)) { return $valid; }
        $data = ['post_type' => 'post'];
        if ($id === null) { $data['post_status'] = 'draft'; }
        if ($id !== null) { $data['ID'] = $id; }
        foreach (['title' => 'post_title', 'content' => 'post_content', 'excerpt' => 'post_excerpt'] as $field => $key) {
            if (array_key_exists($field, $input)) { $data[$key] = $input[$field]; }
        }
        // wp_insert_post / wp_update_post enforce WordPress content sanitization.
        $result = $id === null ? wp_insert_post(wp_slash($data), true) : wp_update_post(wp_slash($data), true);
        if (is_wp_error($result)) { return $result; }
        foreach (['categories' => 'category', 'tags' => 'post_tag'] as $field => $taxonomy) {
            if (array_key_exists($field, $input)) {
                $term_result = wp_set_object_terms($result, array_map('intval', $input[$field]), $taxonomy, false);
                if (is_wp_error($term_result)) { return $term_result; }
            }
        }
        if (array_key_exists('featured_media', $input)) {
            if ((int) $input['featured_media'] === 0) { delete_post_thumbnail($result); }
            else { set_post_thumbnail($result, (int) $input['featured_media']); }
        }
        return self::present(get_post($result), true);
    }

    public static function create_draft(array $input) {
        if (!current_user_can('edit_posts')) { return new \WP_Error('mcp_forbidden', 'Permission denied.'); }
        return self::save_fields($input);
    }

    public static function update_draft(array $input) {
        $id = (int) $input['id'];
        $post = get_post($id);
        if (!$post || $post->post_type !== 'post' || $post->post_status !== 'draft' || !current_user_can('edit_post', $id)) {
            return new \WP_Error('mcp_draft_forbidden', 'Only editable draft posts can be updated.');
        }
        return self::save_fields($input, $id);
    }
    private static function check_fresh(\WP_Post $post, array $input) {
        // Compare the version the client read with the current post to prevent lost edits.
        if (!isset($input['expected_modified_gmt']) || $input['expected_modified_gmt'] !== $post->post_modified_gmt) {
            return new \WP_Error('mcp_edit_conflict', 'Post has changed since it was read. Refresh and review changes before retrying.');
        }
        return true;
    }

    public static function update_published(array $input) {
        $id = (int) $input['id'];
        $post = get_post($id);
        if (!$post || $post->post_type !== 'post' || $post->post_status !== 'publish' || !current_user_can('edit_post', $id)) {
            return new \WP_Error('mcp_published_forbidden', 'Only editable published posts can be updated.');
        }
        $fresh = self::check_fresh($post, $input);
        if (is_wp_error($fresh)) { return $fresh; }
        // save_fields updates only explicitly provided fields, preserving published status.
        return self::save_fields($input, $id);
    }

    public static function publish_draft(array $input) {
        $id = (int) $input['id'];
        $post = get_post($id);
        if (!$post || $post->post_type !== 'post' || $post->post_status !== 'draft' || !current_user_can('publish_posts') || !current_user_can('edit_post', $id)) {
            return new \WP_Error('mcp_publish_forbidden', 'Only authorized users can publish an editable draft.');
        }
        $fresh = self::check_fresh($post, $input);
        if (is_wp_error($fresh)) { return $fresh; }
        $result = wp_update_post(['ID' => $id, 'post_status' => 'publish'], true);
        if (is_wp_error($result)) { return $result; }
        return self::present(get_post($id), true);
    }

    public static function list_revisions(array $input) {
        $id = (int) $input['id'];
        $post = get_post($id);
        if (!$post || $post->post_type !== 'post' || !current_user_can('edit_post', $id)) {
            return new \WP_Error('mcp_revision_forbidden', 'Post not found or access denied.');
        }
        $limit = min(50, max(1, (int) ($input['per_page'] ?? 20)));
        $revisions = wp_get_post_revisions($id, ['posts_per_page' => $limit]);
        $items = [];
        foreach ($revisions as $rev) {
            $items[] = ['id' => (int) $rev->ID, 'date_gmt' => $rev->post_date_gmt, 'modified_gmt' => $rev->post_modified_gmt, 'author' => (int) $rev->post_author];
        }
        return ['post_id' => $id, 'revisions' => $items];
    }

    public static function get_revision(array $input) {
        $id = (int) $input['id'];
        $post = get_post($id);
        if (!$post || $post->post_type !== 'post' || !current_user_can('edit_post', $id)) {
            return new \WP_Error('mcp_revision_forbidden', 'Post not found or access denied.');
        }
        $revision_id = (int) $input['revision_id'];
        $rev = wp_get_post_revision($revision_id);
        if (!$rev || (int) $rev->post_parent !== $id) {
            return new \WP_Error('mcp_revision_missing', 'Revision does not belong to this post.');
        }
        return ['id' => (int) $rev->ID, 'post_id' => $id, 'date_gmt' => $rev->post_date_gmt, 'title' => $rev->post_title, 'content' => $rev->post_content, 'excerpt' => $rev->post_excerpt];
    }

}
