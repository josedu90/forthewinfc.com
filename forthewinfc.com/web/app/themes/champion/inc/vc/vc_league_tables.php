<?php

if (!defined('ABSPATH')) die('-1');

class VCLeagueTablesClass {
    function __construct() {
        // We safely integrate with VC with this hook
        add_action( 'init', array( $this, 'integrateWithVC' ) );
 
        // Use this when creating a shortcode addon
        add_shortcode( 'stm_lt', array( $this, 'render' ) );
    }
 
    public function integrateWithVC() {
        // Check if Visual Composer is installed
        if ( ! defined( 'WPB_VC_VERSION' ) ) {
            // Display notice that Visual Compser is required
            add_action('admin_notices', array( $this, 'showVcVersionNotice' ));
            return;
        }

        $tables = get_posts(array('posts_per_page' => 9999, 'post_type' => 'sp_table'));
        $tables_array = array('0' => __('Empty', 'champion'));
        if($tables){
            $tables_array = array();
            foreach($tables as $table){
                $tables_array[$table->post_title] = $table->ID;
            }
        }

        if(function_exists("vc_map")) {
            vc_map(array(
                "name" => __("League Tables", 'champion'),
                "description" => __("Place League Table", 'champion'),
                "base" => "stm_lt",
                "class" => "league_table",
                "controls" => "full",
                "icon" => 'vc_league_tables_icon',
                "category" => __('STM', 'champion'),
                'admin_enqueue_css' => array(get_template_directory_uri() . '/inc/vc/assets/css/vc_league_tables_admin.css'), // This will load css file in the VC backend editor
                "params" => array(
                    array(
                        "type" => "dropdown",
                        "class" => "",
                        "heading" => __("Select Table", 'champion'),
                        "param_name" => "id",
                        "value" => $tables_array
                    ),
                    array(
                        "type" => "textfield",
                        "class" => "",
                        "heading" => __("Count", 'champion'),
                        "param_name" => "count",
                        "value" => 5,
                        "min" => 1
                    )
                )
            ));
        }
    }
    
