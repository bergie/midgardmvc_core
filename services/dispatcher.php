<?php
/**
 * @package midgardmvc_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Dispatcher for Midgard MVC
 *
 * Dispatcher is the heart of the component architecture. It is responsible for mapping requests to components
 * and their specific controllers and calling those.
 *
 * @package midgardmvc_core
 */
interface midgardmvc_core_services_dispatcher
{
    public function __construct();

    public function get_request();
    
    public function dispatch(midgardmvc_core_request $request);
    
    public function generate_url($route_id, array $args, $intent);
    
    public function get_midgard_connection();
    
    public function headers_sent();

    public function session_is_started();

    public function end_request();

    public function get_stdin();

    public function session_has_var($name);

    public function session_get_var($name);

    public function session_set_var($name, $value);
}
?>
