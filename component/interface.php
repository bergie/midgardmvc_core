<?php
/**
 * @package midgardmvc_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Component interface definition for MidCOM 3
 *
 * The defines the structure of component instance interface class
 *
 * @package midgardmvc_core
 */
interface midgardmvc_core_component_interface
{
    public function __construct(midgardmvc_core_services_configuration $configuration, midgard_page $folder = null);
    
    public function initialize();
    
    public function on_initialize();
}
?>