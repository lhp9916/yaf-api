<?php

class IpController extends Yaf_Controller_Abstract
{

    public function getAction()
    {
        $ip = $this->getRequest()->getQuery('ip', '');
        if (!$ip || !filter_var($ip, FILTER_VALIDATE_IP)) {
            echo json_encode([
                'errno' => -5001,
                'errmsg' => '请传递正确的IP地址'
            ]);
            return false;
        }

        $model = new IpModel();
        if ($data = $model->get(trim($ip))) {
            echo json_encode([
                'errno' => 0,
                'errmsg' => "",
                'data' => $data
            ]);
        } else {
            echo json_encode([
                'errno' => $model->errno,
                'errmsg' => $model->errmsg,
            ]);
        }
        return false;
    }
}
