<?php
/**
 * Class Siberian_Migration_Db_Table
 *
 * Migration Class to update DB reflecting latest schema.
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.1.0
 *
 */

class Siberian_Migration_Db extends Zend_Db_Table_Abstract {

    /**
     * All tables from PE excluding optional modules
     *
     * @note remove when full process is done.
     */
    const TABLES = array(
        'acl_resource',
        'acl_resource_role',
        'acl_role',
        'admin',
        'api_key',
        'api_provider',
        'api_user',
        'application',
        'application_acl_option',
        'application_admin',
        'application_device',
        'application_layout_homepage',
        'application_option',
        'application_option_category',
        'application_option_layout',
        'application_option_preview',
        'application_option_preview_language',
        'application_option_value',
        'application_tc',
        'backoffice_notification',
        'backoffice_user',
        'booking',
        'booking_store',
        'catalog_category',
        'catalog_product',
        'catalog_product_folder_category',
        'catalog_product_format',
        'catalog_product_group',
        'catalog_product_group_option',
        'catalog_product_group_option_value',
        'catalog_product_group_value',
        'cms_application_block',
        'cms_application_page',
        'cms_application_page_block',
        'cms_application_page_block_address',
        'cms_application_page_block_button',
        'cms_application_page_block_file',
        'cms_application_page_block_image',
        'cms_application_page_block_image_library',
        'cms_application_page_block_slider',
        'cms_application_page_block_text',
        'cms_application_page_block_video',
        'cms_application_page_block_video_link',
        'cms_application_page_block_video_podcast',
        'cms_application_page_block_video_youtube',
        'comment',
        'comment_answer',
        'comment_like',
        'comment_radius',
        'contact',
        'customer',
        'customer_address',
        'customer_social',
        'customer_social_post',
        'event',
        'event_custom',
        'folder',
        'folder_category',
        'form',
        'form_field',
        'form_section',
        'inbox_reply',
        'inbox_message',
        'inbox_customer_message',
        'log',
        'loyalty_card',
        'loyalty_card_customer',
        'loyalty_card_customer_log',
        'loyalty_card_password',
        'maps',
        'mcommerce',
        'mcommerce_cart',
        'mcommerce_cart_line',
        'mcommerce_delivery_method',
        'mcommerce_order',
        'mcommerce_order_line',
        'mcommerce_payment_method',
        'mcommerce_store',
        'mcommerce_store_delivery_method',
        'mcommerce_store_payment_method',
        'mcommerce_store_payment_method_paypal',
        'mcommerce_store_payment_method_stripe',
        'mcommerce_store_printer',
        'mcommerce_store_tax',
        'mcommerce_tax',
        'media_gallery_image',
        'media_gallery_image_custom',
        'media_gallery_image_instagram',
        'media_gallery_image_picasa',
        'media_gallery_music',
        'media_gallery_music_album',
        'media_gallery_music_elements',
        'media_gallery_music_track',
        'media_gallery_video',
        'media_gallery_video_itunes',
        'media_gallery_video_vimeo',
        'media_gallery_video_youtube',
        'media_library',
        'media_library_image',
        'message_application',
        'message_application_file',
        'module',
        'padlock',
        'padlock_value',
        'promotion',
        'promotion_customer',
        'push_apns_devices',
        'push_certificate',
        'push_delivered_message',
        'push_gcm_devices',
        'push_messages',
        'radio',
        'rss_feed',
        'sales_invoice',
        'sales_invoice_line',
        'sales_order',
        'sales_order_line',
        'session',
        'social_facebook',
        'socialgaming_game',
        'source_code',
        'subscription',
        'subscription_acl_resource',
        'subscription_application',
        'subscription_application_detail',
        'system_config',
        'tax',
        'template_block',
        'template_block_app',
        'template_block_white_label_editor',
        'template_category',
        'template_design',
        'template_design_block',
        'template_design_category',
        'template_design_content',
        'topic',
        'topic_category',
        'topic_category_message',
        'topic_subscription',
        'weather',
        'weblink',
        'weblink_link',
        'whitelabel_editor',
        'wordpress',
        'wordpress_category',
    );

    /**
     * Check whether a Table exists or not
     *
     * @param string $table_name
     * @return boolean
     * @throws Exception
     */
    public function tableExists($table_name) {

        try {
            $this->getAdapter()->describeTable($table_name);
            return true;
        }
        catch(Exception $e) {}

        return false;
    }

    /**
     * Run through all tables to export the current database
     *
     * @note remove when full process is done.
     *
     * @throws Exception
     */
    public function exportAll() {
        foreach(self::TABLES as $table_name) {
            $migration_db_table = new Siberian_Migration_Db_Table($table_name);
            if($migration_db_table->tableExists(false, false)) {
                $migration_db_table->exportDatabase(true);
            }
        }
    }

    /*
     * Run the migration
     *
     * @throws Exception
     */
    public function migrateDatabase() {
        foreach(self::TABLES as $table_name) {
            $migration_db_table = new Siberian_Migration_Db_Table($table_name);
            if($migration_db_table->tableExists()) {
                $migration_db_table->updateTable();
            }
        }
    }

    /*
     * Run the migration
     *
     * @throws Exception
     */
    public function createDatabase() {
        foreach(self::TABLES as $table_name) {
            $migration_db_table = new Siberian_Migration_Db_Table($table_name);
            //$migration_db_table->exportDatabase();
            $migration_db_table->createTable();
        }
    }

}