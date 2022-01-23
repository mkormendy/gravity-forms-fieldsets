<?php

/**
 * Plugin Name: Gravity Forms Fieldsets
 * Description: Gravity Forms Fieldsets Add-On for grouping fields inside an HTML5 fieldset. Adapted from <a href="https://wordpress.org/plugins/gravity-forms-fieldsets/" target="_blank">Gravity Fieldset for Gravity Forms</a> by Bas van den Wijngaard & Harro Heijboer.
 * Author: Mike Kormendy
 * Author URI: https://mikekormendy.com
 * Version: 0.3.5
 * Text Domain: gravity-forms-fieldsets
 * Domain Path: /languages
 * License: GPL2 v2
 */


if (! defined('ABSPATH')) die();

/**
 * Load translations
 */
function gravity_forms_fieldsets_load_textdomain() {

  load_plugin_textdomain( 'gravity-forms-fieldsets', FALSE, basename( dirname( __FILE__ ) ) . '/languages' );

}

add_action( 'plugins_loaded', 'gravity_forms_fieldsets_load_textdomain', 1 );

add_action( 'admin_notices', array('Gravity_Forms_Fieldsets', 'admin_warnings' ), 20 );

/**
 * Gravity_Forms_Fieldsets class.
 */
if (!class_exists('Gravity_Forms_Fieldsets')) :

  class Gravity_Forms_Fieldsets {

    private static $name = 'Gravity Forms Fieldsets';
    private static $domain = 'gravity-forms-fieldsets';
    private static $version = '0.3.5';


    /**
     * Construct the plugin object
     */
    public function __construct() {
      // delays the registration until all plugins have been loaded, ensuring this plugin does not run before Gravity Forms is available
      add_action( 'plugins_loaded', array( &$this, 'register_actions' ) );
    }


    /*
     * Register plugin functions
     */
    function register_actions() {

      // register filter and action hooks
      if (self::is_gravityforms_installed()) {

        // BACKEND HOOKS

        // add new Fieldset tab and Fieldset Begin/End buttons to Gravity Forms
        add_filter( 'gform_add_field_buttons', array( &$this, 'fieldset_add_field' ) );

        // add input field for field title
        add_filter( 'gform_field_type_title' , array( &$this, 'fieldset_title' ), 10, 2 );

        add_action( 'gform_editor_js_set_default_values', array( &$this, 'set_defaults' ) );

        add_action( 'admin_enqueue_scripts', array( &$this, 'fieldset_include_assets' ) );
        add_filter( 'gform_noconflict_scripts', array( &$this, 'fieldset_register_safe_scripts' ) );
        add_filter( 'gform_noconflict_styles', array( &$this, 'fieldset_register_safe_styles' ) );

        // FRONTEND HOOKS

        add_action( 'wp_enqueue_scripts', array( &$this, 'fieldset_include_css' ) );
        
        add_action( 'gform_field_css_class', array( &$this, 'fieldset_custom_class' ), 10, 3 );
        add_filter( 'gform_field_content', array( &$this, 'fieldset_display_field' ), 10, 5 );

        // add filter for altering the fieldset container html
        add_filter( 'gform_field_container', array( &$this, 'filter_gform_field_container'), 10, 6 );
        add_filter( 'gform_field_content', array( &$this, 'filter_gform_field_remove_label'), 10, 6 );

        // add filter for altering the complete form HTML
        add_filter( 'gform_get_form_filter', array( &$this, 'filter_gform_cleanup_html' ), 10, 2 );

      }

    }


    /*
     * BACKEND FORM EDITOR: Check if GF is installed
     */
    private static function is_gravityforms_installed() {
      
      if ( !function_exists( 'is_plugin_active' ) || !function_exists( 'is_plugin_active_for_network' ) ) {
        require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
      }

      if (is_multisite()) {
        return (
          is_plugin_active_for_network( 'gravityforms/gravityforms.php' ) ||
          is_plugin_active( 'gravityforms/gravityforms.php' )
        );
      }
      else {
        return is_plugin_active( 'gravityforms/gravityforms.php' );
      }

    }


    /*
     * BACKEND FORM EDITOR: Warning message if Gravity Forms is installed and enabled
     */
    public static function admin_warnings() {

      if ( !self::is_gravityforms_installed() ) {
        $message = __('requires Gravity Forms to be installed.', self::$domain);
      }

      if ( empty( $message ) ) return;

      ?>
      <div class="error">
        <h3>Warning</h3>
        <p><?php _e('The plugin', self::$domain); ?> <strong><?php echo self::$name; ?></strong> <?php echo $message; ?><br /><?php _e('Please', self::$domain); ?> <a target="_blank" href="http://www.gravityforms.com/"><?php _e('download the latest version', self::$domain); ?></a> <?php _e('of Gravity Forms and try again.', self::$domain); ?></p>
      </div>
      <?php

    }


    /**
     * BACKEND FORM EDITOR: Create a new fields group in the Gravity Forms forms editor and add our fieldset 'fields' to it
     */
    public static function fieldset_add_field( $field_groups ) {

      $fieldset_begin_field = array(

        'label'         => 'Fieldset Begin',
        'id'            => 'fieldset_begin',
        'class'         => 'button',
        'value'         => __( 'Fieldset Begin', self::$domain ),
        'data-type'     => 'FieldsetBegin',
        'onclick'       => 'StartAddField( \'FieldsetBegin\' );',
        'data-icon'     => 'gform-icon--excerpt'

      );

      $fieldset_end_field = array(

        'label'         => 'Fieldset End',
        'id'            => 'fieldset_end',
        'class'         => 'button',
        'value'         => __( 'Fieldset End', self::$domain ),
        'data-type'     => 'FieldsetEnd',
        'onclick'       => 'StartAddField( \'FieldsetEnd\' );',
        'data-icon'     => 'gform-icon--excerpt'

      );

      foreach ( $field_groups as &$group ) {

        $fieldset_fields_active = false;

        if ( $group["name"] === "fieldset_fields" ) {

          $fieldset_fields_active = true;

          $group["fields"][] = $fieldset_begin_field;
          $group["fields"][] = $fieldset_end_field;

        }

      }


      if ( !$fieldset_fields_active ) {

        $field_groups[] = array(

          'name'    => 'fieldset_fields',
          'label'   => __( 'Fieldsets', self::$domain ),
          'fields'  => array( $fieldset_begin_field, $fieldset_end_field )

        );

      }

      return $field_groups;

    }


    /**
     * BACKEND FORM EDITOR: Add title to fieldset, displayed in Gravity Forms forms editor
     */
    public static function fieldset_title( $title, $field_type ) {

      if ( $field_type === "FieldsetBegin" ) :

        return __( 'Fieldset Begin', self::$domain );

      elseif ( $field_type === "FieldsetEnd" ) :

        return __( 'Fieldset End', self::$domain );

      else :

        return __( 'Unknown', self::$domain );

      endif;

    }

    
    /**
     * Change the default labels for the inserted fieldset fields
     */
    public static function set_defaults() {
      
      // this hook is fired in the middle of a javascript switch statement so we need to add cases for our new field types
      ?>
      case "FieldsetBegin" :
        field.label = "Fieldset Begin";
        break;
      case "FieldsetEnd" :
        field.label = "Fieldset End";
        break;
      <?php

    }


  /**
   * Enqueues CSS Styling and JavaScript code for fieldset fields
   * This is also required to register the styles and scripts the Gravity Forms editor in the backend admin
   */
  public static function fieldset_include_css() {

    wp_register_style( 'gravity_forms_fieldsets_style', plugins_url( '/css/gravity_forms_fieldsets.css', __FILE__ ), array(), self::$version, 'all' );
    wp_enqueue_style( 'gravity_forms_fieldsets_style' );

  }

    /**
     * BACKEND FORM EDITOR: Enqueues CSS Styling and JavaScript code for fieldset fields in the Gravity forms editor
     */
    public static function fieldset_include_assets() {

      // styling for fieldset fields in the Gravity forms editor
      wp_register_style( 'gravity_forms_fieldsets_admin_style', plugins_url( '/css/gravity_forms_fieldsets_admin.css', __FILE__ ), array(), self::$version, 'all' );
      wp_enqueue_style( 'gravity_forms_fieldsets_admin_style' );

      // scripting to handle fieldset fields in the Gravity forms editor
      wp_register_script( 'gravity_forms_fieldsets_admin_script', plugins_url( '/js/gravity_forms_fieldsets_admin.js', __FILE__ ), array('jquery'), self::$version, true );
      wp_enqueue_script( 'gravity_forms_fieldsets_admin_script' );

    }

    /**
     * BACKEND: This is required to bypass the No Conflict and Output Default CSS modes in the site-wide Gravity Forms General Settings, allowing our styles/scripts to load regardless
     */
    public static function fieldset_register_safe_styles( $styles ) {
      $styles[] = 'gravity_forms_fieldsets_admin_style';
      return $styles;
    }
    public static function fieldset_register_safe_scripts( $scripts ) {
      $scripts[] = 'gravity_forms_fieldsets_admin_script';
      return $scripts;
    }


    /**
     * Get the appropriate CSS Grid class for the column span of the field.
     *
     * @since 2.5
     *
     * @return string
     */
    public static function get_css_grid_class( $layoutGridColumnSpan ) {

      switch ( $layoutGridColumnSpan ) {
        case 12:
          $class = 'gfield--width-full';
          break;
        case 11:
          $class = 'gfield--width-eleven-twelfths';
          break;
        case 10:
          $class = 'gfield--width-five-sixths';
          break;
        case 9:
          $class = 'gfield--width-three-quarter';
          break;
        case 8:
          $class = 'gfield--width-two-thirds';
          break;
        case 7:
          $class = 'gfield--width-seven-twelfths';
          break;
        case 6:
          $class = 'gfield--width-half';
          break;
        case 5:
          $class = 'gfield--width-five-twelfths';
          break;
        case 4:
          $class = 'gfield--width-third';
          break;
        case 3:
          $class = 'gfield--width-quarter';
          break;
        default:
          $class = '';
          break;
      }

      return $class;
    }


    /**
     * FRONTEND: Add custom classes to fieldset fields, controls CSS applied to field
     */
    public static function fieldset_custom_class($classes, $field, $form) {

      if ( self::get_css_grid_class( $field->layoutGridColumnSpan ) && !GFCommon::is_legacy_markup_enabled( $field->formId )) {
        $classes .= ' ' . self::get_css_grid_class( $field->layoutGridColumnSpan );
      }

      if ( $field['type'] === 'FieldsetBegin' ) :

        $classes .= ' gform_fieldset_begin gform_fieldset';

      elseif ($field['type'] === 'FieldsetEnd') :

        $classes .= ' gform_fieldset_end gform_fieldset';

      endif;

      return $classes;

    }


    /**
     * FRONTEND: Displays fieldset
     */
    public static function fieldset_display_field( $content, $field, $value, $lead_id, $form_id ) {

      if ( self::get_css_grid_class( $field->layoutGridColumnSpan ) && !GFCommon::is_legacy_markup_enabled( $field->formId )) {
        $classes = ' ' . self::get_css_grid_class( $field->layoutGridColumnSpan );
      }

      $custom_field_classes = $field->cssClass;

      if ( ( !is_admin() ) && ( $field['type'] == 'FieldsetBegin') ) :

        $content .= '<fieldset class="gfield gfieldset' . $classes . ' gform_fieldset_begin gform_fieldset '.$custom_field_classes.'">';

        if ( isset( $field['label'] ) && trim( $field['label'] ) !== '' ) :

          $hidden_legend = $field['labelPlacement'] == 'hidden_label' ? 'style="width:1px;height:1px;overflow:hidden;"' : '';

          $content .= '<legend class="gfieldset-legend"' . $hidden_legend . '>' . trim( $field['label'] ) . '</legend>';

        endif;

      elseif ( ( !is_admin() ) && ( $field['type'] == 'FieldsetEnd' ) ) :

        $content .= '</fieldset>';

      endif;

      return $content;

    }


    /*
     * FRONTEND: Alter container html when field type is fieldset
     */
    public static function filter_gform_field_container( $field_container, $field, $form, $css_class, $style, $field_content ) {

      if ( ( !is_admin() ) && ( $field->type === 'FieldsetBegin' || $field->type === 'FieldsetEnd' ) ) {
        // $ul_classes = GFCommon::get_ul_classes($form);        
        // $field_container = '</ul>{FIELD_CONTENT}<ul class="'.$ul_classes.'">';
        $field_container = '{FIELD_CONTENT}';
      }

      return $field_container;

    }


    /*
     * FRONTEND: Remove label tag when field type is fieldset
     */
    public static function filter_gform_field_remove_label( $field_content, $field, $value, $lead_id, $form_id ) {

      if ( ( !is_admin() ) && ( $field->type === 'FieldsetBegin' || $field->type === 'FieldsetEnd' ) ) {
        $field_content = preg_replace( '/<label[^>]*>([\s\S]*?)<\/label[^>]*>/', '', $field_content );
      }

      return $field_content;

    }


    /*
     * FRONTEND: Remove empty ul tag that is created when the fieldset close type is the last formfield.
     */
    public static function filter_gform_cleanup_html( $form_string, $form ) {

      if ( !is_admin() ) {
        $form_string = preg_replace( '#<(ul+)[^>]*>([[:space:]]|&nbsp;)*</ul>#', '', $form_string );
      }

      return $form_string;

    }

  }

  $Gravity_Forms_Fieldsets = new Gravity_Forms_Fieldsets();

endif;
