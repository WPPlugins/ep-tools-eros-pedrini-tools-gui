<?php
/*
ep_tools_plugin_gui_class
coded by Eros Pedrini @2008

This is the interface class, that has to be derived and customized
to implement the GUI plugin enabled to work with
EP Tools - Plugins GUI


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

class ep_tools_plugin_gui_class {
    function getName() { return ''; }
    
    function printTitle() { ; }
    
    function printGUI() { ; }
    
    function updateConfig() { ; }
    
    function checkAvailability() { return true; }
    
    function printAvailabilityError() { ; }
    
    function existsUninstallProcedure() { return false; }
    
    function uninstallPlugin() { ; }
}

?>