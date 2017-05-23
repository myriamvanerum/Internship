<?php

class Survey_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->model('Group_model');
        $this->load->model('Question_model');
    }

    function get($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('survey');
        $survey = $query->row();
        
        $survey->group = $this->Group_model->get($survey->group_id);
        $survey->questions = $this->Question_model->getQuestionsBySurvey($survey->id);
        
        return $survey;
    }

    function getAll() {
        $this->db->order_by('name', 'asc');
        $query = $this->db->get('survey');
        $surveys = $query->result();
        
        foreach($surveys as $survey){
            $survey->group = $this->Group_model->get($survey->group_id);
            $survey->question_count = $this->Question_model->getSurveyQuestionCount($survey->id);
        }

        return $surveys;
    }
    
    function insert($survey) {
        $this->db->insert('survey', $survey);
    }
    
    function toggleActive($survey) {
        $this->db->where('id', $survey->id);
        $this->db->update('survey', $survey);
    }
}
