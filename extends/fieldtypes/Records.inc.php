<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 08.08.14 22:02
 * You must not use this file without permission.
 */
namespace Workflow\Plugins\Fieldtypes;

class Records extends \Workflow\Fieldtype
{
    public function getFieldTypes($moduleName) {
        $fields = array();

        $modules= \Workflow\VtUtils::getEntityModules();

        $relmodules = array(
            '' => getTranslatedString('module of records', 'Workflow2'),
        );
        foreach($modules as $mod) {
            $relmodules[$mod[0]] = vtranslate($mod[1], $mod[0]);
        }

        $fields[] = array(
            'id' => 'records',
            'title' => 'select Record',
            'config' => array(
                'module' => array(
                    'type' => 'picklist',
                    'label' => 'Records from module',
                    'options' => $relmodules,
                    'nomodify' => true,
                ),
                'condition' => array(
                    'type' => 'condition',
                    'moduleField' => 'module',
                    'label' => 'Search possible Records',
                ),
            )
        );

        return $fields;
    }


    /**
     * @param $data     - Config Array of this Input with the following Structure
     *                      array(
     *                          'label' => 'Label the Function should use',
     *                          'name' => 'The Fieldname, which should submit the value, the Workflow will be write to Environment',
     *                          'config' => Key-Value Array with all configurations, done by admin
     *                      )
     * @param \Workflow\VTEntity $context - Current Record, which is assigned to the Workflow
     * @return array - The rendered content, shown to the user with the following structure
     *                  array(
     *                      'html' => '<htmlContentOfThisInputField>',
     *                      'javascript' => 'A Javascript executed after html is shown'
     *                  )
     *
     */
    public function renderFrontend($data, $context) {
        $relmod = $data['config']['module'];

        if(empty($data['config']['condition'])) {
            echo 'please configure condition!';
            return;
        }
        $adb = \PearDatabase::getInstance();

        $conditions = \Zend_Json::decode(base64_decode($data['config']['condition']));

        $logger = new \Workflow\ConditionLogger();

        $objMySQL = new \Workflow\ConditionMysql($relmod, $context);
        $objMySQL->setLogger($logger);

        $main_module = \CRMEntity::getInstance($relmod);

        $sqlCondition = $objMySQL->parse($conditions['condition']);

        if(strlen($sqlCondition) > 3) {
            $sqlCondition .= "AND vtiger_crmentity.deleted = 0";
        } else {
            $sqlCondition .= "vtiger_crmentity.deleted = 0";
        }

        $logs = $logger->getLogs();
        //$this->setStat($logs);

        $sqlTables = $objMySQL->generateTables();
        $idColumn = $main_module->table_name.".".$main_module->table_index;
        $sqlQuery = "SELECT $idColumn as idcol ".$sqlTables." WHERE ".(strlen($sqlCondition) > 3?$sqlCondition:"").' GROUP BY vtiger_crmentity.crmid';

        //$this->addStat("MySQL Query: ".$sqlQuery);

        $result = $adb->query($sqlQuery);
        $ids = array();
        while($row = $adb->fetchByAssoc($result)) {
            $ids[] = $row['idcol'];
        }

        $mainData = \Workflow\VtUtils::getMainRecordData($relmod, $ids);
        uasort($mainData, function ($a, $b) {
            return strcmp($a["number"], $b["number"]);
        });

        $html = '';
        $script = '';

        $fieldId = 'field_'.preg_replace('/[^a-zA-Z0-9_]/','_', $data['name']);

        $field = '<select style="width:410px;" name="' . $data['name'] . '" id="' . $fieldId . '" class="select2" data-placeholder="'.vtranslate('choose Reference','Workflow2').'">';

        if(!empty($data['config']['nullable'])) {
            $field .= '<option value="" selected="selected"><em>- '.vtranslate('no Selection','Workflow2').'</em></option>';
        }

        if(count($mainData) > 0) {
            foreach($mainData as $crmid => $record) {
                $field .= '<option value="'.$crmid.'" data-url="'.$record['link'].'">['.$record['number'].'] '.$record['label'].'</option>';
            }
        }
        $field .= '</select>';

        $html = "<label><div style='min-height:26px;padding:2px 0;'><div style=''><strong>".$data['label']."</strong></div><div style='text-align:right;'>".$field."<div style='display:none;margin-top:5px;' id='url_".$data['name']."'></div></div></div></label>";

        $script = '';
        if(!empty($data['config']['nullable'])) {
            $script .= 'jQuery("#' . $fieldId . '").select2("val", "");';
        }
        $script .= 'jQuery("#' . $fieldId . '").on("change", function(e) {var selected = jQuery("#' . $fieldId . ' option:selected"); if(selected.val() == "") { jQuery("#url_' . $data['name'] . '").html("");return;}; jQuery("#url_' . $data['name'] . '").show().html("Link: <a href=\'" + selected.data("url") + "\' target=\'_blank\'><strong>" + selected.text() + "</strong></a>");
         });';
        return array('html' => $html, 'javascript' => $script);
    }
}

// The class neeeds to be registered
\Workflow\Fieldtype::register('records', '\Workflow\Plugins\Fieldtypes\Records');