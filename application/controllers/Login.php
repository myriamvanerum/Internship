<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

//***************************Myriam Van Erum********************************
/**
 * Page coded by Myriam Van Erum 
 * Login page
 */
class Login extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
    }

    public function LoadView($viewnaam, $data) {       
        $partials = array(
            'title' => $data['title'],
            'header' => $this->parser->parse('main_header', $data, true),
            'content' => $this->parser->parse($viewnaam, $data, true),
            'footer' => $this->parser->parse('main_footer', $data, true)
        );
        $this->parser->parse('main_master', $partials);
    }

    public function index() {
        $this->user_control->notLoggedIn();
        
        $data['title'] = 'Login - HH Prospects';
        $data['error'] = json_encode($this->session->flashdata('error'));
        $data['updated'] = json_encode($this->session->flashdata('updated'));
        $data['user'] = json_encode($this->authex->getUserInfo());
        $this->LoadView('login/login', $data);
    }

    public function login() {
        $email = trim($this->input->post('email'));
        $password = $this->input->post('password');

        if ($this->authex->login($email, $password)) {
            $user = $this->User_model->getUser($email);
            
            switch ($user->level) {
                case 2:
                    // Administrator
                    redirect('Admin');
                    break;
                case 3:
                    // Analyst
                    redirect('Analyst');
                    break;
            }
        } else {
            $this->session->set_flashdata('error', 1);

            redirect('Login/index');
        }
    }

    public function logout() {
        $this->authex->logout();
        redirect('Home');
    }
    
    public function forgot_password() {
        $data['title'] = 'Forgot your password? - HH Prospects';
        $data['user'] = $this->authex->getUserInfo();
        $data['error'] = $this->session->flashdata('error');
        $this->LoadView('login/forgot_password', $data);
    }
    
    public function check_email() {
        $email = trim($this->input->post('email'));

        $emailExists = $this->User_model->email_exists($email);

        if ($emailExists) {
            $this->send_email($email);
            
            $data['title'] = 'Password reset email sent - HH Prospects';
            $data['email'] = $email;
            $this->LoadView('login/email_sent', $data);
        } else {
            $this->session->set_flashdata('error', 1);
            redirect('Login/forgot_password');
        }
    }
    
    public function send_email($email) {
        $this->email->from('noreply@hh.se', 'Halmstad University Prospects');
        $this->email->to($email);
        $this->email->subject('Reset your password');
        $data = array();
        
        $data['url'] = base_url() . 'index.php/Login/reset_password/' . urlencode($email) . '/' . sha1($email);
        
        //$this->email->message($this->load->view('emails/reset_password_email', $data, true));
        $this->email->message(base_url() . 'index.php/Login/reset_password/' . urlencode($email) . '/' . sha1($email));
        //$this->email->set_mailtype("html");
        $this->email->send();
        }
    
    public function reset_password($email, $encr_email) {
        $email = urldecode($email);
        if (sha1($email) == $encr_email)
        {
            // OK, go ahead
            $this->show_reset_page($email);
        }
        else
        {
            // Wrong url, go to 404
            show_404();
        }
        
    }
    
    public function show_reset_page($email) {
        $data['title'] = 'Reset your password - HH Prospects';
        $data['email'] = $email;
        $data['error'] = $this->session->flashdata('error');
        $this->LoadView('login/reset_password', $data);
    }
    
    public function change_password() {
        $email = trim($this->input->post('email'));
        $password = $this->input->post('password');
        $passwordControl = $this->input->post('passwordControl');

        if ($password === $passwordControl) {
            $this->User_model->update_password($email, $password);
            $this->session->set_flashdata('updated', 1);
            redirect('Login/index');
        } else {
            $this->session->set_flashdata('error', 1);
            redirect('Login/reset_password/' . urlencode($email) . '/' . sha1($email));
        }
    }

}
