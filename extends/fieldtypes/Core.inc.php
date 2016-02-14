<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 08.08.14 22:02
 * You must not use this file without permission.
 */
namespace Workflow\Plugins\Fieldtypes;

class Core extends \Workflow\Fieldtype
{
    public function getFieldTypes($moduleName) {
        $fields = array();

        $fields[] = array(
            'id' => 'text',
            'title' => 'Text',
            'config' => array(
                'default' => array(
                    'type' => 'templatefield',
                    'label' => 'Default'
                )
            )
        );
        $fields[] = array(
            'id' => 'textarea',
            'title' => 'Textarea',
            'config' => array(
                'default' => array(
                    'type' => 'templatefield',
                    'label' => 'Default'
                )
            )
        );
        $fields[] = array(
            'id' => 'checkbox',
            'title' => 'Checkbox',
            'config' => array(
                'default' => array(
                    'type' => 'checkbox',
                    'label' => 'Default',
                    'value' => 'On'
                )
            )
        );
        $fields[] = array(
            'id' => 'picklist',
            'title' => 'Picklist',
            'config' => array(
                'default' => array(
                    'type' => 'templatearea',
                    'label' => 'Options'
                )
            )
        );
        $fields[] = array(
            'id' => 'date',
            'title' => 'Date',
            'config' => array(
                'default' => array(
                    'type' => 'templatefield',
                    'label' => 'Default'
                )
            )
        );
        $fields[] = array(
            'id' => 'file',
            'title' => 'Fileupload',
            'config' => array(
                'default' => array(
                    'type' => 'templatefield',
                    'label' => 'FileStoreID'
                ),
            )
        );

        return $fields;
    }

    public function renderFrontend($data, $context) {
        if(!empty($data['config']['default'])) {
            $data['config']['default'] = \Workflow\VTTemplate::parse($data['config']['default'], $context);
        }

        $html = '';
        $script = '';

        switch($data['type']) {
            case 'file':
                $field = '<input type="file" id="reqfield_' . $data['name'] . '" data-filestoreid="' . $data['config']['default'] . '" style="width:400px;" name="' . $data['name'] . '" value="' . $data['config']["default"] . '">';
                break;
            case 'checkbox':
                $field = '<input type="checkbox" name="' . $data['name'] . '" ' . ($data["config"]["default"] == 'On'?"checked='checked'":"") . ' value="on">';
                break;
            case 'textarea':
                $field = '<textarea id="reqfield_' . $data['name'] . '" style="width:400px;height:100px;" name="' . $data['name'] . '">' . $data['config']["default"] . '</textarea>';
                break;
            case 'picklist':
                $options = explode("\n", $data['config']['default']);
                $field = '<select style="width:410px;" name="' . $data['name'] . '" class="select2">';
                foreach($options as $option) {
                    $option = trim($option);
                    if(strpos($option, '#~#') !== false) {
                        $parts = explode('#~#',$option);
                        $fieldValue = $parts[1];
                        $fieldLabel = $parts[0];

                    } else {
                        $fieldValue = $option;
                        $fieldLabel = $option;
                    }

                    $field .= '<option value="'.$fieldValue.'">'.$fieldLabel.'</option>';
                }
                $field .= '</select>';
                break;
            case 'date':
                $current_user = \Users_Record_Model::getCurrentUserModel();
                $field = '<div class="input-append pull-right" style="width:410px;">';
                if(!empty($data['config']["default"])) {
                    $preset = \DateTimeField::convertToUserFormat($data['config']["default"]);
                } else {
                    $preset = '';
                }
                $field .= '<input type="text" class="dateField span2" data-date-format="'.$current_user->date_format.'"id="reqfield_' . $data['name'] . '"name="' . $data['name'] . '" value="' . $preset . '">';
                $field .= '<span class="add-on"><i class="icon-calendar"></i></span>';
                $field .= '</div>';
                break;
            case 'text':
            default:
                $field = '<input type="text" id="reqfield_' . $data['name'] . '" style="width:400px;" name="' . $data['name'] . '" value="' . $data['config']["default"] . '">';
                break;
        }

        $html = "<label><div style='min-height:26px;padding:2px 0;'><div style=''><strong>".$data['label']."</strong></div><div style='text-align:right;'>".$field."</div></div></label>";

        return array('html' => $html, 'javascript' => $script);
    }

    /**
     * @param $value
     * @param $name
     * @param $type
     * @param \Workflow\VTEntity $context
     * @return \type
     */
    public function getValue($value, $name, $type, $context, $allValues) {
        if($type == 'date') {
            $value = \DateTimeField::convertToDBFormat($value);
        }


        if($type == 'file') {
//            var_dump($value, $name, $type, $_FILES, $this);
            $context->addTempFile($_FILES['fileUpload']['tmp_name'][$name], $value, $_FILES['fileUpload']['name'][$name]);
            return '1';
        }
        return $value;
    }
}

\Workflow\Fieldtype::register('core', '\Workflow\Plugins\Fieldtypes\Core');