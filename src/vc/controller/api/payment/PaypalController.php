<?php
namespace vc\controller\api\payment;

class PaypalController extends \vc\controller\api\AbstractApiController
{
    public function handleGet(\vc\controller\Request $request)
    {
    }

    public function handlePost(\vc\controller\Request $request)
    {
        $input = @file_get_contents("php://input");
        $inputJson = json_decode($input);
    }
}
