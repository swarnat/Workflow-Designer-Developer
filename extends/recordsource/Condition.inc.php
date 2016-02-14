<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */
namespace Workflow\Plugins\RecordSource;

class Condition extends \Workflow\RecordSource {


    public function getSources($moduleName) {

        $return = array(
            'id' => 'condition',
            'title' => 'get Records by Condition',
            'options' => array(
                'condition' => array(
                    'type' => 'condition',
                    'label' => 'Define condition'
                )
            )
        );

        return $return;
    }

    /**
     * @param $key
     * @param $value
     * @param $context \Workflow\VTEntity
     * @return array|void
     */
    public function doAction($configuration, $moduleName, \Workflow\VTEntity $context) {

    }

    public function beforeGetTaskform($data) {

    }
}

\Workflow\RecordSource::register('condition', '\Workflow\Plugins\RecordSource\Condition');