<?php
/*
Plugin Name: EP_Tools (Eros Pedrini Tools) - Plugins GUI
Plugin URI: http://www.contezero.net/sites/contezero/index.php/2008/12/08/plugins-gui/
Description: This plugin is the common GUI for manage EP_Tools.
Author: Eros Pedrini
Version: 1.3
Author URI: http://www.contezero.net/


Copyright 2008  Eros Pedrini  (email : contezero74@yahoo.it)


This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
require_once(dirname(__FILE__) . '/lib/wordpress_pre2.6.inc');

class ep_tools_plugins_gui {
    // public:
    function ep_tools_plugins_gui() { 
        $this->BasePluginsGUIDir = get_option('ep_tools_plugins_gui_dir');
        if(false == $this->BasePluginsGUIDir) {
            $this->BasePluginsGUIDir = dirname(__FILE__);
        }
        
        add_action('admin_menu', array(&$this, 'setupPluginsConfigPage') );
    }
    
    // private:
    var $BasePluginsGUIDir  = null; 
    
    function uninstallPlugin() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . "ept_registered_plugins";
        
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        
        if( false != get_option('ep_tools_plugins_gui_dir') ) {
            delete_option('ep_tools_plugins_gui_dir');
        }        
    }
    
    function setupPluginsConfigPage() {
        if ( function_exists('add_submenu_page') ) {
            add_submenu_page('edit.php', __('EP Tools Plugins GUI'), __('EP Tools Plugins GUI'), 'manage_options', 'ep_tools_config_page', array(&$this, 'showPluginsConfigPage') );
        } 
    }
    
    function showPluginsConfigPage() {
        $this->loadPluginsGUI();
                
        if ( isset($_POST['uninstallGUI']) ) {
            if ( function_exists('current_user_can') && !current_user_can('manage_options') ){
                die(__('Cheatin&#8217; uh?'));
            }
            
            $this->uninstallPlugin();
            
            echo '<div id="message" class="updated fade"><p><strong>';
                _e('EP Tools - Plugins GUI uninstalled successfully.');
            echo "</strong></p></div>\n";
        } else if ( isset($_POST['submit']) ) {
            if ( function_exists('current_user_can') && !current_user_can('manage_options') ){
                die(__('Cheatin&#8217; uh?'));
            }
            
            // save plugin config changes
            foreach ($this->PluginsGUI as $G) {
                $G->updateConfig();
            }
            
            if ( !empty($_POST) ) {
                echo '<div id="message" class="updated fade"><p><strong>';
                    _e('Options saved.');
                echo "</strong></p></div>\n";
            }
    	} else {
            // user asks to uninstall a plugin
            foreach ($this->PluginsGUI as $G) {
                if ( isset($_POST[ 'uninstall_' . $G->getName() ]) ) {
                    $G->uninstallPlugin();
                }
            }
        }
    	        
        // show plugins GUI
        // header
        echo "<div class=\"wrap\">\n";
        echo "\t<h2>";
            _e('EP Tools - Plugins GUI');
        echo "</h2>\n";
        echo "\t<form action=\"\" method=\"post\">\n";
        
        // plugins part
        if ( 0 == count($this->PluginsGUI) ) {
            echo "\t\t<p>No Plugin avvaiables</p>\n";
        } else {
            foreach ($this->PluginsGUI as $G) {
                echo "\t\t<h3>";
                    $G->printTitle();
                    
                    if ( method_exists($G, 'existsUninstallProcedure') && $G->existsUninstallProcedure() ) {               
                        echo ' - ';
                        echo "<input type=\"submit\" name=\"uninstall_" . $G->getName() . "\" value=\"";
                            _e('Uninstall &raquo;');
                        echo "\" />";
                    }
                echo "</h3>\n";
                
                if ( method_exists($G, 'checkAvailability') && !$G->checkAvailability() ) {
                    $G->printAvailabilityError();
                } else {
                    $G->printGUI();
                }
            }
            
            echo "\t\t<p class=\"submit\"><input type=\"submit\" name=\"submit\" value=\"";
                _e('Update Options &raquo;');
            echo "\" /></p>";
        }
        
        // uninstall part
        echo "\t\t<div class=\"submit\">\n";
        echo "\t\t\t<h3>Uninstall EP Tools - Plugins GUI</h3>\n";
        echo "\t\t\t<p>Deactivating <em>EP Tools - Plugins GUI</em> plugin does not remove " .
             'any data that may have been created. To completely remove this plugin, ' .
             "you can uninstall it here.</p>\n";
        echo "\t\t\t<p style=\"color: red;\"><strong>WARNING:</strong><br />" .
             'Once uninstalled, this cannot be undone. You should use a Database Backup ' .
             "plugin of WordPress to back up all the data first.</p>\n";

        echo "\t\t\t<p><input type=\"submit\" name=\"uninstallGUI\" value=\"";
            _e('Full Uninstall EP Tools - Plugins GUI &raquo;');
        echo "\" /></p>";
        echo "\t\t</div>\n";
        
        // footer        
        echo "\t</form>\n";     
        echo "</div>\n";
    }    
    
    function loadPluginsGUI() {
        global $wpdb;
        
        $this->PluginsGUI = array();
        
        $table_name = $wpdb->prefix . "ept_registered_plugins";
        
        $Plugin2Show = $wpdb->get_results("SELECT * FROM $table_name");
               
        foreach ($Plugin2Show as $key=>$value) {    	
        	if ( is_file($value->PluginDir) ) {  	   
        	   require_once($value->PluginDir);
        	   
        	   eval( '$this->PluginsGUI[] = new ' . $value->PluginName . '();' );
            }
        }
    }
    
    var $PluginsGUI = null;
}

/*
This function install the plugin, creating WordPress Options and WordPress
support Tables (for MySQL DBMS)
*/
function ept_gui_install() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . "ept_registered_plugins";
    
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    
    $sql = 'CREATE TABLE ' . $table_name . ' ( '.
                'PluginName text NOT NULL, ' .
                'PluginDir text NOT NULL, ' .
                'UNIQUE KEY PluginNameKey (PluginName(255))' .
            ');';
                
    maybe_create_table($table_name, $sql);
    
    if( false == get_option('ep_tools_plugins_gui_dir') ) {
        add_option( 'ep_tools_plugins_gui_dir', dirname(__FILE__) );
    }
}

register_activation_hook(__FILE__, 'ept_gui_install');

$ep_tools_PluginsGuiInstance = new ep_tools_plugins_gui();

?>
