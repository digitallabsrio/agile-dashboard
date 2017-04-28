<?php

namespace RGies\MetricsBundle\Controller;

use RGies\MetricsBundle\Entity\Widgets;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RGies\MetricsBundle\Entity\Dashboard;
use RGies\MetricsBundle\Form\DashboardType;

/**
 * Dashboard controller.
 *
 * @Route("/dashboard")
 */
class DashboardController extends Controller
{

    /**
     * Lists all Dashboard entities.
     *
     * @Route("/", name="dashboard")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MetricsBundle:Dashboard')->findBy(
            array(),
            array('pos'=>'ASC')
        );

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Dashboard entity.
     *
     * @Route("/", name="dashboard_create")
     * @Method("POST")
     * @Template("MetricsBundle:Dashboard:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Dashboard();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('home', array('id'=>$entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Dashboard entity.
     *
     * @param Dashboard $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Dashboard $entity)
    {
        $form = $this->createForm(new DashboardType(), $entity, array(
            'action' => $this->generateUrl('dashboard_create'),
            'method' => 'POST',
            'attr'   => array('id' => 'create-form'),
        ));

        //$form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Dashboard entity.
     *
     * @Route("/new", name="dashboard_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Dashboard();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Dashboard entity.
     *
     * @Route("/{id}", name="dashboard_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:Dashboard')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Dashboard entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Dashboard entity.
     *
     * @Route("/{id}/edit", name="dashboard_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:Dashboard')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Dashboard entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Dashboard entity.
    *
    * @param Dashboard $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Dashboard $entity)
    {
        $form = $this->createForm(new DashboardType(), $entity, array(
            'action' => $this->generateUrl('dashboard_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'attr'   => array('id' => 'edit-form'),
        ));

        //$form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Dashboard entity.
     *
     * @Route("/{id}", name="dashboard_update")
     * @Method("PUT")
     * @Template("MetricsBundle:Dashboard:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetricsBundle:Dashboard')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Dashboard entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('dashboard'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Dashboard entity.
     *
     * @Route("/{id}", name="dashboard_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MetricsBundle:Dashboard')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Dashboard entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('dashboard'));
    }

    /**
     * Creates a form to delete a Dashboard entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(null, array('attr'=>array('id' => 'delete-form')))
            ->setAction($this->generateUrl('dashboard_delete', array('id' => $id)))
            ->setMethod('DELETE')
            //->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    /**
     * Generates data array.
     *
     * @param integer $id Dashbaord id
     * @return array
     */
    protected function _toArray($id)
    {
        $data = array();
        $em = $this->getDoctrine()->getManager();

        $dashboard = $em->getRepository('MetricsBundle:Dashboard')
            ->createQueryBuilder('d')
            ->where('d.id = :id')
            ->setParameter('id', $id)
            ->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        if (!$dashboard) {
            throw $this->createNotFoundException('Dashboard not found');
        }

        $data['dashboard'] = $dashboard[0];

        $widgets = $em->getRepository('MetricsBundle:Widgets')
            ->createQueryBuilder('w')
            ->where('w.dashboard = :id')
            ->setParameter('id', $id)
            ->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        $data['widgets'] = $widgets;

        $data['configs'] = array();
        foreach($widgets as $widget) {
            $data['configs'][$widget['id']] =
                $this->get('widgetService')->getWidgetConfig($widget['type'], $widget['id'], true)
            ;
        }

        return $data;
    }

    /**
     * Reorder dashboards by given id list.
     *
     * @Route("/reorderDashboards/", name="dashboard_reorder")
     * @Method("POST")
     * @return Response
     */
    public function reorderDashboardsAjaxAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
        {
            return new Response('No valid request', Response::HTTP_FORBIDDEN);
        }

        $response = array();
        $ids = $request->request->get('id_list', '');

        $list = array_flip(explode(',', trim($ids, ',')));

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('MetricsBundle:Dashboard')->findAll();

        foreach ($entities as $entity)
        {
            if (isset($list[$entity->getId()]))
            {
                $entity->setPos($list[$entity->getId()]);
                $em->persist($entity);
            }
            else
            {
                $entity->setPos(999);
                $em->persist($entity);
            }
        }
        $em->flush();

        return new Response(json_encode($response), Response::HTTP_OK);
    }

    /**
     * Export dashboard.
     *
     * @Route("/dashboard-export/{id}", name="dashboard_export")
     * @return Response
     */
    public function exportDashboardAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('MetricsBundle:Dashboard')->find($id);
        $filename = str_replace(array(' ','%','/'), '_', $entity->getTitle()) . '.json';

        $response = new Response();

        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename);

        $response->setContent(
            json_encode(
                $this->_toArray($id)
            )
        );

        return $response;
    }

    /**
     * Import dashboard.
     *
     * @Route("/dashboard-import/", name="dashboard_import")
     * @Template()
     */
    public function importDashboardAction(Request $request)
    {
        foreach ($request->files->get('file') as $file) {
            $filename = 'uploads/' . $file->getClientOriginalName();

            if (!move_uploaded_file($file->getPathname(), $filename)) {
                throw $this->createNotFoundException('Error on file upload');
            }

            if (substr($file->getClientOriginalName(), -5) != '.json') {
                throw $this->createNotFoundException('Only *.json files allowed');
            }

            //$title = basename($file->getClientOriginalName(), '.json');
            $import = json_decode(file_get_contents($filename));

            $this->_persistArray($import);

            @unlink($filename);
        }

        return $this->forward('MetricsBundle:Dashboard:index');
    }

    /**
     * Creates new dashboard database entry with given data.
     *
     * @param array $data Dashboard data
     */
    protected function _persistArray($data)
    {
        $em = $this->getDoctrine()->getManager();

        // persist dashboard
        $dashboard = new Dashboard($data->dashboard);
        $dashboard->setTitle($dashboard->getTitle() . '-new');
        $em->persist($dashboard);
        $em->flush();

        // persist widgets
        foreach ($data->widgets as $widget) {
            $id = $widget->id;
            $widget->dashboard = $dashboard;
            $widget = new Widgets($widget);
            $em->persist($widget);
            $em->flush();

            // persist config
            $config = $data->configs->$id;
            $config->widget_id = $widget->getId();
            $this->get('WidgetService')->setWidgetConfig($widget->getType(), $config);
        }
    }


}
