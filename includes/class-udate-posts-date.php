<?php
/**
 * No Direct Access
 */
defined( 'ABSPATH' ) or die;

/**
 * Schedule post dates update
 */
class UdatePostsDate
{
    /**
     * Get errors html.
     *
     * @since  1.0.0
     *
     * @param  int    $category   Category ID.
     * @param  int    $increment  Increment date timestamp.
     * @param  string $date_type  Post's date type.
     *
     * @return void
     */
    public static function increment( $category, $increment, $date_type ) {
        $posts = get_posts( array(
            'numberposts' => '-1',
            'category'    => $category,
        ));

        foreach( $posts as $post_item ) {
            $date_args = array(
                'ID' => $post_item->ID,
            );

            // Set Date.
            switch ( $date_type ) {
                case 'published':
                    $date_args['post_date']     = self::new_date( $post_item, 'post_date', $increment );
                    $date_args['post_date_gmt'] = self::new_date( $post_item, 'post_date_gmt', $increment );
                    break;

                case 'updated':
                    $date_args['post_modified']     = self::new_date( $post_item, 'post_modified', $increment );
                    $date_args['post_modified_gmt'] = self::new_date( $post_item, 'post_modified_gmt', $increment );
                    break;

                case 'both':
                    $date_args['post_date']     = self::new_date( $post_item, 'post_date', $increment );
                    $date_args['post_date_gmt'] = self::new_date( $post_item, 'post_date_gmt', $increment );
                    $date_args['post_modified']     = self::new_date( $post_item, 'post_modified', $increment );
                    $date_args['post_modified_gmt'] = self::new_date( $post_item, 'post_modified_gmt', $increment );
                    break;
            }

            // Update Post Date.
            wp_update_post( wp_slash( $date_args ) );
        }
    }

    /**
     * Increment date.
     *
     * @since  1.0.0
     *
     * @param array   $post_item  Post's Data.
     * @param string  $field      What field to increment.
     * @param int     $increment  Increment seconds.
     *
     * @return string             Date in mysql format.
     */
    private static function new_date( $post_item, $field, $increment ) {
        $post_item = (array) $post_item;

        if ( ! isset( $post_item[ $field ] ) ) {
            return;
        }

        if ( strtotime( $post_item[ $field ] ) ) {
            $new_timestamp = strtotime( $post_item[ $field ] ) + $increment;

            return date( 'Y-m-d H:i:s', $new_timestamp );
        }

        return $post_item[ $field ];
    }
}