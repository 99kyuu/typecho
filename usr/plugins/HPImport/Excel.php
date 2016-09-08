<?php

class HPImport_Excel extends Widget_Abstract_Contents implements Widget_Interface_Do {
    public function __construct($request, $response, $params = NULL) {
        parent::__construct($request, $response, $params);
    }

    public function die_with_json($code,$msg){
        $array = array(
            'ret'=>$code,
            'msg'=>$msg
        );
        die(json_encode($array));
    }

    /**
     * 绑定动作
     *
     * @access public
     * @return void
     */
    public function action() {
        $this->die_with_json(1,'未实现' );
    }
}

?>
