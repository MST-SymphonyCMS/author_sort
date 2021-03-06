<?php

Class extension_author_sort extends  Extension {

    public function getSubscribedDelegates() {
        
        return array(

            array(
                'page'     => '/publish/',
                'delegate' => 'getSortingField',
                'callback' => 'getSortingField'
            ),

            array('page'     => '/publish/',
                  'delegate' => 'setSortingField',
                  'callback' => 'setSortingField'
            ),

            array('page'     => '/publish/',
                  'delegate' => 'getSortingOrder',
                  'callback' => 'getSortingOrder'
            ),

            array('page'     => '/publish/',
                  'delegate' => 'setSortingOrder',
                  'callback' => 'setSortingOrder'
            ),

        );
    }

    public function install() {
        Symphony::Database()->query("
            CREATE TABLE IF NOT EXISTS `tbl_author_sort` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) UNSIGNED NOT NULL,
                `direction` ENUM('asc', 'desc') DEFAULT 'asc',
                `field_id` INT(11) UNSIGNED NOT NULL,
                `section_handle` VARCHAR(50) NOT NULL,
                PRIMARY KEY (`id`)
            )
        ");
        
        return true;
    }

    public function getUser(){
        try {
            if (Symphony::Engine() instanceof Frontend){
                // use config sorting if frontend
                return false;
            } else {
                return Symphony::Author()->get('id');
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function checkUserSection($section){
        //check if user has a field set in the specific section
        $query = "SELECT user_id 
                FROM tbl_author_sort
                WHERE user_id = '{$this->getUser()}'
                AND section_handle = '{$section}'
                LIMIT 1
                    ";
        if(Symphony::Database()->fetchVar('user_id',0,$query)){
            return true;
        }
        return false;
    }

    public function getSortingField($context){

        $user = $this->getUser();

        if (!$user){
            return;
        }

        $query = "SELECT field_id 
                FROM tbl_author_sort
                WHERE user_id = '{$user}' AND section_handle = '{$context['section-handle']}'
                LIMIT 1
                    ";
        $context['field'] = Symphony::Database()->fetchVar('field_id',0,$query);
    }

    public function setSortingField($context){

        if($this->checkUserSection($context['section-handle'])){
            //user has a field set, update the field id.
            $update = [];
            $update['field_id'] = $context['sort'];
            $where = [];
            $where['user_id'] = $this->getUser();
            $where['section_handle'] = $context['section-handle'];

            $context['updated'] = Symphony::Database()->update($update,'tbl_author_sort','user_id = '.$this->getUser().' AND section_handle = "'.$context["section-handle"].'"');
        }
        else{
            //user does not have a field set, set a new one.
            $insert = [];
            $insert['user_id'] = $this->getUser();
            $insert['field_id'] = $context['sort'];
            $insert['section_handle'] = $context['section-handle'];
            $context['updated'] = Symphony::Database()->insert($insert,'tbl_author_sort','true');
        }
    }

     public function getSortingOrder($context){

        $user = $this->getUser();

        if (!$user){
            return;
        }

        $query = "SELECT direction 
                FROM tbl_author_sort
                WHERE user_id = '{$user}' AND section_handle = '{$context['section-handle']}'
                LIMIT 1
                    ";
        $context['order'] = Symphony::Database()->fetchVar('direction',0,$query);
    }

    public function setSortingOrder($context){

        if($this->checkUserSection($context['section-handle'])){
            //user has a field set, update the field id.
            $update = [];
            $update['direction'] = $context['order'];
            $where = [];
            $where['user_id'] = $this->getUser();
            $where['section_handle'] = $context['section-handle'];

            $context['updated'] = Symphony::Database()->update($update,'tbl_author_sort','user_id = '.$this->getUser().' AND section_handle = "'.$context["section-handle"].'"');
        }
        else{
            //user does not have a field set, set a new one.
            $insert = [];
            $insert['user_id'] = $this->getUser();
            $insert['direction'] = $context['order'];
            $insert['section_handle'] = $context['section-handle'];
            $context['updated'] = Symphony::Database()->insert($insert,'tbl_author_sort','true');
        }
    }


}
