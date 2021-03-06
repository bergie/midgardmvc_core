<?php
/**
 * @package midgardmvc_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Base class for object management controller. Extend this to easily implement the regular Create, Read, Update and Delete cycle
 *
 * @package midgardmvc_core
 */
abstract class midgardmvc_core_controllers_baseclasses_crud
{
    /**
     * The actual MgdSchema object to be managed by the controller.
     */
    protected $object = null;
    
    /**
     * Midgard MVC Forms instance
     */
    protected $form = null;

    public function __construct(midgardmvc_core_request $request)
    {
        $this->request = $request;
    }

    /**
     * Method for loading the object to be managed. To be overridden in the actual controller.
     */
    abstract public function load_object(array $args);
    
    /**
     * Method for preparing a new object to be created. To be overridden in the actual controller.
     */
    abstract public function prepare_new_object(array $args);
    
    /**
     * Method for generating route to the object
     *
     * @return string Object URL
     */
    abstract public function get_url_read();

    /**
     * Method for generating route to editing the object
     *
     * @return string Object URL
     */    
    abstract public function get_url_update();
    
    public function load_form()
    {
        $this->form = midgardmvc_helper_forms_mgdschema::create($this->object);
    }

    public function relocate_to_read()
    {
        midgardmvc_core::get_instance()->head->relocate($this->get_url_read());
    }

    public function process_form()
    {
        $this->data['form']->process_post();
        midgardmvc_helper_forms_mgdschema::form_to_object($this->data['form'], $this->object);
    }

    // TODO: Refactor. There is code duplication with edit
    public function get_create(array $args)
    { 
        $node = $this->request->get_node();
        if ($node instanceof midgardmvc_core_providers_hierarchy_node_midgard2)
        {
            // If we have a Midgard node we can assign that as a "default parent"
            $this->data['parent'] = $node->get_object();
        }

        // Prepare the new object that form will eventually create
        $this->prepare_new_object($args);
        $this->data['object'] =& $this->object;

        if (isset($this->data['parent']))
        {
            midgardmvc_core::get_instance()->authorization->require_do('midgard:create', $this->data['parent']);
        }

        $this->load_form();
        $this->data['form'] =& $this->form;
    }

    public function post_create(array $args)
    {
        $this->get_create($args);
        try
        {
            $transaction = new midgard_transaction();
            $transaction->begin();
            $this->process_form();
            $this->object->create();
            $transaction->commit();
            
            // TODO: add uimessage of $e->getMessage();
            $this->relocate_to_read();
        }
        catch (midgardmvc_helper_forms_exception_validation $e)
        {
            // TODO: UImessage
        }
    }

    public function get_read(array $args)
    {
        $this->load_object($args);
        $this->data['object'] =& $this->object;
        $this->data['type'] = get_class($this->object);
        
        if (   $this->data['object'] instanceof midgard_dbobject
            && midgardmvc_core::get_instance()->authorization->can_do('midgard:update', $this->data['object']))
        {
            midgardmvc_core::get_instance()->head->add_link
            (
                array
                (
                    'rel' => 'edit',
                    'type' => 'application/x-wiki',
                    'title' => 'Edit this page!', // TODO: l10n and object type
                    'href' => $this->get_url_update(),
                )
            );
        }
    }

    public function get_update(array $args)
    {
        $this->load_object($args);
        $this->data['object'] =& $this->object;
        midgardmvc_core::get_instance()->authorization->require_do('midgard:update', $this->object);
        
        $this->load_form();
        $this->data['form'] =& $this->form;
    }

    public function post_update(array $args)
    {
        $this->get_update($args);

        try
        {
            $transaction = new midgard_transaction();
            $transaction->begin();
            $this->process_form();
            $this->object->update();
            $transaction->commit();

            // FIXME: We can remove this once signals work again
            midgardmvc_core::get_instance()->cache->invalidate(array($this->object->guid));

            // TODO: add uimessage of $e->getMessage();
            $this->relocate_to_read();
        }
        catch (midgardmvc_helper_forms_exception_validation $e)
        {
            // TODO: UImessage
        }
    }
        
    public function get_delete(array $args)
    {
        $this->load_object($args);
        $this->data['object'] =& $this->object;
        
        // Make a read-only form for display purposes
        $this->load_form();
        $this->form->set_readonly(true);
        $this->data['form'] =& $this->form;
        
        midgardmvc_core::get_instance()->authorization->require_do('midgard:delete', $this->object);
    }
    
    public function post_delete(array $args)
    {
        $this->get_delete($args);

        if (isset($_POST['delete']))
        {
            $transaction = new midgard_transaction();
            $transaction->begin();
            $this->object->delete();
            $transaction->commit();

            // FIXME: We can remove this once signals are used for this
            midgardmvc_core::get_instance()->cache->invalidate(array($this->object->guid));
            midgardmvc_core::get_instance()->head->relocate(midgardmvc_core::get_instance()->context->get_request()->get_prefix());
            // TODO: This needs a better redirect 
        }
    }
}
?>
