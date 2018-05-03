<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

class Wasa_Kredit_Checkout_Page
{
    public function __construct()
    {
        $this->create_checkout_page();

        // Hooks
        add_filter( 'page_template', array( $this, 'checkout_template_override' ) );
    }

    public function checkout_template_override($page_template)
    {
        // Always load our checkout page template on checkout page
        $checkout_page = get_page_by_title( 'Wasa Kredit Checkout' );

        if ( is_page( $checkout_page ) ) {
            $page_template =
                dirname(__FILE__) . '/../templates/checkout-page.php';
        }

        return $page_template;
    }

    public function create_checkout_page()
    {
        // This page is used with the template ./templates/checkout-page.php to give the
        // the ability to edit text above and the checkout
        if ( is_admin() ) {
            return;
        }

        if ( get_page_by_title( 'Wasa Kredit Checkout' ) == null ) {
            global $user_ID;

            $new_page = array(
                'post_title' => 'Wasa Kredit Checkout',
                'post_content' => 'Please fill out the forms in the Wasa Kredit Checkout and follow the wizard.',
                'post_status' => 'publish',
                'post_date' => date('Y-m-d H:i:s'),
                'post_author' => $user_ID,
                'post_type' => 'page'
            );

            $page_id = wp_insert_post( $new_page );
        }
    }
}
