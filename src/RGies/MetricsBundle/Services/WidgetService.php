<?php
/**
 * Created by PhpStorm.
 * User: rgies
 * Date: 04.05.17
 * Time: 16:11
 */

namespace RGies\MetricsBundle\Services;

use RGies\MetricsBundle\Entity\Widgets;
use RGies\MetricsBundle\Objects\WidgetConfig;

/**
 * Class WidgetService.
 *
 * @package RGies\MetricsBundle\Services
 */
class WidgetService
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $_doctrine;

    /**
     * @var array Array of registered widget types
     */
    private $_widgetTypes;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $_serviceContainer;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    protected $_session;

    /**
     * Class constructor.
     */
    public function __construct($doctrine, $widgetTypes, $serviceContainer, $session)
    {
        $this->_doctrine            = $doctrine;
        $this->_widgetTypes         = $widgetTypes;
        $this->_serviceContainer    = $serviceContainer;
        $this->_session = $session;
    }

    /**
     * Get action name to widget configuration.
     *
     * @param $widgetType string Widget type (bundle name)
     * @return string
     */
    public function getWidgetEditActionName($widgetType)
    {
        $widgetService = $this->_loadPluginService($widgetType);
        return $widgetService->getWidgetEditActionName($widgetType);
    }

    /**
     * Get configuration by given widget id.
     *
     * @param string $widgetType Type name of the widget
     * @param integer $widgetId OPTIONAL ID of the widget
     * @param boolean $toArray OPTIONAL True for result type array
     * @return object | array | null
     */
    public function getWidgetConfig($widgetType, $widgetId = null, $toArray = false)
    {
        $widgetService = $this->_loadPluginService($widgetType);
        return $widgetService->getWidgetConfig($widgetType, $widgetId, $toArray);
    }

    /**
     * Get configuration with resolved placeholder by given widget id.
     *
     * @param string $widgetType Type name of the widget
     * @param integer $widgetId ID of the widget
     * @return object | array | null
     */
    public function getResolvedWidgetConfig($widgetType, $widgetId)
    {
        $widgetService = $this->_loadPluginService($widgetType);

        return new WidgetConfig(
            $widgetId,
            $widgetService->getWidgetConfig($widgetType, $widgetId, false),
            $this->_doctrine
        );
    }

    /**
     * Set configuration by given widget id.
     *
     * @param string $widgetType Type name of the widget
     * @param array $data Config value
     */
    public function setWidgetConfig($widgetType, $data)
    {
        $widgetService = $this->_loadPluginService($widgetType);
        $widgetService->setWidgetConfig($widgetType, (array)$data);
    }

    /**
     * Deletes configuration by given widget id.
     *
     * @param string $widgetType Type name of the widget
     * @param $widgetId
     */
    public function deleteWidgetConfig($widgetType, $widgetId)
    {
        $widgetService = $this->_loadPluginService($widgetType);
        $widgetService->deleteWidgetConfig($widgetId, $widgetType);
    }

    /**
     * Gets path to include the widget at the dashboard.
     *
     * @param string $widgetType Type name of the widget
     * @return string
     */
    public function getWidgetIncludePath($widgetType)
    {
        $widgetService = $this->_loadPluginService($widgetType);
        return $widgetService->getWidgetIncludePath($widgetType);
    }

    /**
     * Loads the required service from given plugin type.
     *
     * @param string $widgetType Type name of the widget
     * @return object
     */
    protected function _loadPluginService($widgetType)
    {
        $list = explode('/', $widgetType, 2);
        $bundle = $list[0];
        $path = $this->_serviceContainer->get('kernel')->locateResource('@' . $bundle . '/Services');

        // load bundle plugin service
        require_once ($path . '/WidgetPluginService.php');
        $service = $this->_serviceContainer->get($bundle . 'Service');

        return $service;
    }

    /**
     * Gets the widget size configuration.
     *
     * @param $widgetType
     * @return array
     */
    public function getWidgetSizes($widgetType)
    {
        if ($conf = $this->_serviceContainer->getParameter($widgetType . 'Config'))
        {
            if (isset($conf['widget_sizes'])) {
                return explode(',', $conf['widget_sizes']);
            };
        }

        return array('1x1');
    }

    /**
     * Creates new widget database entry with given data.
     *
     * @param array $data Widget data
     * @param \RGies\MetricsBundle\Entity\Dashboard $dashboard Target dashboard
     * @return Widgets
     */
    public function import($data, $dashboard)
    {
        $em = $this->_doctrine->getManager();
        $widget = null;

        try {
            $widget = new Widgets($data['widget']);
            $widget->setDashboard($dashboard);
            $em->persist($widget);
            $em->flush();

            // persist config
            $config = $data['config'];
            $config['widget_id'] = $widget->getId();
            $this->setWidgetConfig($widget->getType(), $config);
        } catch(\Exception $e) {
            $this->_session->getFlashBag()->add('error', ' Widget could not be created [' . $e->getMessage() . '].');
        }

        return $widget;
    }

    /**
     * Generates data array from given widget id.
     *
     * @param integer $id Widget id
     * @return array
     * @throws \Exception
     */
    public function export($id)
    {
        $em = $this->_doctrine->getManager();

        $result = $em->getRepository('MetricsBundle:Widgets')
            ->createQueryBuilder('w')
            ->where('w.id = :id')
            ->setParameter('id', $id)
            ->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        if (!$result) {
            throw new \Exception('Widget with id ['. $id .'] not found');
        }

        $data = array();
        $data['widget'] = $widget = $result[0];
        $data['config'] = $this->getWidgetConfig($widget['type'], $widget['id'], true);

        return $data;
    }
}