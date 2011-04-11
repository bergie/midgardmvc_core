<?php
/**
 * @package midgardmvc_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Event observation interface for Midgard2 
 *
 * @package midgardmvc_core
 */
class midgardmvc_core_services_observation_midgard2 implements midgardmvc_core_services_observation
{
    private $type_callbacks = array();
    private $connected_types = array();

    public function add_listener($callback, array $events, array $types = null, array $data = null)
    {
        if (!is_callable($callback))
        {
            throw new InvalidArgumentException("Callback is not valid");
        }

        if (is_null($types))
        {
            // Empty types means connecting to every type
            $types = $this->_core->dispatcher->get_mgdschema_classes();
        }
        
        foreach ($types as $type)
        {
            // Ensure we get the signal
            $this->connect_type($type, $events);
            
            // Register listener
            if (!isset($this->type_callbacks[$type]))
            {
                $this->type_callbacks[$type] = array();
            }
            
            foreach ($events as $event)
            {
                if (!isset($this->type_callbacks[$type][$event]))
                {
                    $this->type_callbacks[$type][$event] = array();
                }
                $this->type_callbacks[$type][$event][] = $callback;
            }
        }
    }
    
    public function trigger(midgard_object $object, $data)
    {
        if (   !isset($this->type_callbacks[$data['type']])
            || !isset($this->type_callbacks[$data['type']][$data['event']]))
        {
            return;
        }
        
        foreach ($this->type_callbacks[$data['type']] as $callback)
        {
            call_user_func($callback, $object, $data);
        }
    }
    
    private function prepare_data($type, $event, array $data = null)
    {
        if (!$data)
        {
            $data = array();
        }
        
        $data['type'] = $type;
        $data['event'] = $event;
        
        return $data;
    }
    
    private function connect_type($type, array $events, array $data = null)
    {
        if (!isset($this->connected_types[$type]))
        {
            $this->connected_types[$type] = array();
        }
        
        foreach ($events as $event)
        {
            if (isset($this->connected_types[$type][$event]))
            {
                // We already listen for this signal
                return;
            }
            midgard_object_class::connect_default
            (
                $type,
                $event,
                array
                (
                    $this,
                    'trigger',
                    $this->prepare_data($type, $event, $data)
                )
            );
        }
    }
    
    public function get_listeners()
    {
        return array();
    }
}