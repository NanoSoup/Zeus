<?php

namespace Zeus\Wordpress;

/**
 * Class UserPermissions
 * @package Zeus\Wordpress
 */
class UserPermissions
{
    /**
     * UserPermissions constructor.
     *
     * Note if changing any permissions you will need to remove the role by
     * using remove_role('role_name'); and then the add_role functions will
     * then update the changes.
     */
    public function __construct()
    {
        add_role(
            'account_manager',
            __('Account Manager'),
            [
                'activate_plugins' => false,
                'delete_others_pages' => false,
                'delete_others_posts' => false,
                'delete_pages' => false,
                'delete_posts' => false,
                'delete_private_pages' => false,
                'delete_private_posts' => false,
                'delete_published_pages' => false,
                'delete_published_posts' => false,
                'edit_dashboard' => false,
                'edit_others_pages' => false,
                'edit_others_posts' => false,
                'edit_pages' => false,
                'edit_posts' => false,
                'edit_private_pages' => false,
                'edit_private_posts' => false,
                'edit_published_pages' => false,
                'edit_published_posts' => false,
                'edit_theme_options' => false,
                'export' => false,
                'import' => false,
                'list_users' => true,
                'manage_categories' => false,
                'manage_links' => false,
                'manage_options' => false,
                'moderate_comments' => false,
                'promote_users' => true,
                'publish_pages' => false,
                'publish_posts' => false,
                'read_private_pages' => false,
                'read_private_posts' => false,
                'read' => true,
                'remove_users' => true,
                'switch_themes' => false,
                'upload_files' => false,
                'customize' => false,
                'delete_site' => false,
            ]
        );
    }
}
