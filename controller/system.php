<?php
namespace controller;

use \system\be;

class system extends \system\controller
{
    // 用户使用条款
    public function terms_and_conditions()
    {
        $model_system = be::get_model('system');
        $language = $model_system->get_language();

        $row_system_terms_and_conditions = be::get_row('system_terms_and_conditions');
        $row_system_terms_and_conditions->load(array('language'=>$language));

        $template = be::get_template('system.terms_and_conditions');
        $template->set_title('用户使用条款');
        $template->set('terms_and_conditions', $row_system_terms_and_conditions);
        $template->display();
    }

    // 隐私协议
    public function privacy_policy()
    {
        $model_system = be::get_model('system');
        $language = $model_system->get_language();

        $row_system_privacy_policy = be::get_row('system_privacy_policy');
        $row_system_privacy_policy->load(array('language'=>$language));

        $template = be::get_template('system.privacy_policy');
        $template->set_title('隐私保护');
        $template->set('privacy_policy', $row_system_privacy_policy);
        $template->display();
    }

}
?>