    /*
    Shortcode logic how it should be rendered
    */
    public function render( $atts, $content = null ) {

        extract( shortcode_atts( array(
            'id' => 0,
            'count' => 5
        ), $atts ) );

        $defaults = array(
            'id' => $id,
            'number' => -1,
            'columns' => null,
            'highlight' => null,
            'show_full_table_link' => false,
            'show_team_logo' => get_option( 'sportspress_table_show_logos', 'yes' ) == 'yes' ? true : false,
            'link_posts' => get_option( 'sportspress_link_teams', 'no' ) == 'yes' ? true : false,
            'sortable' => get_option( 'sportspress_enable_sortable_tables', 'yes' ) == 'yes' ? true : false,
            'scrollable' => get_option( 'sportspress_enable_scrollable_tables', 'yes' ) == 'yes' ? true : false,
            'responsive' => get_option( 'sportspress_enable_responsive_tables', 'yes' ) == 'yes' ? true : false,
            'paginated' => true,
            'rows' => $count,
        );

        extract( $defaults, EXTR_SKIP );

        if ( ! isset( $highlight ) ) $highlight = get_post_meta( $id, 'sp_highlight', true );

        $output = '<div class="vc_league_table sp-table-wrapper' . ( $scrollable ? ' sp-scrollable-table-wrapper' : '' ) . '">';

        $output .= '<table class="sp-league-table sp-data-table' . ( $responsive ? ' sp-responsive-table' : '' ) . ( $sortable ? ' sp-sortable-table' : '' ) . ( $paginated ? ' sp-paginated-table' : '' ) . '" data-sp-rows="' . $rows . '">' . '<thead>' . '<tr>';

        $table = new SP_League_Table( $id );

        $data = $table->data();

        // The first row should be column labels
        $labels = $data[0];

        // Remove the first row to leave us with the actual data
        unset( $data[0] );

        if ( ! $columns )
            $columns = get_post_meta( $id, 'sp_columns', true );

        if ( ! is_array( $columns ) )
            $columns = explode( ',', $columns );

        $output .= '<th class="data-rank">' . __( 'Pos', 'sportspress' ) . '</th>';

        foreach( $labels as $key => $label ):
            if ( ! is_array( $columns ) || $key == 'name' || in_array( $key, $columns ) )
                $output .= '<th class="data-' . esc_attr( $key ) . '">' . $label . '</th>';
        endforeach;

        $output .= '</tr>' . '</thead>' . '<tbody>';

        $i = 0;
        $start = 0;

        if ( intval( $number ) > 0 ):
            $limit = $number;

            // Trim table to center around highlighted team
            if ( $highlight && sizeof( $data ) > $limit && array_key_exists( $highlight, $data ) ):

                // Number of teams in the table
                $size = sizeof( $data );

                // Position of highlighted team in the table
                $key = array_search( $highlight, array_keys( $data ) );

                // Get starting position
                $start = $key - ceil( $limit / 2 ) + 1;
                if ( $start < 0 ) $start = 0;

                // Trim table using starting position
                $trimmed = array_slice( $data, $start, $limit, true );

                // Move starting position if we are too far down the table
                if ( sizeof( $trimmed ) < $limit && sizeof( $trimmed ) < $size ):
                    $offset = $limit - sizeof( $trimmed );
                    $start -= $offset;
                    if ( $start < 0 ) $start = 0;
                    $trimmed = array_slice( $data, $start, $limit, true );
                endif;

                // Replace data
                $data = $trimmed;
            endif;
        endif;

        // Loop through the teams
	    $r = 0;
        foreach ( $data as $team_id => $row ): $r++;

            if ( isset( $limit ) && $i >= $limit ) continue;

            $name = sp_array_value( $row, 'name', null );
            if ( ! $name ) continue;

            // Generate tags for highlighted team
            $before = $after = $class = '';
            if ( $highlight == $team_id ):
                $before = '<strong>';
                $after = '</strong>';
                $class = ' highlighted';
            endif;

	        if($r == 1 || $r == 2 || $r == 3){
		        $class .= ' red';
	        }

            $output .= '<tr class="' . esc_attr( ( $i % 2 == 0 ? 'odd' : 'even' ) . $class ) . '">';

            // Rank
            $output .= '<td class="data-rank">' . $before . ( $start + 1 ) . $after . '.</td>';

            $name_class = '';

	        $permalink = get_post_permalink( $team_id );
	        $name = '<a href="' . esc_url( $permalink ) . '">' . $name . '</a>';

            $output .= '<td class="data-name' . esc_attr( $name_class ) . '">' . $name . '</td>';

            foreach( $labels as $key => $value ):
                if ( $key == 'name' )
                    continue;
                if ( ! is_array( $columns ) || in_array( $key, $columns ) )
                    $output .= '<td class="data-' . esc_attr( $key ) . '">' . $before . sp_array_value( $row, $key, '&mdash;' ) . $after . '</td>';
            endforeach;

            $output .= '</tr>';

            $i++;
            $start++;

        endforeach;

        $output .= '</tbody>' . '</table>';

        $output .= '<a class="sp-league-table-link sp-view-all-link" href="' . esc_url( get_permalink( $id ) )  . '">' . __( 'view all', 'champion' ) . '</a>';

        $output .= '</div>';

        return $output;
    }

    /*
    Show notice if your plugin is activated but Visual Composer is not
    */
    public function showVcVersionNotice() {
        $plugin_data = get_plugin_data(__FILE__);
        echo '
        <div class="updated">
        <p>'.sprintf(__('<strong>%s</strong> requires <strong><a href="#" target="_blank">Visual Composer</a></strong> plugin to be installed and activated on your site.', 'vc_extend'), $plugin_data['Name']).'</p>
        </div>';
    }
}

if ( is_plugin_active( 'sportspress/sportspress.php' ) || is_plugin_active( 'sportspress-pro/sportspress-pro.php' ) && defined( 'WPB_VC_VERSION' ) ) {

    new VCLeagueTablesClass();

}