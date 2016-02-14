<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */
namespace Workflow\Plugins\FileActions;

class Download extends \Workflow\FileAction {

    public function getActions($moduleName) {

        $return = array(
            'id' => 'download',
            'title' => 'direct download File',
            'options' => array(
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
    public function doAction($configuration, $filepath, $filename, $context, $targetRecordIds = array()) {

        if(\Workflow2::$isAjax === true ) {
            $workflow = $this->getWorkflow();
            if(!empty($workflow)) {
                $id = md5(microtime(false).rand(10000, 99999));

                copy($filepath, vglobal('root_directory') . '/modules/Workflow2/tmp/download/'.$id);

                $workflow->setSuccessRedirection('index.php?module=Workflow2&action=DownloadFile&filename='.urlencode($filename).'&id='.$id);
                $workflow->setSuccessRedirectionTarget('new');
                return;
            }
        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"".$filename."\"");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".filesize($filepath));

        @readfile($filepath);
        exit();
    }

}

\Workflow\FileAction::register('download', '\Workflow\Plugins\FileActions\Download');